<?php
    $instructor_id = $this->session->userdata('user_id');
    $instructor_details = $this->user_model->get_all_user($instructor_id)->row_array();
    $instructor_name = !empty($instructor_details) ? $instructor_details['first_name'] . ' ' . $instructor_details['last_name'] : 'Instructor';

    $number_of_courses = $this->crud_model->get_instructor_wise_courses($instructor_id)->num_rows();
    $number_of_enrolment_result = $this->crud_model->instructor_wise_enrolment($instructor_id);
    if ($number_of_enrolment_result) {
        $number_of_enrolment = $number_of_enrolment_result->num_rows();
    } else {
        $number_of_enrolment = 0;
    }
    $total_pending_amount = $this->crud_model->get_total_pending_amount($instructor_id);
    $requested_withdrawal_amount = $this->crud_model->get_requested_withdrawal_amount($instructor_id);

    $active_courses_count = $this->crud_model->get_status_wise_courses_for_instructor('active')->num_rows();
    $pending_courses_count = $this->crud_model->get_status_wise_courses_for_instructor('pending')->num_rows();
?>

<style>
.qes-user-hero {
    background: linear-gradient(135deg, #1f4037 0%, #99f2c8 100%);
    background: linear-gradient(135deg, #141e30 0%, #243b55 100%);
    border-radius: 14px;
    color: #ffffff;
    padding: 24px 28px;
    box-shadow: 0 8px 24px rgba(20, 30, 48, 0.2);
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
}
.qes-user-hero::after {
    content: "";
    position: absolute;
    right: -40px;
    bottom: -50px;
    width: 240px;
    height: 240px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.qes-kpi-card {
    border-radius: 12px;
    border: 1px solid #ebedf2;
    background: #ffffff;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
    height: 100%;
}
.qes-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
}
.kpi-icon-box {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.kpi-icon-box i {
    font-size: 24px;
    line-height: 1;
    display: inline-block;
}
.kpi-blue { background: rgba(114, 124, 245, 0.12); color: #727cf5; }
.kpi-green { background: rgba(10, 207, 151, 0.12); color: #0acf97; }
.kpi-purple { background: rgba(155, 89, 182, 0.12); color: #9b59b6; }
.kpi-amber { background: rgba(255, 188, 0, 0.15); color: #ffbc00; }
</style>

<!-- Instructor Hero Banner -->
<div class="row">
    <div class="col-12">
        <div class="qes-user-hero">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge badge-light-lighten px-3 py-1 font-12 text-white mb-2" style="background: rgba(255,255,255,0.18);">
                        <i class="mdi mdi-school mr-1"></i> <?php echo get_phrase('instructor_portal'); ?>
                    </span>
                    <h2 class="text-white font-weight-bold mb-1">
                        <?php echo get_phrase('welcome_back'); ?>, <?php echo htmlspecialchars($instructor_name); ?>! 👋
                    </h2>
                    <p class="text-white-50 font-14 mb-0">
                        Monitor course enrolments, trainee engagement, curriculum delivery, and revenue payouts in real-time.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                    <div class="d-inline-flex flex-wrap" style="gap: 8px;">
                        <a href="<?php echo site_url('user/course_form/add_course'); ?>" class="btn btn-sm btn-primary shadow-sm" style="border-radius: 8px;">
                            <i class="mdi mdi-plus-circle mr-1"></i> <?php echo get_phrase('create_course'); ?>
                        </a>
                        <a href="<?php echo site_url('user/courses'); ?>" class="btn btn-sm btn-light shadow-sm" style="border-radius: 8px;">
                            <i class="mdi mdi-book-open mr-1"></i> <?php echo get_phrase('manage_courses'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4 Key Metric Cards -->
<div class="row mb-4">
    <!-- Courses -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <a href="<?php echo site_url('user/courses'); ?>" class="text-decoration-none">
            <div class="qes-kpi-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="text-muted font-12 font-weight-bold text-uppercase tracking-wider">
                            <?php echo get_phrase('my_courses'); ?>
                        </span>
                        <h3 class="font-weight-bold text-dark my-1"><?php echo $number_of_courses; ?></h3>
                    </div>
                    <div class="kpi-icon-box kpi-blue shadow-sm">
                        <i class="mdi mdi-book-multiple"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between font-12 text-muted">
                    <span><i class="mdi mdi-check-circle text-success mr-1"></i><?php echo $active_courses_count; ?> <?php echo get_phrase('active'); ?></span>
                    <span class="badge badge-info-lighten font-11"><?php echo $pending_courses_count; ?> <?php echo get_phrase('pending'); ?></span>
                </div>
            </div>
        </a>
    </div>

    <!-- Enrolments -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <div class="qes-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <span class="text-muted font-12 font-weight-bold text-uppercase tracking-wider">
                        <?php echo get_phrase('enrolled_students'); ?>
                    </span>
                    <h3 class="font-weight-bold text-dark my-1"><?php echo $number_of_enrolment; ?></h3>
                </div>
                <div class="kpi-icon-box kpi-green shadow-sm">
                    <i class="mdi mdi-account-group"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between font-12 text-muted">
                <span><i class="mdi mdi-chart-line text-success mr-1"></i><?php echo get_phrase('total_pharmacists_trained'); ?></span>
                <span class="text-success font-weight-semibold"><i class="mdi mdi-arrow-up font-10"></i> Active</span>
            </div>
        </div>
    </div>

    <!-- Pending Balance -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <a href="<?php echo site_url('user/payout_report'); ?>" class="text-decoration-none">
            <div class="qes-kpi-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="text-muted font-12 font-weight-bold text-uppercase tracking-wider">
                            <?php echo get_phrase('pending_balance'); ?>
                        </span>
                        <h3 class="font-weight-bold text-dark my-1">
                            <?php echo $total_pending_amount > 0 ? currency($total_pending_amount) : currency_code_and_symbol().''.$total_pending_amount; ?>
                        </h3>
                    </div>
                    <div class="kpi-icon-box kpi-purple shadow-sm">
                        <i class="mdi mdi-wallet-outline"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between font-12 text-muted">
                    <span><i class="mdi mdi-cash-multiple text-info mr-1"></i><?php echo get_phrase('ready_to_withdraw'); ?></span>
                    <span class="badge badge-purple-lighten font-11"><?php echo get_phrase('balance'); ?></span>
                </div>
            </div>
        </a>
    </div>

    <!-- Requested Withdrawal -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <a href="<?php echo site_url('user/payout_report'); ?>" class="text-decoration-none">
            <div class="qes-kpi-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="text-muted font-12 font-weight-bold text-uppercase tracking-wider">
                            <?php echo get_phrase('requested_withdrawal'); ?>
                        </span>
                        <h3 class="font-weight-bold text-dark my-1">
                            <?php echo $requested_withdrawal_amount > 0 ? currency($requested_withdrawal_amount) : currency_code_and_symbol().''.$requested_withdrawal_amount; ?>
                        </h3>
                    </div>
                    <div class="kpi-icon-box kpi-amber shadow-sm">
                        <i class="mdi mdi-credit-card"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between font-12 text-muted">
                    <span><i class="mdi mdi-clock-outline text-warning mr-1"></i><?php echo get_phrase('in_processing'); ?></span>
                    <span class="badge badge-warning-lighten font-11"><?php echo get_phrase('payout'); ?></span>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Revenue Analytics & Course Overview Grid -->
<div class="row">
    <div class="col-xl-8 mb-4">
        <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="header-title font-weight-bold text-dark mb-0">
                        <i class="mdi mdi-chart-areaspline text-primary mr-1"></i>
                        <?php echo get_phrase('instructor_revenue_trend'); ?> (<?php echo date('Y'); ?>)
                    </h5>
                    <a href="<?php echo site_url('user/payout_report'); ?>" class="btn btn-sm btn-link text-primary p-0 font-12 font-weight-semibold">
                        <?php echo get_phrase('payout_details'); ?> &rarr;
                    </a>
                </div>
                <div class="chartjs-chart" style="height: 300px; position: relative;">
                    <canvas id="task-area-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 mb-4">
        <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-4">
                <h5 class="header-title font-weight-bold text-dark mb-3">
                    <i class="mdi mdi-chart-pie text-info mr-1"></i>
                    <?php echo get_phrase('course_status_overview'); ?>
                </h5>
                <div class="chartjs-chart my-2" style="height: 200px; position: relative;">
                    <canvas id="project-status-chart"></canvas>
                </div>
                <div class="row text-center mt-3 pt-2 border-top">
                    <div class="col-6">
                        <h4 class="font-weight-bold text-success mb-0"><?php echo $active_courses_count; ?></h4>
                        <small class="text-muted"><?php echo get_phrase('active_courses'); ?></small>
                    </div>
                    <div class="col-6">
                        <h4 class="font-weight-bold text-warning mb-0"><?php echo $pending_courses_count; ?></h4>
                        <small class="text-muted"><?php echo get_phrase('pending_courses'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
