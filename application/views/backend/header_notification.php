<?php foreach($notifications->result_array() as $notification): ?>
    <?php
        $target_url = 'javascript:void(0);';
        if ($notification['type'] == 'message') {
            $thread_code = $this->crud_model->get_thread_code_between($notification['from_user'], $notification['to_user']);
            $target_url = $thread_code ? site_url('admin/message/message_read/' . $thread_code) : site_url('admin/message');
        } elseif ($notification['type'] == 'signup') {
            $target_url = site_url('admin/users');
        }
    ?>
    <a href="<?php echo $target_url; ?>" class="dropdown-item notify-item cursor-pointer <?php if($notification['status'] == 0) echo 'unread' ?>" style="text-decoration: none; color: inherit;">
        <?php if($notification['type'] == 'signup' || $notification['type'] == 'message'): ?>
            <div class="notify-icon">
                <img src="<?php echo $this->user_model->get_user_image_url($notification['from_user']); ?>" class="img-fluid rounded-circle" alt="User image" style="width: 36px; height: 36px; object-fit: cover;" />
            </div>
        <?php else: ?>
            <div class="notify-icon bg-info">
                <i class="mdi mdi-comment-account-outline"></i>
            </div>
        <?php endif; ?>
        <p class="notify-details">
            <?php echo $notification['title']; ?>
            <small class="text-muted float-right"><?php echo get_past_time($notification['created_at']); ?></small>
        </p>
        <div class="text-muted mb-0 user-msg text-13">
            <?php echo ($notification['description']); ?>
        </div>
    </a>
<?php endforeach; ?>

<?php if($notifications->num_rows() == 0): ?>
    <div class="row mt-3">
        <div class="col-md-12 text-center">
            <img width="100px" src="<?php echo site_url('assets/global/image/empty-notification.png'); ?>">
            <h5 class="my-1"><?php echo get_phrase('No notification'); ?></h5>
            <p class="px-4 mx-3 my-1 text-10px text-muted"><small><?php echo get_phrase('Stay tuned!'); ?> <?php echo get_phrase('Notifications about your activity will show up here.'); ?></small></p>
            <a href="<?php echo site_url('admin/notification_settings'); ?>"><small><?php echo get_phrase('Notification Settings'); ?></small></a>
        </div>
    </div>
<?php endif; ?>