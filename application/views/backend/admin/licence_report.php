<style>
  .table-responsive {
    overflow-x: auto !important;
    width: 100%;
  }
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12 {
    overflow-x: auto !important;
    width: 100%;
    padding-bottom: 10px;
  }
  #licence_report_table {
    min-width: 1250px;
    width: 100% !important;
  }
  #licence_report_table th, 
  #licence_report_table td {
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

  .kpi-card {
    cursor: pointer;
    border-left: 3px solid transparent;
    transition: all 0.15s ease-in-out;
  }
  .kpi-card:hover {
    background-color: #fafbfd;
  }
  .kpi-card.active-filter {
    border-left-color: #727cf5;
    background-color: #f6f8fd;
  }
  .kpi-card.active-filter#card_valid {
    border-left-color: #0acf97;
  }
  .kpi-card.active-filter#card_expiring_soon {
    border-left-color: #ffbc00;
  }
  .kpi-card.active-filter#card_expired {
    border-left-color: #fa5c7c;
  }
  .kpi-card.active-filter#card_pending {
    border-left-color: #6c757d;
  }
</style>

<!-- Title Bar -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title">
                    <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('licence_report'); ?>
                    <div class="float-right">
                        <button type="button" onclick="exportLicenceCsv()" class="btn btn-outline-success btn-rounded alignToTitle mr-1">
                            <i class="mdi mdi-file-excel mr-1"></i> <?php echo get_phrase('export_csv'); ?>
                        </button>
                        <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-rounded alignToTitle mr-1">
                            <i class="mdi mdi-printer mr-1"></i> <?php echo get_phrase('print_report'); ?>
                        </button>
                        <a href="<?php echo site_url('admin/users'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
                            <i class="mdi mdi-account-group mr-1"></i> <?php echo get_phrase('manage_pharmacists'); ?>
                        </a>
                    </div>
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Overview KPI Filter Cards (Widget-Inline style matching Course List) -->
<div class="row">
    <div class="col-12">
        <div class="card widget-inline">
            <div class="card-body p-0">
                <div class="row no-gutters">
                    <!-- Total Pharmacists -->
                    <div class="col-sm-6 col-xl">
                        <a href="javascript:void(0);" onclick="setValidityFilter('all')" class="text-secondary kpi-card active-filter" id="card_all">
                            <div class="card shadow-none m-0">
                                <div class="card-body text-center">
                                    <i class="dripicons-user-group text-primary" style="font-size: 24px;"></i>
                                    <h3><span id="stat_total"><?php echo number_format($stats['total']); ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo get_phrase('total_pharmacists'); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Valid Licences -->
                    <div class="col-sm-6 col-xl">
                        <a href="javascript:void(0);" onclick="setValidityFilter('valid')" class="text-secondary kpi-card" id="card_valid">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-checkmark text-success" style="font-size: 24px;"></i>
                                    <h3><span id="stat_valid" class="text-success"><?php echo number_format($stats['valid_count']); ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo get_phrase('valid_licences'); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Expiring Soon -->
                    <div class="col-sm-6 col-xl">
                        <a href="javascript:void(0);" onclick="setValidityFilter('expiring_soon')" class="text-secondary kpi-card" id="card_expiring_soon">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-warning text-warning" style="font-size: 24px;"></i>
                                    <h3><span id="stat_expiring_soon" class="text-warning"><?php echo number_format($stats['expiring_soon_count']); ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo get_phrase('expiring_soon'); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Expired -->
                    <div class="col-sm-6 col-xl">
                        <a href="javascript:void(0);" onclick="setValidityFilter('expired')" class="text-secondary kpi-card" id="card_expired">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-cross text-danger" style="font-size: 24px;"></i>
                                    <h3><span id="stat_expired" class="text-danger"><?php echo number_format($stats['expired_count']); ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo get_phrase('expired'); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Missing Date -->
                    <div class="col-sm-6 col-xl">
                        <a href="javascript:void(0);" onclick="setValidityFilter('pending')" class="text-secondary kpi-card" id="card_pending">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="mdi mdi-calendar-question text-secondary" style="font-size: 24px;"></i>
                                    <h3><span id="stat_pending"><?php echo number_format($stats['pending_count']); ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo get_phrase('missing_date'); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Toolbar Card -->
