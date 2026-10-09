<?php
    $logged_in_name = $this->session->userdata('name');
    if (empty($logged_in_name)) {
        $user_id = $this->session->userdata('user_id');
        $u_info = $this->user_model->get_all_user($user_id)->row_array();
        $logged_in_name = !empty($u_info) ? $u_info['first_name'] . ' ' . $u_info['last_name'] : 'Administrator';
    }

    $total_p = isset($total_pharmacists) ? (int)$total_pharmacists : 0;
    $lic_p = isset($licensed_pharmacists) ? (int)$licensed_pharmacists : 0;
    $unlic_p = max(0, $total_p - $lic_p);
    $lic_ratio = ($total_p > 0) ? round(($lic_p / $total_p) * 100) : 0;

    $total_c = isset($total_courses) ? (int)$total_courses : 0;
    $active_c = isset($active_courses_count) ? (int)$active_courses_count : 0;
    $pending_c = isset($pending_courses_count) ? (int)$pending_courses_count : 0;

    $total_e = isset($total_enrollments) ? (int)$total_enrollments : 0;
    $month_e = isset($month_enrollments) ? (int)$month_enrollments : 0;

    $total_s = isset($total_stores) ? (int)$total_stores : 0;
    $unstaffed_s_count = isset($unstaffed_stores) ? count($unstaffed_stores) : 0;
    $staffed_s_count = max(0, $total_s - $unstaffed_s_count);

    $assigned_p = isset($assigned_pharmacists) ? (int)$assigned_pharmacists : 0;
    $unassigned_p = isset($unassigned_pharmacists) ? (int)$unassigned_pharmacists : 0;

    // Compliance color rating
    $compliance_badge_class = 'badge-success';
    $compliance_text_color = '#0acf97';
    $compliance_status_label = get_phrase('optimal_compliance');
    if ($lic_ratio < 50) {
        $compliance_badge_class = 'badge-danger';
        $compliance_text_color = '#fa5c7c';
        $compliance_status_label = get_phrase('critical_attention_required');
    } elseif ($lic_ratio < 80) {
        $compliance_badge_class = 'badge-warning';
        $compliance_text_color = '#ffbc00';
        $compliance_status_label = get_phrase('moderate_compliance');
    }

    $has_compliance_issues = ($unlic_p > 0 || $unstaffed_s_count > 0 || $pending_c > 0);
?>

