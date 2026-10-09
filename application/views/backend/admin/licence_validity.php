<style>
  .buttons-csv {
    margin-bottom: 20px;
  }
  .table-responsive {
    overflow-x: auto !important;
    width: 100%;
  }
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12 {
    overflow-x: auto !important;
    width: 100%;
    padding-bottom: 10px;
  }
  #licence_validity_table {
    min-width: 1250px;
    width: 100% !important;
  }
  #licence_validity_table th, 
  #licence_validity_table td {
    white-space: nowrap;
    vertical-align: middle;
  }
  .table-responsive::-webkit-scrollbar,
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12::-webkit-scrollbar {
    height: 8px;
  }
  .table-responsive::-webkit-scrollbar-track,
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12::-webkit-scrollbar-track {
    background: #f1f3fa;
    border-radius: 4px;
  }
  .table-responsive::-webkit-scrollbar-thumb,
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12::-webkit-scrollbar-thumb {
    background: #727cf5;
    border-radius: 4px;
  }
  .table-responsive::-webkit-scrollbar-thumb:hover,
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12::-webkit-scrollbar-thumb:hover {
    background: #5b66d4;
  }

  .stat-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-radius: 8px;
    cursor: pointer;
  }
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  }
  .stat-card.active-filter {
    border: 2px solid #727cf5 !important;
  }

  .nav-pills-validity .nav-link {
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 16px;
    color: #6c757d;
    background: #f1f3fa;
    margin-right: 8px;
    margin-bottom: 8px;
    transition: all 0.2s;
  }
  .nav-pills-validity .nav-link.active {
    background-color: #727cf5;
    color: #fff;
    box-shadow: 0 2px 6px rgba(114, 124, 245, 0.4);
  }
</style>

