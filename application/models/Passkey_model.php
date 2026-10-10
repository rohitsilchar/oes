<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Device-bound passkeys (WebAuthn) used to lock a user account to the device
 * it was first registered on. Credentials live in the device's secure hardware
 * (e.g. Windows Hello / TPM), so every browser on that device can use them,
 * but another device cannot.
 */
class Passkey_model extends CI_Model
{
    // FALSE (default): works on every modern OS — synced passkeys (iCloud Keychain, Google Password
    //   Manager, Windows), a phone via QR code, or a USB security key. The account is locked to the
    //   user's own passkey, which may sync between that user's devices.
    // TRUE: strict one-device lock — only the device's built-in authenticator and only passkeys that
    //   cannot sync. In practice this works only on Windows 10 (1903+) / 11 with Windows Hello.
    const STRICT_DEVICE_BINDING = false;

    // How long a user has to complete the passkey step after entering a valid password
    const PENDING_LOGIN_TTL = 300;

    private $webauthn = null;

    public function __construct()
    {
        parent::__construct();
        $this->ensure_passkey_table();
    }

    /**
     * Create the user_passkeys table if missing (safe self-healing migration)
     */
    public function ensure_passkey_table()
    {
        if (!$this->db->table_exists('user_passkeys')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `user_passkeys` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT UNSIGNED NOT NULL,
                `credential_id` TEXT NOT NULL,
                `public_key` TEXT NOT NULL,
                `sign_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `backup_eligible` TINYINT(1) NOT NULL DEFAULT 0,
                `device_name` VARCHAR(255) NULL DEFAULT NULL,
                `created_at` INT UNSIGNED NULL DEFAULT NULL,
                `last_used_at` INT UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_user_passkeys_user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }

    /**
     * WebAuthn server instance bound to this site's domain (the relying party)
     */
    public function webauthn()
    {
        if ($this->webauthn === null) {
            require_once APPPATH . 'vendor/autoload.php';
            $rp_id   = parse_url(base_url(), PHP_URL_HOST);
            $rp_name = get_settings('system_name') ?: $rp_id;
            $this->webauthn = new \lbuchs\WebAuthn\WebAuthn($rp_name, $rp_id, ['none']);
        }
        return $this->webauthn;
    }

    public function get_user_passkeys($user_id)
    {
        return $this->db->get_where('user_passkeys', ['user_id' => $user_id])->result_array();
    }

    public function has_passkey($user_id)
    {
        return $this->db->where('user_id', $user_id)->count_all_results('user_passkeys') > 0;
    }

    /**
     * Binary credential IDs of a user's passkeys (for allowCredentials / excludeCredentials)
     */
    public function get_credential_ids($user_id)
    {
        $ids = [];
        foreach ($this->get_user_passkeys($user_id) as $passkey) {
            $ids[] = $this->base64url_decode($passkey['credential_id']);
        }
        return $ids;
    }

    /**
     * Find one of the user's passkeys by its binary credential ID
     */
    public function find_user_passkey($user_id, $credential_id_binary)
    {
        foreach ($this->get_user_passkeys($user_id) as $passkey) {
            if (hash_equals($this->base64url_decode($passkey['credential_id']), $credential_id_binary)) {
                return $passkey;
            }
        }
        return null;
    }

    public function save_passkey($user_id, $data)
    {
        $user_agent = (string) $this->input->user_agent();
        $this->db->insert('user_passkeys', [
            'user_id'         => $user_id,
            'credential_id'   => $this->base64url_encode($data->credentialId),
            'public_key'      => $data->credentialPublicKey,
            'sign_count'      => (int) $data->signatureCounter,
            'backup_eligible' => $data->isBackupEligible ? 1 : 0,
            'device_name'     => substr($user_agent, 0, 255),
            'created_at'      => time(),
        ]);
    }

    public function mark_used($passkey_id, $sign_count)
    {
        $this->db->where('id', $passkey_id);
        $this->db->update('user_passkeys', [
            'sign_count'   => (int) $sign_count,
            'last_used_at' => time(),
        ]);
    }

    /**
     * Remove all of a user's passkeys so their next login registers a new device
     */
    public function reset_user_passkeys($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->delete('user_passkeys');
    }

    public function base64url_encode($binary)
    {
        return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
    }

    public function base64url_decode($string)
    {
        return base64_decode(strtr($string, '-_', '+/'));
    }
}
