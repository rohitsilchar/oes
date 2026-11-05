<?php
$system_name = get_settings('system_name');
$is_admin = $this->session->userdata('admin_login') == 1;
$is_user = $this->session->userdata('user_login') == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo site_phrase('front_website_disabled') . ' | ' . html_escape($system_name); ?></title>
    <link rel="shortcut icon" href="<?php echo base_url('assets/favicon.png'); ?>">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f4f6f9;
            color: #1f2937;
        }
        .card {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
            padding: 40px 32px;
            text-align: center;
        }
        .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        h1 { font-size: 22px; margin: 0 0 12px; }
        p { font-size: 15px; line-height: 1.6; color: #4b5563; margin: 0 0 24px; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            background: #4f46e5;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
        }
        .btn:hover { background: #4338ca; }
        .site { margin-top: 28px; font-size: 13px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
            </svg>
        </div>
        <h1><?php echo site_phrase('front_website_is_disabled_by_admin'); ?></h1>
        <p><?php echo site_phrase('the_front_website_has_been_disabled_by_the_administrator') . '. ' . site_phrase('please_check_back_later') . '.'; ?></p>

        <?php if ($is_admin): ?>
            <a class="btn" href="<?php echo site_url('admin/dashboard'); ?>"><?php echo site_phrase('go_to_admin_panel'); ?></a>
        <?php elseif ($is_user): ?>
            <a class="btn" href="<?php echo site_url('home/my_courses'); ?>"><?php echo site_phrase('go_to_my_courses'); ?></a>
        <?php else: ?>
            <a class="btn" href="<?php echo site_url('login'); ?>"><?php echo site_phrase('login'); ?></a>
        <?php endif; ?>

        <div class="site"><?php echo html_escape($system_name); ?></div>
    </div>
</body>
</html>
