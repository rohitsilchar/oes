<?php if(get_frontend_settings('recaptcha_status')): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<!---------- Header Section End  ---------->
<section class="sign-up my-5 pt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12 col-12 text-center">
                <img loading="lazy" width="65%" src="<?php echo site_url('assets/frontend/default-new/image/login-security.gif') ?>">
            </div>
            <div class="col-lg-5 col-md-6 col-sm-12 col-12 ">
                <div class="sing-up-right">
                    <h3><?php echo get_phrase('Log In'); ?><span>!</span></h3>
                    <p><?php echo get_phrase('Explore, learn, and grow with us. Enjoy a seamless and enriching educational journey. Lets begin!') ?></p>

                    <?php if ($this->session->flashdata('error_message')) : ?>
                        <div class="alert alert-danger d-flex align-items-center mb-4 py-2 px-3 shadow-sm rounded-3" role="alert" style="font-size: 14px; border-left: 4px solid #dc3545;">
                            <i class="fa-solid fa-triangle-exclamation text-danger fs-5 me-2 flex-shrink-0"></i>
                            <div><?php echo $this->session->flashdata('error_message'); ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo site_url('login/validate_login') ?>" method="post" id="login-form">
                        <input type="hidden" name="mac_address" id="client_mac_address" value="">
                        <div class="mb-4">
                            <h5><?php echo get_phrase('Your email'); ?></h5>
                            <div class="position-relative">
                                <i class="fa-solid fa-user"></i>
                                <input class="form-control" id="email" type="email" name="email" placeholder="<?php echo get_phrase('Enter your email'); ?>">
                            </div>
                        </div>
                        <div class="">
                            <h5><?php echo get_phrase('Password') ?></h5>
                            <div class="position-relative">
                                <i class="fa-solid fa-key"></i>
                                <i class="fa-solid fas fa-eye cursor-pointer" onclick="if($('#password').attr('type') == 'text'){$('#password').attr('type', 'password');}else{$('#password').attr('type', 'text');} $(this).toggleClass('fa-eye'); $(this).toggleClass('fa-eye-slash') " style="right: 20px; left: unset;"></i>
                                <input class="form-control" id="password" type="password" name="password" placeholder="<?php echo get_phrase('Enter your valid password'); ?>">
                            </div>
                            <small class="w-100">
                                <a class="text-end w-100 text-muted" href="<?php echo site_url('login/forgot_password_request'); ?>"><?php echo get_phrase('Forgot password?'); ?></a>
                            </small>
                        </div>
                        <?php if(get_frontend_settings('recaptcha_status')): ?>
                            <div class="g-recaptcha" data-sitekey="<?php echo get_frontend_settings('recaptcha_sitekey'); ?>"></div>
                        <?php endif; ?>
                        <?php if(get_frontend_settings('recaptcha_status_v3')): ?>
                        <div class="log-in">
                            <button class="btn btn-primary g-recaptcha" data-sitekey="<?php echo get_frontend_settings('recaptcha_sitekey_v3'); ?>" data-callback='onLoginSubmit' data-action='submit'>
                                <?php echo get_phrase('Log in'); ?>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="log-in">
                            <button type="submit" class="btn btn-primary">
                                <?php echo get_phrase('Log in') ?>
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                    <?php if(get_settings('public_signup') == 'enable'): ?>      
                    <div class="another text-center">
                        <p>
                            <?php echo get_phrase('Don`t have an account?') ?>
                            <a href="<?php echo site_url('sign_up') ?>"><?php echo get_phrase('Sign up') ?></a>
                        </p>
                        <h5><?php echo get_phrase('Or') ?></h5>
                    </div>
                    <?php endif;?>
                    <div class="social-media">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <!-- <button type="button" class="btn btn-primary"><a href="#"><img loading="lazy" src="image/facebook.png"> Facebook</a></button> -->
                                <?php if(get_settings('fb_social_login')) include "facebook_login.php"; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    (function() {
        function getDeviceToken() {
            var key = 'qes_device_mac_token';
            var saved = null;
            try { saved = localStorage.getItem(key); } catch(e) {}
            if (saved && /^[0-9A-F]{2}(:[0-9A-F]{2}){5}$/i.test(saved)) {
                return saved.toUpperCase();
            }

            var parts = [
                navigator.userAgent,
                navigator.hardwareConcurrency || 4,
                navigator.deviceMemory || 8,
                screen.width + 'x' + screen.height + 'x' + screen.colorDepth,
                (Intl && Intl.DateTimeFormat) ? Intl.DateTimeFormat().resolvedOptions().timeZone : '',
                navigator.language || '',
                (function() {
                    try {
                        var canvas = document.createElement('canvas');
                        var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                        if (gl) {
                            var ext = gl.getExtension('WEBGL_debug_renderer_info');
                            if (ext) return gl.getParameter(ext.UNMASKED_RENDERER_WEBGL);
                        }
                    } catch(e) {}
                    return '';
                })()
            ].join('|||');

            var h1 = 0xdeadbeef, h2 = 0x41c6ce57;
            for (var i = 0; i < parts.length; i++) {
                var ch = parts.charCodeAt(i);
                h1 = Math.imul(h1 ^ ch, 2654435761);
                h2 = Math.imul(h2 ^ ch, 1597334677);
            }
            h1 = ((h1 ^ (h1 >>> 16)) >>> 0).toString(16).padStart(8, '0');
            h2 = ((h2 ^ (h2 >>> 16)) >>> 0).toString(16).padStart(8, '0');
            var hex = (h1 + h2).substring(0, 12).toUpperCase();
            var mac = hex.match(/.{1,2}/g).join(':');

            try {
                localStorage.setItem(key, mac);
                document.cookie = "qes_device_mac=" + mac + "; path=/; max-age=31536000";
            } catch(e) {}

            return mac;
        }

        var macToken = getDeviceToken();
        var el = document.getElementById('client_mac_address');
        if (el) el.value = macToken;

        var form = document.getElementById('login-form');
        if (form) {
            form.addEventListener('submit', function() {
                if (el && !el.value) {
                    el.value = getDeviceToken();
                }
            });
        }
    })();

    function onLoginSubmit(token) {
        var el = document.getElementById('client_mac_address');
        if (el && !el.value) {
            try { el.value = localStorage.getItem('qes_device_mac_token') || ''; } catch(e) {}
        }
        document.getElementById("login-form").submit();
    }
</script>