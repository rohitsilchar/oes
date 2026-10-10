<?php $is_register = ($passkey_mode == 'register'); ?>
<section class="sign-up my-5 pt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12 col-12 text-center">
                <img loading="lazy" width="65%" src="<?php echo site_url('assets/frontend/default-new/image/login-security.gif') ?>">
            </div>
            <div class="col-lg-5 col-md-6 col-sm-12 col-12 ">
                <div class="sing-up-right">
                    <h3><?php echo $is_register ? site_phrase('register_this_device') : site_phrase('verify_this_device'); ?><span>!</span></h3>
                    <p>
                        <?php if ($is_register && $passkey_strict): ?>
                            <?php echo site_phrase('your_account_will_be_locked_to_this_device') . '. ' . site_phrase('confirm_with_windows_hello_or_your_device_pin_fingerprint_or_face') . '. ' . site_phrase('you_can_use_any_browser_on_this_device_afterwards') . '.'; ?>
                        <?php elseif ($is_register): ?>
                            <?php echo site_phrase('create_a_passkey_to_secure_your_account') . '. ' . site_phrase('confirm_with_your_device_pin_fingerprint_or_face') . '. ' . site_phrase('if_this_device_cannot_create_one_choose_use_a_phone_and_scan_the_qr_code') . '.'; ?>
                        <?php else: ?>
                            <?php echo site_phrase('confirm_with_your_passkey_to_continue') . '. ' . site_phrase('use_your_device_pin_fingerprint_or_face_or_scan_the_qr_code_with_your_phone') . '.'; ?>
                        <?php endif; ?>
                    </p>

                    <div id="passkey-info" class="alert alert-info d-none align-items-center mb-4 py-2 px-3 rounded-3" role="alert" style="font-size: 14px;">
                        <i class="fa-solid fa-mobile-screen me-2 flex-shrink-0"></i>
                        <div id="passkey-info-text"></div>
                    </div>
                    <p class="text-muted" style="font-size: 14px;">
                        <i class="fa-solid fa-user me-1"></i><?php echo htmlspecialchars($passkey_user['email']); ?>
                    </p>

                    <div id="passkey-error" class="alert alert-danger d-none align-items-center mb-4 py-2 px-3 shadow-sm rounded-3" role="alert" style="font-size: 14px; border-left: 4px solid #dc3545;">
                        <i class="fa-solid fa-triangle-exclamation text-danger fs-5 me-2 flex-shrink-0"></i>
                        <div id="passkey-error-text"></div>
                    </div>

                    <div class="log-in">
                        <button type="button" id="passkey-button" class="btn btn-primary">
                            <i class="fa-solid fa-fingerprint me-1"></i>
                            <?php echo $is_register ? site_phrase('register_this_device') : site_phrase('verify_this_device'); ?>
                        </button>
                    </div>
                    <div class="another text-center mt-3">
                        <a class="text-muted" href="<?php echo site_url('passkey/cancel'); ?>"><?php echo site_phrase('back_to_login'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    (function() {
        var isRegister = <?php echo json_encode($is_register); ?>;
        var isStrict   = <?php echo json_encode((bool) $passkey_strict); ?>;
        var optionsUrl = <?php echo json_encode(site_url($is_register ? 'passkey/register_options' : 'passkey/verify_options')); ?>;
        var submitUrl  = <?php echo json_encode(site_url($is_register ? 'passkey/register_submit' : 'passkey/verify_submit')); ?>;
        var messages = <?php echo json_encode([
            'unsupported'   => site_phrase('this_browser_does_not_support_passkeys') . '. ' . site_phrase('please_use_an_up_to_date_chrome_edge_firefox_or_safari') . '.',
            'no_platform'   => site_phrase('this_device_has_no_windows_hello_or_screen_lock_set_up') . '. ' . site_phrase('on_windows_go_to_settings_accounts_sign_in_options_and_set_up_a_pin_then_try_again') . '.',
            'use_phone'     => site_phrase('this_device_has_no_built_in_passkey_support') . '. ' . site_phrase('click_the_button_and_choose_use_a_phone_to_scan_the_qr_code_or_insert_a_security_key') . '.',
            'cancelled'     => site_phrase('the_device_check_was_cancelled_or_timed_out') . '. ' . site_phrase('please_try_again') . '.',
            'unregistered'  => $unregistered_device_message,
            'already'       => site_phrase('this_device_is_already_registered') . '. ' . site_phrase('please_go_back_and_login_again') . '.',
            'generic'       => site_phrase('device_verification_failed') . '. ' . site_phrase('please_try_again') . '.',
        ]); ?>;

        var button = document.getElementById('passkey-button');

        function showError(text) {
            var box = document.getElementById('passkey-error');
            document.getElementById('passkey-error-text').textContent = text;
            box.classList.remove('d-none');
            box.classList.add('d-flex');
        }

        // The server sends binary fields as "=?BINARY?B?<base64>?=" strings
        function decodeBinary(value) {
            if (typeof value === 'string') {
                var m = value.match(/^=\?BINARY\?B\?(.*)\?=$/);
                if (m) {
                    var bin = atob(m[1]), bytes = new Uint8Array(bin.length);
                    for (var i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
                    return bytes.buffer;
                }
                return value;
            }
            if (Array.isArray(value)) return value.map(decodeBinary);
            if (value && typeof value === 'object') {
                var out = {};
                for (var k in value) out[k] = decodeBinary(value[k]);
                return out;
            }
            return value;
        }

        function toBase64(buffer) {
            var bytes = new Uint8Array(buffer), bin = '';
            for (var i = 0; i < bytes.length; i++) bin += String.fromCharCode(bytes[i]);
            return btoa(bin);
        }

        function postJson(url, body) {
            return fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: body ? JSON.stringify(body) : '{}'
            }).then(function(r) { return r.json(); });
        }

        function handleServerError(res) {
            if (res.redirect) { window.location = res.redirect; return; }
            showError(res.message || messages.generic);
        }

        async function run() {
            button.disabled = true;
            try {
                var options = await postJson(optionsUrl);
                if (options.ok === false) return handleServerError(options);
                options = decodeBinary(options);

                var payload;
                if (isRegister) {
                    if (isStrict) options.publicKey.hints = ['client-device'];
                    var created = await navigator.credentials.create(options);
                    payload = {
                        clientDataJSON: toBase64(created.response.clientDataJSON),
                        attestationObject: toBase64(created.response.attestationObject)
                    };
                } else {
                    if (isStrict) options.publicKey.hints = ['client-device'];
                    var assertion = await navigator.credentials.get(options);
                    payload = {
                        id: toBase64(assertion.rawId),
                        clientDataJSON: toBase64(assertion.response.clientDataJSON),
                        authenticatorData: toBase64(assertion.response.authenticatorData),
                        signature: toBase64(assertion.response.signature)
                    };
                }

                var result = await postJson(submitUrl, payload);
                if (result.ok) {
                    window.location = result.redirect;
                    return;
                }
                handleServerError(result);
            } catch (e) {
                if (e && e.name === 'NotAllowedError') {
                    // On verify, the browser reports a device without the account's passkey the same way as a cancel
                    showError(isRegister ? messages.cancelled : messages.cancelled + ' ' + messages.unregistered);
                } else if (e && e.name === 'InvalidStateError') {
                    showError(messages.already);
                } else {
                    showError(messages.generic + (e && e.message ? ' (' + e.message + ')' : ''));
                }
            } finally {
                button.disabled = false;
            }
        }

        if (!window.PublicKeyCredential || !navigator.credentials) {
            showError(messages.unsupported);
            button.disabled = true;
            return;
        }

        PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable().then(function(available) {
            if (available) return;
            if (isStrict) {
                showError(messages.no_platform);
            } else {
                // No built-in authenticator (e.g. Linux, older Windows): a phone or security key still works
                var info = document.getElementById('passkey-info');
                document.getElementById('passkey-info-text').textContent = messages.use_phone;
                info.classList.remove('d-none');
                info.classList.add('d-flex');
            }
        }).catch(function() {});

        button.addEventListener('click', run);
    })();
</script>
