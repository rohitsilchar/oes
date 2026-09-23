<?php
    $logged_in_name = $this->session->userdata('name');
    if (empty($logged_in_name)) {
        $user_id = $this->session->userdata('user_id');
        $u_info = $this->user_model->get_all_user($user_id)->row_array();
        $logged_in_name = !empty($u_info) ? $u_info['first_name'] . ' ' . $u_info['last_name'] : 'Administrator';
    }

    $total_p = isset($total_pharmacists) ? (int)$total_pharmacists : 0;
    $lic_p = isset($licensed_pharmacists) ? (int)$licensed_pharmacists : 0;
    $lic_ratio = ($total_p > 0) ? round(($lic_p / $total_p) * 100) : 0;
    $total_c = isset($total_courses) ? (int)$total_courses : 0;
    $active_c = isset($active_courses_count) ? (int)$active_courses_count : 0;
    $total_e = isset($total_enrollments) ? (int)$total_enrollments : 0;
    $total_s = isset($total_stores) ? (int)$total_stores : 0;
?>

<style>
.qes-dashboard-hero {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 60%, #4776e6 100%);
    border-radius: 14px;
    color: #ffffff;
    padding: 26px 30px;
    box-shadow: 0 8px 24px rgba(30, 60, 114, 0.18);
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
}
.qes-dashboard-hero::after {
    content: "";
    position: absolute;
    right: -40px;
    bottom: -60px;
    width: 260px;
    height: 260px;
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
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}
.kpi-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
}
.kpi-blue { background: rgba(114, 124, 245, 0.12); color: #727cf5; }
.kpi-green { background: rgba(10, 207, 151, 0.12); color: #0acf97; }
.kpi-purple { background: rgba(155, 89, 182, 0.12); color: #9b59b6; }
.kpi-amber { background: rgba(255, 188, 0, 0.15); color: #ffbc00; }

.qes-quick-btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 8px 16px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}
.qes-quick-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.qes-panel-card {
    border-radius: 12px;
    border: 1px solid #ebedf2;
    background: #ffffff;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    margin-bottom: 24px;
}
.qes-panel-header {
    border-bottom: 1px solid #f1f3fa;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.avatar-initials {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #eef2ff;
    color: #4e73df;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}
</style>

<!-- Hero Welcome Banner -->
<div class="row">
    <div class="col-12">
        <div class="qes-dashboard-hero">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge badge-light-lighten px-3 py-1 font-12 text-white mb-2" style="background: rgba(255,255,255,0.18);">
                        <i class="mdi mdi-shield-check-outline mr-1"></i> QES Pharmacy Training & Compliance Portal
                    </span>
                    <h2 class="text-white font-weight-bold mb-1">
                        <?php echo get_phrase('welcome_back'); ?>, <?php echo htmlspecialchars($logged_in_name); ?>! 👋
                    </h2>
                    <p class="text-white-50 font-14 mb-0" style="max-width: 680px;">
                        Manage registered pharmacists, retail store networks, compliance drug licences, and employee training certifications all in one centralized hub.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                    <div class="d-inline-block text-left text-lg-right">
                        <div class="text-white font-weight-semibold font-15">
                            <i class="mdi mdi-calendar-clock mr-1"></i> <?php echo date('l, d F Y'); ?>
                        </div>
                        <div class="text-white-50 font-12 mt-1">
                            <span class="badge badge-success px-2 py-1"><i class="mdi mdi-check-circle-outline mr-1"></i> System Online</span>
                            <span class="ml-1"><?php echo $total_s; ?> Store(s) Connected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Command Bar -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px; background: #ffffff;">
            <div class="card-body p-2 d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center mb-1 mb-md-0 ml-2">
                    <i class="mdi mdi-flash text-warning font-20 mr-1"></i>
                    <strong class="font-13 text-dark"><?php echo get_phrase('quick_actions'); ?>:</strong>
                </div>
                <div class="d-flex flex-wrap" style="gap: 8px;">
                    <a href="<?php echo site_url('admin/user_form/add_user_form'); ?>" class="btn btn-sm btn-primary qes-quick-btn shadow-sm">
                        <i class="mdi mdi-ocr mr-1"></i> <?php echo get_phrase('add_pharmacist_ocr'); ?>
                    </a>
                    <a href="<?php echo site_url('admin/enrol_student'); ?>" class="btn btn-sm btn-success qes-quick-btn shadow-sm">
                        <i class="mdi mdi-account-multiple-plus mr-1"></i> <?php echo get_phrase('enroll_pharmacists'); ?>
                    </a>
                    <a href="<?php echo site_url('admin/store_form/add'); ?>" class="btn btn-sm btn-info qes-quick-btn shadow-sm">
                        <i class="mdi mdi-store-plus mr-1"></i> <?php echo get_phrase('add_new_store'); ?>
                    </a>
                    <a href="<?php echo site_url('admin/course_form/add_course'); ?>" class="btn btn-sm btn-secondary qes-quick-btn shadow-sm">
                        <i class="mdi mdi-book-plus mr-1"></i> <?php echo get_phrase('create_course'); ?>
                    </a>
                    <a href="<?php echo site_url('admin/enrol_history'); ?>" class="btn btn-sm btn-outline-dark qes-quick-btn">
                        <i class="mdi mdi-history mr-1"></i> <?php echo get_phrase('enrol_history'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4 Key Performance Stat Cards -->
<div class="row mb-4">
    <!-- Total Pharmacists -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <a href="<?php echo site_url('admin/users'); ?>" class="text-decoration-none">
            <div class="qes-kpi-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="text-muted font-12 font-weight-bold text-uppercase tracking-wider">
                            <?php echo get_phrase('pharmacists'); ?>
                        </span>
                        <h3 class="font-weight-bold text-dark my-1"><?php echo $total_p; ?></h3>
                    </div>
                    <div class="kpi-icon-box kpi-blue shadow-sm">
                        <i class="mdi mdi-account-group"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between font-12 text-muted">
                    <span><i class="mdi mdi-certificate text-success mr-1"></i><strong><?php echo $lic_p; ?></strong> Licensed</span>
                    <span class="badge badge-success-lighten font-11 font-weight-semibold"><?php echo $lic_ratio; ?>% Compliance</span>
                </div>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $lic_ratio; ?>%;"></div>
                </div>
            </div>
        </a>
    </div>

    <!-- Course Enrollments -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <a href="<?php echo site_url('admin/enrol_history'); ?>" class="text-decoration-none">
            <div class="qes-kpi-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="text-muted font-12 font-weight-bold text-uppercase tracking-wider">
                            <?php echo get_phrase('course_enrollments'); ?>
                        </span>
                        <h3 class="font-weight-bold text-dark my-1"><?php echo $total_e; ?></h3>
                    </div>
                    <div class="kpi-icon-box kpi-green shadow-sm">
                        <i class="mdi mdi-checkbox-multiple-marked-circle-outline"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between font-12 text-muted">
                    <span><i class="mdi mdi-book-open-outline text-success mr-1"></i>Training Records</span>
                    <span class="text-success font-weight-semibold"><i class="mdi mdi-arrow-up-bold font-10"></i> Active</span>
                </div>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div>
                </div>
            </div>
        </a>
    </div>

    <!-- Training Courses -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <a href="<?php echo site_url('admin/courses'); ?>" class="text-decoration-none">
            <div class="qes-kpi-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="text-muted font-12 font-weight-bold text-uppercase tracking-wider">
                            <?php echo get_phrase('training_courses'); ?>
                        </span>
                        <h3 class="font-weight-bold text-dark my-1"><?php echo $total_c; ?></h3>
                    </div>
                    <div class="kpi-icon-box kpi-purple shadow-sm">
                        <i class="mdi mdi-school-outline"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between font-12 text-muted">
                    <span><i class="mdi mdi-check-circle text-info mr-1"></i><strong><?php echo $active_c; ?></strong> Published</span>
                    <span class="badge badge-info-lighten font-11"><?php echo max(0, $total_c - $active_c); ?> Pending</span>
                </div>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-purple" role="progressbar" style="width: <?php echo ($total_c > 0) ? round(($active_c/$total_c)*100) : 0; ?>%;"></div>
                </div>
            </div>
        </a>
    </div>

    <!-- Pharmacy Stores -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <a href="<?php echo site_url('admin/stores'); ?>" class="text-decoration-none">
            <div class="qes-kpi-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="text-muted font-12 font-weight-bold text-uppercase tracking-wider">
                            <?php echo get_phrase('pharmacy_stores'); ?>
                        </span>
                        <h3 class="font-weight-bold text-dark my-1"><?php echo $total_s; ?></h3>
                    </div>
                    <div class="kpi-icon-box kpi-amber shadow-sm">
                        <i class="mdi mdi-storefront-outline"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between font-12 text-muted">
                    <span><i class="mdi mdi-map-marker text-warning mr-1"></i>Retail Network</span>
                    <span class="badge badge-warning-lighten font-11">Locations</span>
                </div>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 100%;"></div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Interactive Analytics Grid -->
<div class="row">
    <!-- Left: Monthly Enrollment Trend Chart -->
    <div class="col-xl-8 mb-4">
        <div class="qes-panel-card h-100">
            <div class="qes-panel-header">
                <div>
                    <h5 class="header-title font-weight-bold text-dark mb-0">
                        <i class="mdi mdi-chart-bell-curve-cumulative text-primary mr-1"></i>
                        <?php echo get_phrase('training_enrollment_trend'); ?> (<?php echo date('Y'); ?>)
                    </h5>
                    <small class="text-muted"><?php echo get_phrase('monthly_pharmacist_training_certifications_logged'); ?></small>
                </div>
                <a href="<?php echo site_url('admin/enrol_history'); ?>" class="btn btn-sm btn-outline-primary font-12">
                    <i class="mdi mdi-file-document-box-multiple-outline mr-1"></i><?php echo get_phrase('view_all'); ?>
                </a>
            </div>
            <div class="card-body p-3">
                <div class="chartjs-chart" style="height: 300px; position: relative;">
                    <canvas id="enrollment-trend-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Compliance & Status Doughnut -->
    <div class="col-xl-4 mb-4">
        <div class="qes-panel-card h-100">
            <div class="qes-panel-header">
                <div>
                    <h5 class="header-title font-weight-bold text-dark mb-0">
                        <i class="mdi mdi-pie-chart text-success mr-1"></i>
                        <?php echo get_phrase('compliance_overview'); ?>
                    </h5>
                    <small class="text-muted"><?php echo get_phrase('licensing_and_course_health'); ?></small>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="chartjs-chart my-2" style="height: 190px; position: relative;">
                    <canvas id="compliance-status-chart"></canvas>
                </div>
                <div class="mt-3">
                    <div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded bg-light">
                        <div class="d-flex align-items-center">
                            <span class="mr-2" style="width: 12px; height: 12px; border-radius: 3px; background: #0acf97;"></span>
                            <span class="font-12 font-weight-medium text-dark"><?php echo get_phrase('licensed_pharmacists'); ?></span>
                        </div>
                        <span class="badge badge-success font-12"><?php echo $lic_p; ?></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded bg-light">
                        <div class="d-flex align-items-center">
                            <span class="mr-2" style="width: 12px; height: 12px; border-radius: 3px; background: #ffbc00;"></span>
                            <span class="font-12 font-weight-medium text-dark"><?php echo get_phrase('pending_licence'); ?></span>
                        </div>
                        <span class="badge badge-warning font-12"><?php echo max(0, $total_p - $lic_p); ?></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                        <div class="d-flex align-items-center">
                            <span class="mr-2" style="width: 12px; height: 12px; border-radius: 3px; background: #727cf5;"></span>
                            <span class="font-12 font-weight-medium text-dark"><?php echo get_phrase('active_courses'); ?></span>
                        </div>
                        <span class="badge badge-primary font-12"><?php echo $active_c; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Operational Data Row: Recent Enrollments & Stores Overview -->
<div class="row">
    <!-- Left Column: Recent Enrollments -->
    <div class="col-xl-7 mb-4">
        <div class="qes-panel-card h-100">
            <div class="qes-panel-header">
                <div>
                    <h5 class="header-title font-weight-bold text-dark mb-0">
                        <i class="mdi mdi-history text-primary mr-1"></i>
                        <?php echo get_phrase('recent_pharmacist_enrollments'); ?>
                    </h5>
                    <small class="text-muted"><?php echo get_phrase('latest_course_training_assignments'); ?></small>
                </div>
                <a href="<?php echo site_url('admin/enrol_history'); ?>" class="btn btn-sm btn-link text-primary font-12 p-0 font-weight-semibold">
                    <?php echo get_phrase('view_full_history'); ?> &rarr;
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 font-13">
                        <thead class="bg-light">
                            <tr>
                                <th><?php echo get_phrase('pharmacist'); ?></th>
                                <th><?php echo get_phrase('course'); ?></th>
                                <th><?php echo get_phrase('store'); ?></th>
                                <th><?php echo get_phrase('date'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_enrollments)): ?>
                                <?php foreach ($recent_enrollments as $enr): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-initials mr-2">
                                                    <?php echo strtoupper(substr($enr['first_name'] ?? 'P', 0, 1) . substr($enr['last_name'] ?? '', 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <a href="<?php echo site_url('admin/user_form/edit_user_form/' . $enr['user_id']); ?>" class="text-dark font-weight-bold d-block mb-0">
                                                        <?php echo htmlspecialchars(($enr['first_name'] ?? '') . ' ' . ($enr['last_name'] ?? '')); ?>
                                                    </a>
                                                    <?php if (!empty($enr['licence_no'])): ?>
                                                        <span class="badge badge-success-lighten font-10">
                                                            <i class="mdi mdi-check-decagram mr-1"></i><?php echo htmlspecialchars($enr['licence_no']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars($enr['email'] ?? ''); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="font-weight-semibold text-dark d-block text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($enr['course_title'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($enr['course_title'] ?? get_phrase('course_enrolled')); ?>
                                            </span>
                                            <small class="text-muted">ID: #<?php echo $enr['course_id']; ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($enr['store_name'])): ?>
                                                <span class="badge badge-light-lighten text-dark font-11 px-2 py-1">
                                                    <i class="mdi mdi-store mr-1 text-primary"></i><?php echo htmlspecialchars($enr['store_name']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted font-11"><em><?php echo get_phrase('no_store'); ?></em></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="text-muted font-12">
                                                <?php echo !empty($enr['enrol_date']) ? date('d M Y', $enr['enrol_date']) : '-'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="mdi mdi-information-outline font-22 d-block mb-1"></i>
                                        <?php echo get_phrase('no_enrollments_recorded_yet'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Stores Network & Compliance -->
    <div class="col-xl-5 mb-4">
        <div class="qes-panel-card h-100">
            <div class="qes-panel-header">
                <div>
                    <h5 class="header-title font-weight-bold text-dark mb-0">
                        <i class="mdi mdi-store text-warning mr-1"></i>
                        <?php echo get_phrase('pharmacy_stores_network'); ?>
                    </h5>
                    <small class="text-muted"><?php echo get_phrase('retail_locations_and_staffing'); ?></small>
                </div>
                <a href="<?php echo site_url('admin/stores'); ?>" class="btn btn-sm btn-link text-warning font-12 p-0 font-weight-semibold">
                    <?php echo get_phrase('manage_stores'); ?> &rarr;
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 font-13">
                        <thead class="bg-light">
                            <tr>
                                <th><?php echo get_phrase('store'); ?></th>
                                <th><?php echo get_phrase('location'); ?></th>
                                <th><?php echo get_phrase('staff'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($stores_list)): ?>
                                <?php foreach ($stores_list as $st): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo site_url('admin/store_form/edit/' . $st['id']); ?>" class="text-dark font-weight-bold d-block">
                                                <?php echo htmlspecialchars($st['store_name']); ?>
                                            </a>
                                            <span class="badge badge-secondary-lighten font-10">
                                                <?php echo htmlspecialchars($st['store_code'] ?? 'STR'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-dark d-block font-12">
                                                <?php echo htmlspecialchars($st['city'] ?? ''); ?>
                                                <?php if (!empty($st['pin_code'])) echo ' - ' . htmlspecialchars($st['pin_code']); ?>
                                            </span>
                                            <small class="text-muted"><?php echo htmlspecialchars($st['state'] ?? ''); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary-lighten font-12 px-2 py-1">
                                                <i class="mdi mdi-account mr-1"></i><?php echo (int)($st['pharmacist_count'] ?? 0); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="mdi mdi-store-outline font-22 d-block mb-1"></i>
                                        <?php echo get_phrase('no_stores_found'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