<div class="row">
    <div class="col-xl-12">
        <div class="card mb-3">
            <div class="card-body p-3">
                <form id="licence_filter_form" class="form-inline d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex flex-wrap align-items-center">
                        <!-- Store Filter -->
                        <div class="form-group mr-3 my-1">
                            <label for="filter_store_id" class="mr-2 font-weight-bold text-dark font-13">
                                <i class="mdi mdi-store text-primary mr-1"></i> <?php echo get_phrase('filter_by_store'); ?>:
                            </label>
                            <select class="form-control form-control-sm select2" id="filter_store_id" style="min-width: 220px;" onchange="applyFilters()">
                                <option value="all" <?php if (($selected_store_id ?? 'all') == 'all') echo 'selected'; ?>><?php echo get_phrase('all_stores'); ?></option>
                                <option value="no_store" <?php if (($selected_store_id ?? '') == 'no_store') echo 'selected'; ?>><?php echo get_phrase('no_store_assigned'); ?></option>
                                <?php if (!empty($stores)): ?>
                                    <?php foreach ($stores as $st): ?>
                                        <option value="<?php echo $st['id']; ?>" <?php if (($selected_store_id ?? '') == $st['id']) echo 'selected'; ?>>
                                            <?php echo html_escape($st['store_name']); ?> <?php echo !empty($st['store_code']) ? '(' . html_escape($st['store_code']) . ')' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="form-group mr-3 my-1">
                            <label for="filter_status_select" class="mr-2 font-weight-bold text-dark font-13">
                                <i class="mdi mdi-filter-variant text-info mr-1"></i> <?php echo get_phrase('filter_by_status'); ?>:
                            </label>
                            <select class="form-control form-control-sm select2" id="filter_status_select" style="min-width: 180px;" onchange="syncStatusFilter(this.value)">
                                <option value="all"><?php echo get_phrase('all_statuses'); ?></option>
                                <option value="valid"><?php echo get_phrase('active_valid'); ?> (> 90 days)</option>
                                <option value="expiring_soon"><?php echo get_phrase('expiring_soon'); ?> (&le; 90 days)</option>
                                <option value="expired"><?php echo get_phrase('expired'); ?></option>
                                <option value="pending"><?php echo get_phrase('missing_date'); ?></option>
                            </select>
                        </div>

                        <!-- Date Range: From -->
                        <div class="form-group mr-2 my-1">
                            <label for="filter_date_from" class="mr-2 font-weight-bold text-dark font-13">
                                <i class="mdi mdi-calendar mr-1"></i> <?php echo get_phrase('from'); ?>:
                            </label>
                            <input type="date" class="form-control form-control-sm" id="filter_date_from" onchange="applyFilters()">
                        </div>

                        <!-- Date Range: To -->
                        <div class="form-group mr-3 my-1">
                            <label for="filter_date_to" class="mr-2 font-weight-bold text-dark font-13">
                                <?php echo get_phrase('to'); ?>:
                            </label>
                            <input type="date" class="form-control form-control-sm" id="filter_date_to" onchange="applyFilters()">
                        </div>
                    </div>

                    <!-- Reset Button -->
                    <div class="my-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                            <i class="mdi mdi-refresh mr-1"></i> <?php echo get_phrase('reset_filters'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <h4 class="header-title mb-1">
                        <i class="mdi mdi-format-list-bulleted text-primary mr-1"></i> <?php echo get_phrase('licence_records'); ?>
                    </h4>
                    <p class="text-muted font-13 mb-0"><?php echo get_phrase('monitor_licence_start_dates_expiry_dates_and_remaining_validity_days'); ?></p>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-centered w-100" id="licence_report_table">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th><?php echo get_phrase('pharmacist_info'); ?></th>
                                <th><?php echo get_phrase('assigned_store'); ?></th>
                                <th><?php echo get_phrase('licence_no'); ?></th>
                                <th><?php echo get_phrase('licence_start_date'); ?></th>
                                <th><?php echo get_phrase('licence_end_date'); ?></th>
                                <th><?php echo get_phrase('validity_days'); ?></th>
                                <th><?php echo get_phrase('status'); ?></th>
                                <th><?php echo get_phrase('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var currentValidityFilter = 'all';
    var licenceReportTable;

    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2();
        }

        licenceReportTable = $('#licence_report_table').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            autoWidth: false,
            pageLength: 10,
            ajax: {
                url: "<?php echo site_url('admin/server_side_licence_report_data'); ?>",
                type: "POST",
                data: function(d) {
                    d.filter_status = currentValidityFilter;
                    d.filter_store_id = $('#filter_store_id').val();
                    d.filter_date_from = $('#filter_date_from').val();
                    d.filter_date_to = $('#filter_date_to').val();
                },
                dataSrc: function(json) {
                    if (json.stats) {
                        $('#stat_total').text(Number(json.stats.total).toLocaleString());
                        $('#stat_valid').text(Number(json.stats.valid_count).toLocaleString());
                        $('#stat_expiring_soon').text(Number(json.stats.expiring_soon_count).toLocaleString());
                        $('#stat_expired').text(Number(json.stats.expired_count).toLocaleString());
                        $('#stat_pending').text(Number(json.stats.pending_count).toLocaleString());
                    }
                    return json.data;
                }
            },
            columns: [
                { data: 0, orderable: false, width: '4%' },
                { data: 1, orderable: true, width: '22%' },
                { data: 2, orderable: true, width: '18%' },
                { data: 3, orderable: true, width: '12%' },
                { data: 4, orderable: true, width: '10%' },
                { data: 5, orderable: true, width: '10%' },
                { data: 6, orderable: false, width: '12%' },
                { data: 7, orderable: false, width: '8%' },
                { data: 8, orderable: false, width: '4%' }
            ],
            order: [[1, 'asc']],
            language: {
                search: "<?php echo get_phrase('search'); ?>:",
                lengthMenu: "<?php echo get_phrase('display'); ?> _MENU_ <?php echo get_phrase('records'); ?>",
                info: "<?php echo get_phrase('showing'); ?> _START_ <?php echo get_phrase('to'); ?> _END_ <?php echo get_phrase('of'); ?> _TOTAL_ <?php echo get_phrase('pharmacists'); ?>",
                infoEmpty: "<?php echo get_phrase('showing'); ?> 0 <?php echo get_phrase('to'); ?> 0 <?php echo get_phrase('of'); ?> 0 <?php echo get_phrase('pharmacists'); ?>",
                emptyTable: "<?php echo get_phrase('no_matching_records_found'); ?>",
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

    function setValidityFilter(status) {
        currentValidityFilter = status;
        $('#filter_status_select').val(status).trigger('change.select2');

        $('.kpi-card').removeClass('active-filter');
        $('#card_' + status).addClass('active-filter');

        if (licenceReportTable) {
            licenceReportTable.ajax.reload();
        }
    }

    function syncStatusFilter(status) {
        currentValidityFilter = status;
        $('.kpi-card').removeClass('active-filter');
        $('#card_' + status).addClass('active-filter');

        if (licenceReportTable) {
            licenceReportTable.ajax.reload();
        }
    }

    function applyFilters() {
        if (licenceReportTable) {
            licenceReportTable.ajax.reload();
        }
    }

    function resetFilters() {
        $('#filter_store_id').val('all').trigger('change.select2');
        $('#filter_status_select').val('all').trigger('change.select2');
        $('#filter_date_from').val('');
        $('#filter_date_to').val('');
        setValidityFilter('all');
    }

    function exportLicenceCsv() {
        var storeId = $('#filter_store_id').val() || 'all';
        var status = currentValidityFilter || 'all';
        var dateFrom = $('#filter_date_from').val() || '';
        var dateTo = $('#filter_date_to').val() || '';
        var search = $('#licence_report_table_filter input').val() || '';

        var url = "<?php echo site_url('admin/export_licence_report_csv'); ?>?store_id=" + encodeURIComponent(storeId) +
                  "&status=" + encodeURIComponent(status) +
                  "&date_from=" + encodeURIComponent(dateFrom) +
                  "&date_to=" + encodeURIComponent(dateTo) +
                  "&search=" + encodeURIComponent(search);

        window.location.href = url;
    }
</script>
