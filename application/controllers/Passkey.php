<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Second login step for non-admin users: after a valid email/password,
 * Login::validate_login() parks the user here until this device is
 * registered (first login) or verified (later logins) with a device-bound passkey.
 */
class Passkey extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set(get_settings('timezone'));

        $this->load->database();
        $this->load->library('session');
        $this->load->model('passkey_model');
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }

    // First login (or after an admin reset): register this device
    public function register()
    {
        $user = $this->pending_user_or_redirect();
        if ($this->passkey_model->has_passkey($user['id'])) {
            redirect(site_url('passkey/verify'), 'refresh');
        }
        $this->show_page('register', $user);
    }

    // Later logins: prove this is the registered device
    public function verify()
    {
        $user = $this->pending_user_or_redirect();
        if (!$this->passkey_model->has_passkey($user['id'])) {
            redirect(site_url('passkey/register'), 'refresh');
        }
        $this->show_page('verify', $user);
    }

    public function register_options()
    {
        $user = $this->pending_user_or_fail();
        if ($this->passkey_model->has_passkey($user['id'])) {
            $this->json_fail(get_phrase('a_device_is_already_registered_for_this_account'));
        }

        $webauthn = $this->passkey_model->webauthn();
        $args = $webauthn->getCreateArgs(
            (string) $user['id'],
            $user['email'],
            trim($user['first_name'] . ' ' . $user['last_name']),
            120,
            false,      // no discoverable credential needed: the user is already identified by email/password
            'required', // device PIN / fingerprint / face
            // strict: the device's built-in authenticator only; otherwise also a phone (QR) or security key
            Passkey_model::STRICT_DEVICE_BINDING ? false : null
        );
        $this->session->set_userdata('passkey_challenge', base64_encode($webauthn->getChallenge()->getBinaryString()));

        $this->json_out($args);
    }

    public function register_submit()
    {
        $user = $this->pending_user_or_fail();
        $post = json_decode($this->input->raw_input_stream);
        $challenge = $this->take_challenge();

        if (!$post || !isset($post->clientDataJSON, $post->attestationObject) || !$challenge) {
            $this->json_fail(get_phrase('invalid_request') . '. ' . get_phrase('please_try_again'));
        }
        if ($this->passkey_model->has_passkey($user['id'])) {
            $this->json_fail(get_phrase('a_device_is_already_registered_for_this_account'));
        }

        try {
            $data = $this->passkey_model->webauthn()->processCreate(
                base64_decode($post->clientDataJSON),
                base64_decode($post->attestationObject),
                $challenge,
                true,  // user verification required
                true,  // user presence required
                false
            );
        } catch (\Throwable $e) {
            log_message('error', 'Passkey registration failed for user ' . $user['id'] . ': ' . $e->getMessage());
            $this->json_fail(get_phrase('device_registration_failed') . ': ' . $e->getMessage());
        }

        if ($data->isBackupEligible && Passkey_model::STRICT_DEVICE_BINDING) {
            $this->json_fail(get_phrase('this_passkey_can_sync_to_other_devices_so_it_cannot_lock_your_account_to_this_device') . '. ' . get_phrase('please_try_again_and_choose_windows_hello_or_this_device_instead_of_a_password_manager_or_phone') . '.');
        }

        $this->passkey_model->save_passkey($user['id'], $data);
        $this->session->set_userdata('passkey_verified_user_id', $user['id']);
        $this->json_out(['ok' => true, 'redirect' => site_url('passkey/complete')]);
    }

    public function verify_options()
    {
        $user = $this->pending_user_or_fail();
        $credential_ids = $this->passkey_model->get_credential_ids($user['id']);
        if (empty($credential_ids)) {
            $this->json_fail(get_phrase('no_device_is_registered_for_this_account'));
        }

        // strict: only the device's built-in authenticator; otherwise also USB / NFC / BLE keys and a phone (hybrid / QR)
        $allow_external = !Passkey_model::STRICT_DEVICE_BINDING;
        $webauthn = $this->passkey_model->webauthn();
        $args = $webauthn->getGetArgs(
            $credential_ids,
            120,
            $allow_external, $allow_external, $allow_external, $allow_external,
            true,
            'required'
        );
        $this->session->set_userdata('passkey_challenge', base64_encode($webauthn->getChallenge()->getBinaryString()));

        $this->json_out($args);
    }

    public function verify_submit()
    {
        $user = $this->pending_user_or_fail();
        $post = json_decode($this->input->raw_input_stream);
        $challenge = $this->take_challenge();

        if (!$post || !isset($post->id, $post->clientDataJSON, $post->authenticatorData, $post->signature) || !$challenge) {
            $this->json_fail(get_phrase('invalid_request') . '. ' . get_phrase('please_try_again'));
        }

        $passkey = $this->passkey_model->find_user_passkey($user['id'], base64_decode($post->id));
        if (!$passkey) {
            $this->json_fail($this->unregistered_device_message());
        }

        $webauthn = $this->passkey_model->webauthn();
        try {
            $webauthn->processGet(
                base64_decode($post->clientDataJSON),
                base64_decode($post->authenticatorData),
                base64_decode($post->signature),
                $passkey['public_key'],
                $challenge,
                (int) $passkey['sign_count'],
                true, // user verification required
                true  // user presence required
            );
        } catch (\Throwable $e) {
            log_message('error', 'Passkey verification failed for user ' . $user['id'] . ': ' . $e->getMessage());
            $this->json_fail(get_phrase('device_verification_failed') . ': ' . $e->getMessage());
        }

        $sign_count = $webauthn->getSignatureCounter();
        $this->passkey_model->mark_used($passkey['id'], $sign_count !== null ? $sign_count : $passkey['sign_count']);
        $this->session->set_userdata('passkey_verified_user_id', $user['id']);
        $this->json_out(['ok' => true, 'redirect' => site_url('passkey/complete')]);
    }

    // Finish the normal login once the device check passed
    public function complete()
    {
        $user_id = $this->session->userdata('passkey_pending_user_id');
        $verified_user_id = $this->session->userdata('passkey_verified_user_id');
        $this->session->unset_userdata(['passkey_pending_user_id', 'passkey_pending_expires', 'passkey_verified_user_id', 'passkey_challenge']);

        if (!$user_id || $user_id != $verified_user_id) {
            $this->session->set_flashdata('error_message', get_phrase('device_verification_failed') . '. ' . get_phrase('please_try_again'));
            redirect(site_url('login'), 'refresh');
        }

        $this->user_model->new_device_login_tracker($user_id);
        $this->user_model->set_login_userdata($user_id);
    }

    public function cancel()
    {
        $this->session->unset_userdata(['passkey_pending_user_id', 'passkey_pending_expires', 'passkey_verified_user_id', 'passkey_challenge']);
        redirect(site_url('login'), 'refresh');
    }

    private function unregistered_device_message()
    {
        return get_phrase('access_denied') . '! ' . get_phrase('this_device_is_not_registered_for_your_account') . '. ' . get_phrase('please_login_from_your_registered_device_or_contact_the_administrator_to_reset_it') . '.';
    }

    private function show_page($mode, $user)
    {
        $page_data['page_name']  = 'passkey';
        $page_data['page_title'] = $mode == 'register' ? site_phrase('register_this_device') : site_phrase('verify_this_device');
        $page_data['passkey_mode'] = $mode;
        $page_data['passkey_user'] = $user;
        $page_data['passkey_strict'] = Passkey_model::STRICT_DEVICE_BINDING;
        $page_data['unregistered_device_message'] = $this->unregistered_device_message();
        $this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $page_data);
    }

    // User who passed the password step and is waiting for the passkey step
    private function pending_user()
    {
        $user_id = $this->session->userdata('passkey_pending_user_id');
        $expires = (int) $this->session->userdata('passkey_pending_expires');
        if (!$user_id || $expires < time()) {
            return null;
        }
        return $this->db->get_where('users', ['id' => $user_id, 'status' => 1])->row_array();
    }

    private function pending_user_or_redirect()
    {
        $user = $this->pending_user();
        if (!$user) {
            $this->session->set_flashdata('error_message', get_phrase('time_over') . '! ' . site_phrase('please_login_again'));
            redirect(site_url('login'), 'refresh');
        }
        return $user;
    }

    private function pending_user_or_fail()
    {
        $user = $this->pending_user();
        if (!$user) {
            $this->json_fail(get_phrase('time_over') . '! ' . site_phrase('please_login_again'), site_url('login'));
        }
        return $user;
    }

    // Challenges are single-use
    private function take_challenge()
    {
        $challenge = $this->session->userdata('passkey_challenge');
        $this->session->unset_userdata('passkey_challenge');
        return $challenge ? base64_decode($challenge) : null;
    }

    private function json_out($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function json_fail($message, $redirect = null)
    {
        $this->json_out(['ok' => false, 'message' => $message, 'redirect' => $redirect]);
    }
}