<style>
/* Modern QES Dashboard Styles */
.qes-dashboard-hero {
    background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
    border-radius: 16px;
    color: #ffffff;
    padding: 26px 32px;
    box-shadow: 0 10px 30px rgba(15, 32, 39, 0.25);
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
}
.qes-dashboard-hero::after {
    content: "";
    position: absolute;
    right: -50px;
    bottom: -60px;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.10) 0%, rgba(255, 255, 255, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

/* Card Elevate & Transition */
.qes-kpi-card {
    border-radius: 14px;
    border: 1px solid #eef2f6;
    background: #ffffff;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    transition: all 0.28s cubic-bezier(0.25, 0.8, 0.25, 1);
    height: 100%;
    position: relative;
    overflow: hidden;
}
.qes-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    border-color: #dbe2ea;
}
.qes-kpi-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: transparent;
    transition: background 0.3s;
}
.qes-kpi-card.kpi-border-blue:hover::before { background: #727cf5; }
.qes-kpi-card.kpi-border-green:hover::before { background: #0acf97; }
.qes-kpi-card.kpi-border-amber:hover::before { background: #ffbc00; }
.qes-kpi-card.kpi-border-purple:hover::before { background: #9b59b6; }

.kpi-icon-box {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    flex-shrink: 0;
}
.kpi-icon-box i {
    font-size: 26px;
    line-height: 1;
    display: inline-block;
}
.kpi-blue { background: rgba(114, 124, 245, 0.12); color: #727cf5; }
.kpi-green { background: rgba(10, 207, 151, 0.12); color: #0acf97; }
.kpi-purple { background: rgba(155, 89, 182, 0.12); color: #9b59b6; }
.kpi-amber { background: rgba(255, 188, 0, 0.15); color: #ffbc00; }
.kpi-danger { background: rgba(250, 92, 124, 0.12); color: #fa5c7c; }

/* Quick Action Command Buttons */
.qes-quick-btn {
    border-radius: 10px;
    font-weight: 500;
    padding: 8px 16px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    border: none;
}
.qes-quick-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.qes-panel-card {
    border-radius: 14px;
    border: 1px solid #eef2f6;
    background: #ffffff;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    margin-bottom: 24px;
}
.qes-panel-header {
    border-bottom: 1px solid #f1f3fa;
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

/* Operational Hub Tabs */
.qes-nav-pills .nav-link {
    border-radius: 10px;
    color: #6c757d;
    font-weight: 600;
    font-size: 13px;
    padding: 10px 18px;
    border: 1px solid transparent;
    transition: all 0.2s ease;
    margin-right: 6px;
    display: flex;
    align-items: center;
}
.qes-nav-pills .nav-link:hover {
    color: #313a46;
    background: #f4f6fa;
}
.qes-nav-pills .nav-link.active {
    background: #727cf5;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(114, 124, 245, 0.35);
}

/* User Avatar Initials */
.avatar-initials {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #eef2ff;
    color: #4e73df;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    border: 1px solid rgba(114, 124, 245, 0.2);
    flex-shrink: 0;
}

/* Pulse Animation for System Online */
.pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    background: #0acf97;
    margin-right: 6px;
    box-shadow: 0 0 0 0 rgba(10, 207, 151, 0.7);
    animation: pulse-green 1.8s infinite;
}
@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(10, 207, 151, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(10, 207, 151, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(10, 207, 151, 0); }
}

/* Action Banner */
.action-alert-box {
    border-radius: 12px;
    border-left: 5px solid;
    padding: 14px 18px;
    margin-bottom: 20px;
    background: #ffffff;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
}
.action-alert-warning {
    border-left-color: #ffbc00;
    background: #fffdf5;
}
.action-alert-success {
    border-left-color: #0acf97;
    background: #f6fdfa;
}

/* Global In-Dashboard Search */
.qes-search-input-wrap {
    position: relative;
    max-width: 360px;
    width: 100%;
}
.qes-search-input-wrap i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #98a6ad;
    font-size: 16px;
}
.qes-search-input-wrap input {
    padding-left: 36px;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    font-size: 13px;
    transition: all 0.2s ease;
}
.qes-search-input-wrap input:focus {
    border-color: #727cf5;
    box-shadow: 0 0 0 3px rgba(114, 124, 245, 0.15);
}

/* Print Stylesheet for Executive Audit */
@media print {
    body, .content-page, .wrapper, .container-fluid {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .left-side-menu, .navbar-custom, .qes-dashboard-hero .btn, #dashboard-actions-row, .qes-nav-pills, .qes-search-input-wrap, .btn {
        display: none !important;
    }
    .qes-dashboard-hero {
        background: #203a43 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        padding: 15px !important;
    }
    .qes-panel-card, .qes-kpi-card {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
    }
}
</style>

<!-- 1. Executive Hero & Command Header -->
<div class="row">
    <div class="col-12">
        <div class="qes-dashboard-hero">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="badge badge-light-lighten px-3 py-1 font-12 text-white mb-2" style="background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.2);">
                        <i class="mdi mdi-shield-check mr-1"></i> QES Pharmacy Compliance & Training Command Center
                    </span>
                    <h2 class="text-white font-weight-bold mb-1">
                        <?php echo get_phrase('welcome_back'); ?>, <?php echo htmlspecialchars($logged_in_name); ?>! 👋
                    </h2>
                    <p class="text-white-50 font-14 mb-0" style="max-width: 620px;">
                        Continuous oversight of registered pharmacists, retail branch staffing, state pharmacy licences, and workforce certifications.
                    </p>
                </div>
                <div class="col-lg-5 text-lg-right mt-3 mt-lg-0">
                    <div class="d-inline-flex flex-column align-items-lg-end">
                        <div class="text-white font-weight-semibold font-15 mb-1">
                            <i class="mdi mdi-calendar-clock mr-1 text-info"></i> <?php echo date('l, d F Y'); ?>
                        </div>
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <span class="badge badge-dark px-2 py-1 font-12" style="background: rgba(255,255,255,0.15);">
                                <span class="pulse-dot"></span> System Live
                            </span>
                            <span class="badge <?php echo $compliance_badge_class; ?> px-2 py-1 font-12">
                                <i class="mdi mdi-check-decagram mr-1"></i> <?php echo $lic_ratio; ?>% <?php echo $compliance_status_label; ?>
                            </span>
                            <button type="button" onclick="window.print()" class="btn btn-sm btn-light font-12 shadow-sm">
                                <i class="mdi mdi-printer mr-1"></i> <?php echo get_phrase('print_audit_report'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Global Quick Command & Action Bar -->
<div class="row mb-3" id="dashboard-actions-row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; background: #ffffff;">
            <div class="card-body p-2 d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                <!-- Instant Action Buttons -->
                <div class="d-flex flex-wrap" style="gap: 8px;">
                    <a href="<?php echo site_url('admin/user_form/add_user_form'); ?>" class="btn btn-sm btn-primary qes-quick-btn shadow-sm">
                        <i class="mdi mdi-scanner mr-1"></i> <?php echo get_phrase('scan_licence_ocr'); ?>
                        <span class="badge badge-light text-primary font-10 ml-1 py-0 px-1">AI</span>
                    </a>
                    <a href="<?php echo site_url('admin/enrol_student'); ?>" class="btn btn-sm btn-success qes-quick-btn shadow-sm">
                        <i class="mdi mdi-account-multiple-plus mr-1"></i> <?php echo get_phrase('enroll_pharmacists'); ?>
                    </a>
                    <a href="<?php echo site_url('admin/store_form/add_store_form'); ?>" class="btn btn-sm btn-warning text-dark qes-quick-btn shadow-sm">
                        <i class="mdi mdi-store mr-1"></i> <?php echo get_phrase('add_new_store'); ?>
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

<!-- 3. Urgent Operational Risk & Compliance Action Tray -->
<?php if ($has_compliance_issues): ?>
<div class="row">
    <div class="col-12">
        <div class="action-alert-box action-alert-warning">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-alert-circle text-warning font-24 mr-3"></i>
                    <div>
                        <h6 class="font-weight-bold text-dark mb-1">
                            <?php echo get_phrase('compliance_action_required'); ?>:
                        </h6>
                        <div class="font-13 text-muted d-flex flex-wrap" style="gap: 14px;">
                            <?php if ($unlic_p > 0): ?>
                                <span>
                                    <strong class="text-danger"><i class="mdi mdi-certificate mr-1"></i><?php echo $unlic_p; ?></strong> <?php echo get_phrase('pharmacists_missing_licence_details'); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($unstaffed_s_count > 0): ?>
                                <span>
                                    <strong class="text-warning"><i class="mdi mdi-store mr-1"></i><?php echo $unstaffed_s_count; ?></strong> <?php echo get_phrase('retail_branch(es)_unstaffed'); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($pending_c > 0): ?>
                                <span>
                                    <strong class="text-info"><i class="mdi mdi-clock-outline mr-1"></i><?php echo $pending_c; ?></strong> <?php echo get_phrase('course(s)_pending_approval'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <?php if ($unlic_p > 0): ?>
                        <button type="button" onclick="activateDashboardTab('tab-licence')" class="btn btn-xs btn-outline-danger font-12 font-weight-semibold">
                            <i class="mdi mdi-arrow-right-circle mr-1"></i><?php echo get_phrase('resolve_licences'); ?>
                        </button>
                    <?php endif; ?>
                    <?php if ($unstaffed_s_count > 0): ?>
                        <button type="button" onclick="activateDashboardTab('tab-stores')" class="btn btn-xs btn-outline-warning font-12 font-weight-semibold">
                            <i class="mdi mdi-arrow-right-circle mr-1"></i><?php echo get_phrase('view_unstaffed_stores'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
    <div class="col-12">
        <div class="action-alert-box action-alert-success">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-check-circle text-success font-22 mr-2"></i>
                    <div>
                        <strong class="text-dark font-13"><?php echo get_phrase('regulatory_compliance_optimal'); ?></strong>
                        <span class="text-muted font-13 ml-2">— All retail stores are staffed and all registered pharmacists possess valid verified licences.</span>
                    </div>
                </div>
                <span class="badge badge-success font-11 px-2 py-1"><i class="mdi mdi-shield-check mr-1"></i>100% Verified</span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 4. Executive KPI Scorecard (4 High-Impact Stat Cards) -->
<div class="row mb-4">
    <!-- Card 1: Regulatory Compliance Rate -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <div class="qes-kpi-card kpi-border-green p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <span class="text-muted font-12 font-weight-bold text-uppercase tracking-wider">
                        <?php echo get_phrase('compliance_rate'); ?>
                    </span>
                    <h3 class="font-weight-bold text-dark my-1"><?php echo $lic_ratio; ?>%</h3>
                </div>
                <div class="kpi-icon-box kpi-green shadow-sm">
                    <i class="mdi mdi-certificate"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between font-12 text-muted">
                <span><i class="mdi mdi-check-circle text-success mr-1"></i><strong><?php echo $lic_p; ?></strong> <?php echo get_phrase('licensed'); ?></span>
                <span class="<?php echo ($unlic_p > 0) ? 'text-danger font-weight-bold' : 'text-muted'; ?>">
                    <?php echo $unlic_p; ?> <?php echo get_phrase('pending'); ?>
                </span>
            </div>
            <div class="progress mt-2" style="height: 5px; border-radius: 4px; background: #eef2f6;">
                <div class="progress-bar <?php echo ($lic_ratio >= 80) ? 'bg-success' : (($lic_ratio >= 50) ? 'bg-warning' : 'bg-danger'); ?>" 
                     role="progressbar" style="width: <?php echo $lic_ratio; ?>%;"></div>
            </div>
        </div>
    </div>

    <!-- Card 2: Pharmacist Workforce -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <a href="<?php echo site_url('admin/users'); ?>" class="text-decoration-none">
            <div class="qes-kpi-card kpi-border-blue p-3">
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
                    <span><i class="mdi mdi-store text-primary mr-1"></i><strong><?php echo $assigned_p; ?></strong> <?php echo get_phrase('in_stores'); ?></span>
                    <span class="badge badge-light-lighten text-dark font-11"><?php echo $unassigned_p; ?> <?php echo get_phrase('floating'); ?></span>
                </div>
                <div class="progress mt-2" style="height: 5px; border-radius: 4px; background: #eef2f6;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($total_p > 0) ? round(($assigned_p / $total_p) * 100) : 0; ?>%;"></div>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 3: Retail Pharmacy Stores -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <a href="<?php echo site_url('admin/stores'); ?>" class="text-decoration-none">
            <div class="qes-kpi-card kpi-border-amber p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="text-muted font-12 font-weight-bold text-uppercase tracking-wider">
                            <?php echo get_phrase('pharmacy_stores'); ?>
                        </span>
                        <h3 class="font-weight-bold text-dark my-1"><?php echo $total_s; ?></h3>
                    </div>
                    <div class="kpi-icon-box kpi-amber shadow-sm">
                        <i class="mdi mdi-store"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between font-12 text-muted">
                    <span><i class="mdi mdi-account-check text-success mr-1"></i><strong><?php echo $staffed_s_count; ?></strong> <?php echo get_phrase('staffed'); ?></span>
                    <?php if ($unstaffed_s_count > 0): ?>
                        <span class="badge badge-danger-lighten font-11 font-weight-bold"><?php echo $unstaffed_s_count; ?> <?php echo get_phrase('unstaffed'); ?></span>
                    <?php else: ?>
                        <span class="badge badge-success-lighten font-11"><?php echo get_phrase('100%_covered'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="progress mt-2" style="height: 5px; border-radius: 4px; background: #eef2f6;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo ($total_s > 0) ? round(($staffed_s_count / $total_s) * 100) : 100; ?>%;"></div>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 4: Training & Certifications -->
    <div class="col-sm-6 col-xl-3 mb-3 mb-xl-0">
        <a href="<?php echo site_url('admin/enrol_history'); ?>" class="text-decoration-none">
            <div class="qes-kpi-card kpi-border-purple p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="text-muted font-12 font-weight-bold text-uppercase tracking-wider">
                            <?php echo get_phrase('enrollments'); ?>
                        </span>
                        <h3 class="font-weight-bold text-dark my-1"><?php echo $total_e; ?></h3>
                    </div>
                    <div class="kpi-icon-box kpi-purple shadow-sm">
                        <i class="mdi mdi-school"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between font-12 text-muted">
                    <span><i class="mdi mdi-trending-up text-info mr-1"></i><strong><?php echo $month_e; ?></strong> <?php echo get_phrase('this_month'); ?></span>
                    <span class="badge badge-info-lighten font-11"><?php echo $active_c; ?> <?php echo get_phrase('active_courses'); ?></span>
                </div>
                <div class="progress mt-2" style="height: 5px; border-radius: 4px; background: #eef2f6;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 100%;"></div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- 5. Interactive Multi-Chart Intelligence Center -->
<div class="row">
    <!-- Left: Dual Mode Chart (Enrollment Trends OR Store Staffing) -->
    <div class="col-xl-8 mb-4">
        <div class="qes-panel-card h-100">
            <div class="qes-panel-header">
                <div>
                    <h5 class="header-title font-weight-bold text-dark mb-0">
                        <i class="mdi mdi-chart-areaspline text-primary mr-1"></i>
                        <span id="chart-panel-title"><?php echo get_phrase('workforce_training_velocity'); ?></span> (<?php echo date('Y'); ?>)
                    </h5>
                    <small class="text-muted"><?php echo get_phrase('interactive_pharmacist_training_records_and_store_distribution'); ?></small>
                </div>
                <div class="d-flex align-items-center" style="gap: 6px;">
                    <div class="btn-group btn-group-sm">
                        <button type="button" id="btn-view-enrol" onclick="toggleDashboardChart('enrol')" class="btn btn-primary font-12">
                            <i class="mdi mdi-chart-line mr-1"></i><?php echo get_phrase('enrollments_trend'); ?>
                        </button>
                        <button type="button" id="btn-view-store" onclick="toggleDashboardChart('store')" class="btn btn-outline-primary font-12">
                            <i class="mdi mdi-chart-bar mr-1"></i><?php echo get_phrase('store_staffing'); ?>
                        </button>
                    </div>
                    <a href="<?php echo site_url('admin/enrol_history'); ?>" class="btn btn-sm btn-link text-primary font-12 p-0 ml-2">
                        <?php echo get_phrase('view_data'); ?> &rarr;
                    </a>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- Enrollment Trend Container -->
                <div id="chart-wrap-enrol" class="chartjs-chart" style="height: 310px; position: relative;">
                    <canvas id="enrollment-trend-chart"></canvas>
                </div>
                <!-- Store Staffing Container -->
                <div id="chart-wrap-store" class="chartjs-chart" style="height: 310px; position: relative; display: none;">
                    <canvas id="store-staffing-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Compliance & Credentialing Doughnut -->
    <div class="col-xl-4 mb-4">
        <div class="qes-panel-card h-100">
            <div class="qes-panel-header">
                <div>
                    <h5 class="header-title font-weight-bold text-dark mb-0">
                        <i class="mdi mdi-shield-check text-success mr-1"></i>
                        <?php echo get_phrase('compliance_breakdown'); ?>
                    </h5>
                    <small class="text-muted"><?php echo get_phrase('audit_readiness_indicator'); ?></small>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="chartjs-chart my-2" style="height: 195px; position: relative;">
                    <canvas id="compliance-status-chart"></canvas>
                </div>
                <div class="mt-3">
                    <div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded bg-light">
                        <div class="d-flex align-items-center">
                            <span class="mr-2" style="width: 12px; height: 12px; border-radius: 3px; background: #0acf97;"></span>
                            <span class="font-12 font-weight-semibold text-dark"><?php echo get_phrase('licensed_pharmacists'); ?></span>
                        </div>
                        <span class="badge badge-success font-12 font-weight-bold"><?php echo $lic_p; ?></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded bg-light">
                        <div class="d-flex align-items-center">
                            <span class="mr-2" style="width: 12px; height: 12px; border-radius: 3px; background: #fa5c7c;"></span>
                            <span class="font-12 font-weight-semibold text-dark"><?php echo get_phrase('licence_pending_upload'); ?></span>
                        </div>
                        <span class="badge badge-danger font-12 font-weight-bold"><?php echo $unlic_p; ?></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                        <div class="d-flex align-items-center">
                            <span class="mr-2" style="width: 12px; height: 12px; border-radius: 3px; background: #727cf5;"></span>
                            <span class="font-12 font-weight-semibold text-dark"><?php echo get_phrase('active_training_courses'); ?></span>
                        </div>
                        <span class="badge badge-primary font-12 font-weight-bold"><?php echo $active_c; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    $recent_enrollments     = array_slice($recent_enrollments ?? [], 0, 10);
    $stores_list            = array_slice($stores_list ?? [], 0, 10);
    $unlicensed_pharmacists = array_slice($unlicensed_pharmacists ?? [], 0, 10);
    $top_courses            = array_slice($top_courses ?? [], 0, 10);
?>
<!-- 6. Unified Operational Hub (Multi-Tab Interactive Workspace) -->
<div class="row">
    <div class="col-12">
        <div class="qes-panel-card">
            <div class="qes-panel-header">
                <div>
                    <h5 class="header-title font-weight-bold text-dark mb-0">
                        <i class="mdi mdi-view-dashboard-outline text-primary mr-1"></i>
                        <?php echo get_phrase('operational_command_hub'); ?>
                    </h5>
                    <small class="text-muted"><?php echo get_phrase('showing_10_most_recent_records_across_operational_queues'); ?></small>
                </div>

                <!-- Navigation Tabs -->
                <ul class="nav qes-nav-pills mt-2 mt-md-0" id="dashboardHubTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-enrollments-btn" data-toggle="pill" href="#tab-enrollments" role="tab">
                            <i class="mdi mdi-account-clock-outline mr-1"></i><?php echo get_phrase('recent_enrollments'); ?>
                            <span class="badge badge-primary-lighten ml-2"><?php echo count($recent_enrollments); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-stores-btn" data-toggle="pill" href="#tab-stores" role="tab">
                            <i class="mdi mdi-store mr-1"></i><?php echo get_phrase('recent_stores'); ?>
                            <span class="badge badge-warning-lighten ml-2"><?php echo count($stores_list); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-licence-btn" data-toggle="pill" href="#tab-licence" role="tab">
                            <i class="mdi mdi-alert-decagram mr-1 <?php echo ($unlic_p > 0) ? 'text-danger' : ''; ?>"></i><?php echo get_phrase('licence_queue'); ?>
                            <?php if ($unlic_p > 0): ?>
                                <span class="badge badge-danger ml-2"><?php echo count($unlicensed_pharmacists); ?></span>
                            <?php else: ?>
                                <span class="badge badge-success ml-2">0</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-courses-btn" data-toggle="pill" href="#tab-courses" role="tab">
                            <i class="mdi mdi-book-open-page-variant mr-1"></i><?php echo get_phrase('top_courses'); ?>
                            <span class="badge badge-info-lighten ml-2"><?php echo count($top_courses); ?></span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="dashboardHubTabContent">

                    <!-- Tab 1: Recent Enrollments -->
                    <div class="tab-pane fade show active" id="tab-enrollments" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 font-13 qes-data-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th><?php echo get_phrase('pharmacist'); ?></th>
                                        <th><?php echo get_phrase('course_training'); ?></th>
                                        <th><?php echo get_phrase('assigned_store'); ?></th>
                                        <th><?php echo get_phrase('enrolled_date'); ?></th>
                                        <th><?php echo get_phrase('expiry_status'); ?></th>
                                        <th class="text-right"><?php echo get_phrase('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_enrollments)): ?>
                                        <?php foreach ($recent_enrollments as $enr): ?>
                                            <tr class="qes-searchable-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-initials mr-2">
                                                            <?php echo strtoupper(substr($enr['first_name'] ?? 'P', 0, 1) . substr($enr['last_name'] ?? '', 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <a href="<?php echo site_url('admin/user_form/edit_user_form/' . $enr['user_id']); ?>" class="text-dark font-weight-bold d-block mb-0">
                                                                <?php echo htmlspecialchars(($enr['first_name'] ?? '') . ' ' . ($enr['last_name'] ?? '')); ?>
                                                            </a>
                                                            <?php if (!empty($enr['employee_id'])): ?>
                                                                <small class="badge badge-light text-dark font-10">ID: <?php echo htmlspecialchars($enr['employee_id']); ?></small>
                                                            <?php endif; ?>
                                                            <?php if (!empty($enr['licence_no'])): ?>
                                                                <span class="badge badge-success-lighten font-10 ml-1">
                                                                    <i class="mdi mdi-check-decagram mr-1"></i><?php echo htmlspecialchars($enr['licence_no']); ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge badge-danger-lighten font-10 ml-1"><?php echo get_phrase('no_licence'); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="font-weight-semibold text-dark d-block text-truncate" style="max-width: 260px;" title="<?php echo htmlspecialchars($enr['course_title'] ?? ''); ?>">
                                                        <?php echo htmlspecialchars($enr['course_title'] ?? get_phrase('course_enrolled')); ?>
                                                    </span>
                                                    <small class="text-muted">#<?php echo $enr['course_id']; ?></small>
                                                </td>
                                                <td>
                                                    <?php if (!empty($enr['store_name'])): ?>
                                                        <span class="badge badge-light-lighten text-dark font-11 px-2 py-1">
                                                            <i class="mdi mdi-store mr-1 text-primary"></i><?php echo htmlspecialchars($enr['store_name']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted font-11"><em><?php echo get_phrase('unassigned'); ?></em></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="text-muted font-12">
                                                        <?php echo !empty($enr['enrol_date']) ? date('d M Y', $enr['enrol_date']) : '-'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($enr['expiry_date']) && $enr['expiry_date'] != '0'): ?>
                                                        <?php $is_expired = (strtotime($enr['expiry_date']) < time()); ?>
                                                        <span class="badge <?php echo $is_expired ? 'badge-danger-lighten' : 'badge-success-lighten'; ?> font-11">
                                                            <?php echo $is_expired ? get_phrase('expired') : date('d M Y', strtotime($enr['expiry_date'])); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary-lighten font-11"><?php echo get_phrase('lifetime'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-right">
                                                    <a href="<?php echo site_url('admin/user_form/edit_user_form/' . $enr['user_id']); ?>" class="btn btn-xs btn-outline-primary" title="<?php echo get_phrase('view_pharmacist'); ?>">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="mdi mdi-information-outline font-22 d-block mb-1"></i>
                                                <?php echo get_phrase('no_enrollments_recorded_yet'); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 border-top bg-light d-flex justify-content-between align-items-center">
                            <small class="text-muted"><?php echo get_phrase('showing_up_to_10_recent_enrollments'); ?> (<?php echo $total_e; ?> <?php echo get_phrase('total_enrollments'); ?>)</small>
                            <a href="<?php echo site_url('admin/enrol_history'); ?>" class="btn btn-sm btn-link font-weight-semibold text-primary">
                                <?php echo get_phrase('view_all_enrollments'); ?> <i class="mdi mdi-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Tab 2: Store Network & Staffing Matrix -->
                    <div class="tab-pane fade" id="tab-stores" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 font-13 qes-data-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th><?php echo get_phrase('store_name'); ?></th>
                                        <th><?php echo get_phrase('store_code'); ?></th>
                                        <th><?php echo get_phrase('location'); ?></th>
                                        <th><?php echo get_phrase('pharmacists_staffed'); ?></th>
                                        <th><?php echo get_phrase('staffing_health'); ?></th>
                                        <th class="text-right"><?php echo get_phrase('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($stores_list)): ?>
                                        <?php foreach ($stores_list as $st): ?>
                                            <?php $p_cnt = (int)($st['pharmacist_count'] ?? 0); ?>
                                            <tr class="qes-searchable-row">
                                                <td>
                                                    <a href="<?php echo site_url('admin/store_form/edit_store_form/' . $st['id']); ?>" class="text-dark font-weight-bold d-block">
                                                        <?php echo htmlspecialchars($st['store_name']); ?>
                                                    </a>
                                                    <?php if (!empty($st['phone'])): ?>
                                                        <small class="text-muted"><i class="mdi mdi-phone mr-1"></i><?php echo htmlspecialchars($st['phone']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary-lighten font-11">
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
                                                    <span class="badge <?php echo ($p_cnt > 0) ? 'badge-primary-lighten' : 'badge-danger-lighten'; ?> font-12 px-2 py-1 font-weight-bold">
                                                        <i class="mdi mdi-account mr-1"></i><?php echo $p_cnt; ?> <?php echo get_phrase('staff'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($p_cnt >= 2): ?>
                                                        <span class="badge badge-success-lighten font-11"><i class="mdi mdi-check-circle mr-1"></i><?php echo get_phrase('optimal_staff'); ?></span>
                                                    <?php elseif ($p_cnt == 1): ?>
                                                        <span class="badge badge-info-lighten font-11"><i class="mdi mdi-account mr-1"></i><?php echo get_phrase('single_pharmacist'); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger font-11"><i class="mdi mdi-alert mr-1"></i><?php echo get_phrase('unstaffed_alert'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-right">
                                                    <a href="<?php echo site_url('admin/store_form/edit_store_form/' . $st['id']); ?>" class="btn btn-xs btn-outline-primary" title="<?php echo get_phrase('edit_store'); ?>">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <a href="<?php echo site_url('admin/users?store_id=' . $st['id']); ?>" class="btn btn-xs btn-outline-secondary ml-1" title="<?php echo get_phrase('view_store_staff'); ?>">
                                                        <i class="mdi mdi-account-multiple"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="mdi mdi-store font-22 d-block mb-1"></i>
                                                <?php echo get_phrase('no_stores_found'); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 border-top bg-light d-flex justify-content-between align-items-center">
                            <small class="text-muted"><?php echo get_phrase('showing_10_recent_stores'); ?> (<?php echo $total_s; ?> <?php echo get_phrase('total_stores'); ?>)</small>
                            <a href="<?php echo site_url('admin/stores'); ?>" class="btn btn-sm btn-link font-weight-semibold text-primary">
                                <?php echo get_phrase('view_all_stores'); ?> <i class="mdi mdi-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Tab 3: Licence Verification Queue (Non-Compliant Pharmacists) -->
                    <div class="tab-pane fade" id="tab-licence" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 font-13 qes-data-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th><?php echo get_phrase('pharmacist'); ?></th>
                                        <th><?php echo get_phrase('employee_id'); ?></th>
                                        <th><?php echo get_phrase('assigned_branch'); ?></th>
                                        <th><?php echo get_phrase('contact_details'); ?></th>
                                        <th><?php echo get_phrase('licence_status'); ?></th>
                                        <th class="text-right"><?php echo get_phrase('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($unlicensed_pharmacists)): ?>
                                        <?php foreach ($unlicensed_pharmacists as $u): ?>
                                            <tr class="qes-searchable-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-initials mr-2 text-danger" style="background: rgba(250,92,124,0.1);">
                                                            <?php echo strtoupper(substr($u['first_name'] ?? 'P', 0, 1) . substr($u['last_name'] ?? '', 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <a href="<?php echo site_url('admin/user_form/edit_user_form/' . $u['id']); ?>" class="text-dark font-weight-bold d-block mb-0">
                                                                <?php echo htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')); ?>
                                                            </a>
                                                            <small class="text-muted"><?php echo htmlspecialchars($u['email'] ?? ''); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light text-dark font-11">
                                                        <?php echo !empty($u['employee_id']) ? htmlspecialchars($u['employee_id']) : '-'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($u['store_name'])): ?>
                                                        <span class="badge badge-light-lighten text-dark font-11 px-2 py-1">
                                                            <i class="mdi mdi-store mr-1 text-primary"></i><?php echo htmlspecialchars($u['store_name']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning-lighten font-11"><?php echo get_phrase('unassigned_store'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="text-muted font-12">
                                                        <i class="mdi mdi-phone mr-1"></i><?php echo !empty($u['phone']) ? htmlspecialchars($u['phone']) : '-'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-danger font-11">
                                                        <i class="mdi mdi-alert mr-1"></i><?php echo get_phrase('licence_missing'); ?>
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <a href="<?php echo site_url('admin/user_form/edit_user_form/' . $u['id']); ?>" class="btn btn-xs btn-primary shadow-sm" title="<?php echo get_phrase('update_licence'); ?>">
                                                        <i class="mdi mdi-ocr mr-1"></i> <?php echo get_phrase('upload_licence'); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-success">
                                                <i class="mdi mdi-check-decagram font-28 d-block mb-1"></i>
                                                <strong><?php echo get_phrase('all_pharmacists_are_properly_licensed'); ?>!</strong>
                                                <div class="text-muted font-12"><?php echo get_phrase('no_pending_verifications_in_queue'); ?></div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 border-top bg-light d-flex justify-content-between align-items-center">
                            <small class="text-muted"><?php echo get_phrase('showing_up_to_10_recent_unlicensed_pharmacists'); ?> (<?php echo $unlic_p; ?> <?php echo get_phrase('total_unlicensed'); ?>)</small>
                            <a href="<?php echo site_url('admin/licence_validity'); ?>" class="btn btn-sm btn-link font-weight-semibold text-primary">
                                <?php echo get_phrase('view_licence_validity_report'); ?> <i class="mdi mdi-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Tab 4: Top Training Courses -->
                    <div class="tab-pane fade" id="tab-courses" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 font-13 qes-data-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th><?php echo get_phrase('course_title'); ?></th>
                                        <th><?php echo get_phrase('enrolled_staff'); ?></th>
                                        <th><?php echo get_phrase('status'); ?></th>
                                        <th class="text-right"><?php echo get_phrase('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($top_courses)): ?>
                                        <?php foreach ($top_courses as $c): ?>
                                            <tr class="qes-searchable-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-2" style="width: 44px; height: 32px; border-radius: 6px; background: #eef2f6; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                                            <i class="mdi mdi-book-open-variant text-primary font-18"></i>
                                                        </div>
                                                        <div>
                                                            <a href="<?php echo site_url('admin/course_form/course_edit/' . $c['id']); ?>" class="text-dark font-weight-bold d-block">
                                                                <?php echo htmlspecialchars($c['title']); ?>
                                                            </a>
                                                            <small class="text-muted">ID: #<?php echo $c['id']; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary font-12 px-2 py-1">
                                                        <i class="mdi mdi-account-group mr-1"></i><?php echo (int)$c['enrol_count']; ?> <?php echo get_phrase('enrolled'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($c['status'] == 'active'): ?>
                                                        <span class="badge badge-success-lighten font-11"><i class="mdi mdi-check mr-1"></i><?php echo get_phrase('active'); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning-lighten font-11"><i class="mdi mdi-clock mr-1"></i><?php echo get_phrase('pending'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-right">
                                                    <a href="<?php echo site_url('admin/course_form/course_edit/' . $c['id']); ?>" class="btn btn-xs btn-outline-primary" title="<?php echo get_phrase('edit_course'); ?>">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <a href="<?php echo site_url('home/course/' . rawurlencode(slugify($c['title'])) . '/' . $c['id']); ?>" target="_blank" class="btn btn-xs btn-outline-secondary ml-1" title="<?php echo get_phrase('preview'); ?>">
                                                        <i class="mdi mdi-open-in-new"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="mdi mdi-book-outline font-22 d-block mb-1"></i>
                                                <?php echo get_phrase('no_courses_found'); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 border-top bg-light d-flex justify-content-between align-items-center">
                            <small class="text-muted"><?php echo get_phrase('showing_top_10_courses_by_enrollment'); ?> (<?php echo $total_courses; ?> <?php echo get_phrase('total_courses'); ?>)</small>
                            <a href="<?php echo site_url('admin/courses'); ?>" class="btn btn-sm btn-link font-weight-semibold text-primary">
                                <?php echo get_phrase('view_all_courses'); ?> <i class="mdi mdi-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- 7. Regulatory Standards & Compliance Disclaimer Card -->
<div class="row">
    <div class="col-12">
        <div class="card bg-light border-0 shadow-none text-muted font-12" style="border-radius: 10px;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                <div>
                    <i class="mdi mdi-shield-lock-outline mr-1 text-primary"></i>
                    <strong><?php echo get_phrase('regulatory_compliance_standard'); ?>:</strong>
                    <span>Records conform with state pharmacy council guidelines and mandatory continuing education audits.</span>
                </div>
                <div>
                    <span><?php echo get_phrase('last_synced'); ?>: <?php echo date('d M Y, h:i A'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Client-side Script for Dashboard Tabs & Real-time Filter -->
<script type="text/javascript">
function activateDashboardTab(tabId) {
    if (tabId === 'tab-licence') {
        $('#tab-licence-btn').tab('show');
    } else if (tabId === 'tab-stores') {
        $('#tab-stores-btn').tab('show');
    } else if (tabId === 'tab-courses') {
        $('#tab-courses-btn').tab('show');
    } else {
        $('#tab-enrollments-btn').tab('show');
    }
    $('html, body').animate({
        scrollTop: $("#dashboardHubTabs").offset().top - 80
    }, 400);
}

$(document).ready(function() {
    // Real-time In-Dashboard Search Filter
    $('#dashboard-global-search').on('keyup', function() {
        var term = $(this).val().toLowerCase().trim();
        $('.tab-pane.active .qes-searchable-row').each(function() {
            var rowText = $(this).text().toLowerCase();
            if (term === '' || rowText.indexOf(term) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Reset filter when switching tabs
    $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        var term = $('#dashboard-global-search').val().toLowerCase().trim();
        if (term !== '') {
            $('#dashboard-global-search').trigger('keyup');
        } else {
            $('.qes-searchable-row').show();
        }
    });
});
</script>