<!-- Title Bar -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title">
                    <i class="mdi mdi-certificate title_icon"></i> <?php echo get_phrase('pharmacist_licence_validity'); ?>
                    <a href="<?php echo site_url('admin/user_form/add_user_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
                        <i class="mdi mdi-plus"></i> <?php echo get_phrase('add_pharmacist'); ?>
                    </a>
                    <a href="<?php echo site_url('admin/users'); ?>" class="btn btn-outline-secondary btn-rounded alignToTitle mr-1">
                        <i class="mdi mdi-account-group"></i> <?php echo get_phrase('manage_pharmacists'); ?>
                    </a>
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Overview Stats Cards -->
<div class="row">
    <!-- Total Pharmacists -->
    <div class="col-md-6 col-xl">
        <div class="card stat-card active-filter" id="card_all" onclick="setValidityFilter('all')">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h6 class="text-muted text-uppercase font-11 mt-0 mb-1"><?php echo get_phrase('total_pharmacists'); ?></h6>
                        <h3 class="my-1 font-20 text-dark"><?php echo number_format($stats['total'] ?? 0); ?></h3>
                    </div>
                    <div class="col-6 text-right">
                        <div class="avatar-sm ml-auto">
                            <span class="avatar-title bg-primary-lighten text-primary rounded-circle font-20">
                                <i class="mdi mdi-account-multiple"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Valid / Active Licences -->
    <div class="col-md-6 col-xl">
        <div class="card stat-card" id="card_valid" onclick="setValidityFilter('valid')">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h6 class="text-muted text-uppercase font-11 mt-0 mb-1"><?php echo get_phrase('valid_licences'); ?></h6>
                        <h3 class="my-1 font-20 text-success"><?php echo number_format($stats['valid_count'] ?? 0); ?></h3>
                        <small class="text-success font-11">> 90 <?php echo get_phrase('days_left'); ?></small>
                    </div>
                    <div class="col-6 text-right">
                        <div class="avatar-sm ml-auto">
                            <span class="avatar-title bg-success-lighten text-success rounded-circle font-20">
                                <i class="mdi mdi-check-decagram"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiring Soon -->
    <div class="col-md-6 col-xl">
        <div class="card stat-card" id="card_expiring_soon" onclick="setValidityFilter('expiring_soon')">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h6 class="text-muted text-uppercase font-11 mt-0 mb-1"><?php echo get_phrase('expiring_soon'); ?></h6>
                        <h3 class="my-1 font-20 text-warning"><?php echo number_format($stats['expiring_soon_count'] ?? 0); ?></h3>
                        <small class="text-warning font-11">≤ 90 <?php echo get_phrase('days_left'); ?></small>
                    </div>
                    <div class="col-6 text-right">
                        <div class="avatar-sm ml-auto">
                            <span class="avatar-title bg-warning-lighten text-warning rounded-circle font-20">
                                <i class="mdi mdi-clock-alert-outline"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expired Licences -->
    <div class="col-md-6 col-xl">
        <div class="card stat-card" id="card_expired" onclick="setValidityFilter('expired')">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h6 class="text-muted text-uppercase font-11 mt-0 mb-1"><?php echo get_phrase('expired'); ?></h6>
                        <h3 class="my-1 font-20 text-danger"><?php echo number_format($stats['expired_count'] ?? 0); ?></h3>
                        <small class="text-danger font-11"><?php echo get_phrase('requires_renewal'); ?></small>
                    </div>
                    <div class="col-6 text-right">
                        <div class="avatar-sm ml-auto">
                            <span class="avatar-title bg-danger-lighten text-danger rounded-circle font-20">
                                <i class="mdi mdi-alert-octagon"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Missing End Date / Pending -->
    <div class="col-md-6 col-xl">
        <div class="card stat-card" id="card_pending" onclick="setValidityFilter('pending')">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h6 class="text-muted text-uppercase font-11 mt-0 mb-1"><?php echo get_phrase('missing_date'); ?></h6>
                        <h3 class="my-1 font-20 text-secondary"><?php echo number_format($stats['pending_count'] ?? 0); ?></h3>
                        <small class="text-muted font-11"><?php echo get_phrase('date_not_set'); ?></small>
                    </div>
                    <div class="col-6 text-right">
                        <div class="avatar-sm ml-auto">
                            <span class="avatar-title bg-secondary-lighten text-secondary rounded-circle font-20">
                                <i class="mdi mdi-help-circle-outline"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="header-title mb-1"><?php echo get_phrase('pharmacist_licence_validity_records'); ?></h4>
                        <p class="text-muted font-13 mb-0"><?php echo get_phrase('monitor_licence_start_dates_expiry_dates_and_remaining_validity_days'); ?></p>
                    </div>

                    <!-- Filter Navigation Pills -->
                    <ul class="nav nav-pills nav-pills-validity mt-2 mt-sm-0" id="validityFilterPills" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="javascript:void(0)" onclick="setValidityFilter('all')">
                                <?php echo get_phrase('all'); ?> <span class="badge badge-light ml-1"><?php echo $stats['total'] ?? 0; ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0)" onclick="setValidityFilter('valid')">
                                <i class="mdi mdi-check-circle text-success mr-1"></i><?php echo get_phrase('valid'); ?> <span class="badge badge-success-lighten ml-1"><?php echo $stats['valid_count'] ?? 0; ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0)" onclick="setValidityFilter('expiring_soon')">
                                <i class="mdi mdi-alert-circle text-warning mr-1"></i><?php echo get_phrase('expiring_soon'); ?> <span class="badge badge-warning-lighten ml-1"><?php echo $stats['expiring_soon_count'] ?? 0; ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0)" onclick="setValidityFilter('expired')">
                                <i class="mdi mdi-close-circle text-danger mr-1"></i><?php echo get_phrase('expired'); ?> <span class="badge badge-danger-lighten ml-1"><?php echo $stats['expired_count'] ?? 0; ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0)" onclick="setValidityFilter('pending')">
                                <i class="mdi mdi-help-circle-outline text-secondary mr-1"></i><?php echo get_phrase('pending'); ?> <span class="badge badge-secondary-lighten ml-1"><?php echo $stats['pending_count'] ?? 0; ?></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-centered w-100" id="licence_validity_table">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th><?php echo get_phrase('pharmacist_name'); ?></th>
                                <th><?php echo get_phrase('pharmacist_info'); ?></th>
                                <th><?php echo get_phrase('licence_no'); ?></th>
                                <th><?php echo get_phrase('start_date'); ?></th>
                                <th><?php echo get_phrase('end_date'); ?></th>
                                <th><?php echo get_phrase('validity_days'); ?></th>
                                <th><?php echo get_phrase('status'); ?></th>
                                <th><?php echo get_phrase('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var currentValidityFilter = 'all';
    var validityTable = null;

    $(document).ready(function() {
        validityTable = $('#licence_validity_table').DataTable({
            responsive: false,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            ajax: {
                url: "<?php echo site_url('admin/server_side_licence_validity_data'); ?>",
                type: "POST",
                dataType: "json",
                data: function(d) {
                    d.filter_status = currentValidityFilter;
                    d.<?php echo $this->security->get_csrf_token_name(); ?> = '<?php echo $this->security->get_csrf_hash(); ?>';
                }
            },
            columns: [
                { data: "key" },
                { data: "pharmacist_name" },
                { data: "pharmacist_info" },
                { data: "licence_no" },
                { data: "licence_start_date" },
                { data: "licence_end_date" },
                { data: "validity_days" },
                { data: "status" },
                { data: "action" }
            ],
            dom: 'Blfrtip',
            lengthMenu: [[10, 25, 50, 100, 250, 500], [10, 25, 50, 100, 250, 500]],
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="mdi mdi-download mr-1"></i> ' + "<?php echo get_phrase('export_as_csv'); ?>",
                    className: 'btn btn-sm btn-outline-secondary text-white',
                    filename: function() {
                        var d = new Date().toISOString().slice(0, 19).replace(/[-T:]/g, '_');
                        return 'pharmacist_licence_validity_' + currentValidityFilter + '_' + d;
                    },
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ],
            drawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });

    function setValidityFilter(status) {
        currentValidityFilter = status;

        // Update pills
        $('#validityFilterPills .nav-link').removeClass('active');
        $('#validityFilterPills a[onclick*="\'' + status + '\'"]').addClass('active');

        // Update stat cards
        $('.stat-card').removeClass('active-filter');
        $('#card_' + status).addClass('active-filter');

        // Reload data table
        if (validityTable) {
            validityTable.ajax.reload();
        }
    }
</script>
