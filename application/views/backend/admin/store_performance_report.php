<style>
  .table-responsive {
    overflow-x: auto !important;
    width: 100%;
  }
  #store_report_table th, 
  #store_report_table td {
    white-space: nowrap;
    vertical-align: middle;
  }
</style>

<!-- Title Bar -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title">
                    <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('store_performance_report'); ?>
                    <div class="float-right">
                        <button type="button" onclick="exportStoreCsv()" class="btn btn-outline-success btn-rounded alignToTitle mr-1">
                            <i class="mdi mdi-file-excel mr-1"></i> <?php echo get_phrase('export_csv'); ?>
                        </button>
                        <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-rounded alignToTitle mr-1">
                            <i class="mdi mdi-printer mr-1"></i> <?php echo get_phrase('print_report'); ?>
                        </button>
                        <a href="<?php echo site_url('admin/stores'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
                            <i class="mdi mdi-store mr-1"></i> <?php echo get_phrase('manage_stores'); ?>
                        </a>
                    </div>
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Overview KPI Cards (Widget-Inline style matching Course List) -->
<div class="row">
    <div class="col-12">
        <div class="card widget-inline">
            <div class="card-body p-0">
                <div class="row no-gutters">
                    <!-- Total Active Stores -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card shadow-none m-0">
                            <div class="card-body text-center">
                                <i class="dripicons-store text-primary" style="font-size: 24px;"></i>
                                <h3><span><?php echo number_format($overview['total_stores']); ?></span></h3>
                                <p class="text-muted font-15 mb-0"><?php echo get_phrase('total_stores'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Pharmacists -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card shadow-none m-0 border-left">
                            <div class="card-body text-center">
                                <i class="dripicons-user-group text-info" style="font-size: 24px;"></i>
                                <h3><span class="text-info"><?php echo number_format($overview['assigned_pharmacists']); ?></span></h3>
                                <p class="text-muted font-15 mb-0"><?php echo get_phrase('assigned_pharmacists'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Overall Licence Compliance -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card shadow-none m-0 border-left">
                            <div class="card-body text-center">
                                <i class="dripicons-shield text-success" style="font-size: 24px;"></i>
                                <h3><span class="text-success"><?php echo $overview['licence_compliance_rate']; ?>%</span></h3>
                                <p class="text-muted font-15 mb-0"><?php echo get_phrase('licence_compliance'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Evaluation Pass Rate -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card shadow-none m-0 border-left">
                            <div class="card-body text-center">
                                <i class="dripicons-graduation text-warning" style="font-size: 24px;"></i>
                                <h3><span class="text-warning"><?php echo $overview['evaluation_pass_rate']; ?>%</span></h3>
                                <p class="text-muted font-15 mb-0"><?php echo get_phrase('evaluation_pass_rate'); ?></p>
                            </div>
                        </div>
                    </div>
                </div> <!-- end row -->
            </div>
        </div> <!-- end card-box-->
    </div> <!-- end col-->
</div>

<!-- Main Table Card -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3">
                    <i class="mdi mdi-format-list-bulleted text-primary mr-1"></i> <?php echo get_phrase('store_performance_and_compliance_breakdown'); ?>
                </h4>

                <div class="table-responsive">
                    <table class="table table-striped table-centered w-100" id="store_report_table">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th><?php echo get_phrase('store_details'); ?></th>
                                <th><?php echo get_phrase('category'); ?></th>
                                <th><?php echo get_phrase('location'); ?></th>
                                <th><?php echo get_phrase('pharmacists'); ?></th>
                                <th><?php echo get_phrase('licence_compliance'); ?></th>
                                <th><?php echo get_phrase('licence_status'); ?></th>
                                <th><?php echo get_phrase('quiz_evaluations'); ?></th>
                                <th><?php echo get_phrase('pass_rate'); ?></th>
                                <th class="text-center"><?php echo get_phrase('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($store_rows as $idx => $st): ?>
                                <tr>
                                    <td><?php echo $idx + 1; ?></td>
                                    <td>
                                        <div class="font-weight-bold text-dark font-14">
                                            <i class="mdi mdi-store mr-1 text-primary"></i> <?php echo html_escape($st['store_name']); ?>
                                        </div>
                                        <?php if (!empty($st['store_code']) && $st['store_code'] !== 'N/A'): ?>
                                            <span class="badge badge-light border text-muted font-11"><?php echo html_escape($st['store_code']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($st['store_category']) && $st['store_category'] !== '-'): ?>
                                            <span class="badge badge-info-lighten font-11 px-2 py-1"><?php echo html_escape($st['store_category']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted font-italic font-11">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $loc_parts = array_filter([$st['city'] ?? '', $st['state'] ?? '']);
                                            $loc_str = implode(', ', $loc_parts);
                                        ?>
                                        <?php if (!empty($loc_str)): ?>
                                            <span class="font-12"><?php echo html_escape($loc_str); ?></span>
                                            <?php if (!empty($st['zone'])): ?>
                                                <br><span class="badge badge-secondary-lighten font-10"><?php echo html_escape($st['zone']); ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted font-italic font-12">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary-lighten font-13 font-weight-bold px-2 py-1">
                                            <?php echo $st['pharmacist_count']; ?>
                                        </span>
                                    </td>
                                    <td style="min-width: 170px;">
                                        <?php if ($st['pharmacist_count'] > 0): ?>
                                            <div class="d-flex align-items-center">
                                                <span class="font-weight-bold font-12 mr-2"><?php echo $st['valid_licence_count']; ?>/<?php echo $st['pharmacist_count']; ?></span>
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-success" style="width: <?php echo $st['licence_compliance_pct']; ?>%;"></div>
                                                </div>
                                                <span class="ml-2 font-11 font-weight-semibold text-muted"><?php echo $st['licence_compliance_pct']; ?>%</span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted font-italic font-12"><?php echo get_phrase('no_staff'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($st['expired_licence_count'] > 0): ?>
                                            <span class="badge badge-danger font-11 mr-1" title="<?php echo get_phrase('expired'); ?>"><i class="mdi mdi-close-circle mr-1"></i><?php echo $st['expired_licence_count']; ?> <?php echo get_phrase('expired'); ?></span>
                                        <?php endif; ?>
                                        <?php if ($st['expiring_soon_count'] > 0): ?>
                                            <span class="badge badge-warning font-11 text-dark mr-1" title="<?php echo get_phrase('expiring_soon'); ?>"><i class="mdi mdi-alert mr-1"></i><?php echo $st['expiring_soon_count']; ?> <?php echo get_phrase('expiring'); ?></span>
                                        <?php endif; ?>
                                        <?php if ($st['expired_licence_count'] == 0 && $st['expiring_soon_count'] == 0 && $st['pharmacist_count'] > 0): ?>
                                            <span class="badge badge-success-lighten font-11"><i class="mdi mdi-check-circle mr-1 text-success"></i><?php echo get_phrase('all_valid'); ?></span>
                                        <?php elseif ($st['pharmacist_count'] == 0): ?>
                                            <span class="text-muted font-italic font-11">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold font-13"><?php echo $st['quiz_attempts']; ?></span>
                                        <small class="text-muted"><?php echo get_phrase('attempts'); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($st['quiz_attempts'] > 0): ?>
                                            <span class="font-weight-bold mr-1 text-<?php echo ($st['quiz_pass_rate'] >= 75) ? 'success' : (($st['quiz_pass_rate'] >= 50) ? 'warning' : 'danger'); ?>">
                                                <?php echo $st['quiz_pass_rate']; ?>%
                                            </span>
                                            <small class="text-muted">(<?php echo $st['quiz_passed']; ?> <?php echo get_phrase('passed'); ?>)</small>
                                        <?php else: ?>
                                            <span class="text-muted font-italic font-11"><?php echo get_phrase('no_attempts'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropright dropright">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="<?php echo site_url('admin/licence_report?store_id=' . $st['id']); ?>"><i class="mdi mdi-certificate mr-1 text-primary"></i> <?php echo get_phrase('view_licences'); ?></a></li>
                                                <li><a class="dropdown-item" href="<?php echo site_url('admin/pharmacist_evaluation_report?store_id=' . $st['id']); ?>"><i class="mdi mdi-clipboard-check mr-1 text-success"></i> <?php echo get_phrase('view_evaluations'); ?></a></li>
                                                <li><a class="dropdown-item" href="<?php echo site_url('admin/users?store_id=' . $st['id']); ?>"><i class="mdi mdi-account-group mr-1 text-info"></i> <?php echo get_phrase('view_pharmacists'); ?></a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#store_report_table').DataTable({
            pageLength: 25,
            order: [[4, 'desc']], // Order by total pharmacists desc
            language: {
                search: "<?php echo get_phrase('search'); ?>:",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });
    });

    function exportStoreCsv() {
        window.location.href = "<?php echo site_url('admin/export_store_performance_csv'); ?>";
    }
</script>
