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
  #evaluation_report_table {
    min-width: 1250px;
    width: 100% !important;
  }
  #evaluation_report_table th, 
  #evaluation_report_table td {
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
  .kpi-card.active-filter#card_res_passed {
    border-left-color: #0acf97;
  }
  .kpi-card.active-filter#card_res_failed {
    border-left-color: #fa5c7c;
  }
</style>

<!-- Title Bar -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title">
                    <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('pharmacist_evaluation_report'); ?>
                    <div class="float-right">
                        <button type="button" onclick="exportEvaluationCsv()" class="btn btn-outline-success btn-rounded alignToTitle mr-1">
                            <i class="mdi mdi-file-excel mr-1"></i> <?php echo get_phrase('export_csv'); ?>
                        </button>
                        <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-rounded alignToTitle mr-1">
                            <i class="mdi mdi-printer mr-1"></i> <?php echo get_phrase('print_report'); ?>
                        </button>
                        <a href="<?php echo site_url('admin/courses'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
                            <i class="mdi mdi-book-open-variant mr-1"></i> <?php echo get_phrase('courses'); ?>
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
                    <!-- Total Evaluations -->
                    <div class="col-sm-6 col-xl-3">
                        <a href="javascript:void(0);" onclick="setResultFilter('all')" class="text-secondary kpi-card active-filter" id="card_res_all">
                            <div class="card shadow-none m-0">
                                <div class="card-body text-center">
                                    <i class="dripicons-document text-primary" style="font-size: 24px;"></i>
                                    <h3><span id="stat_eval_total"><?php echo number_format($stats['total']); ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo get_phrase('total_evaluations'); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Passed Evaluations -->
                    <div class="col-sm-6 col-xl-3">
                        <a href="javascript:void(0);" onclick="setResultFilter('passed')" class="text-secondary kpi-card" id="card_res_passed">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-checkmark text-success" style="font-size: 24px;"></i>
                                    <h3><span id="stat_eval_passed" class="text-success"><?php echo number_format($stats['passed_count']); ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo get_phrase('passed_evaluations'); ?> (<span id="stat_eval_pass_rate"><?php echo $stats['pass_rate']; ?>%</span>)</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Failed Evaluations -->
                    <div class="col-sm-6 col-xl-3">
                        <a href="javascript:void(0);" onclick="setResultFilter('failed')" class="text-secondary kpi-card" id="card_res_failed">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-cross text-danger" style="font-size: 24px;"></i>
                                    <h3><span id="stat_eval_failed" class="text-danger"><?php echo number_format($stats['failed_count']); ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo get_phrase('failed_evaluations'); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Average Score -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card shadow-none m-0 border-left">
                            <div class="card-body text-center">
                                <i class="dripicons-graph-line text-info" style="font-size: 24px;"></i>
                                <h3><span id="stat_eval_avg_score" class="text-info"><?php echo $stats['avg_score']; ?>%</span></h3>
                                <p class="text-muted font-15 mb-0"><?php echo get_phrase('average_score'); ?></p>
                            </div>
                        </div>
                    </div>
                </div> <!-- end row -->
            </div>
        </div> <!-- end card-box-->
    </div> <!-- end col-->
</div>

<!-- Filters Toolbar Card -->
<div class="row">
    <div class="col-xl-12">
        <div class="card mb-3">
            <div class="card-body p-3">
                <form id="evaluation_filter_form" class="form-inline d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex flex-wrap align-items-center">
                        <!-- Store Filter -->
                        <div class="form-group mr-3 my-1">
                            <label for="filter_eval_store_id" class="mr-2 font-weight-bold text-dark font-13">
                                <i class="mdi mdi-store text-primary mr-1"></i> <?php echo get_phrase('filter_by_store'); ?>:
                            </label>
                            <select class="form-control form-control-sm select2" id="filter_eval_store_id" style="min-width: 200px;" onchange="applyEvalFilters()">
                                <option value="all"><?php echo get_phrase('all_stores'); ?></option>
                                <option value="no_store"><?php echo get_phrase('no_store_assigned'); ?></option>
                                <?php if (!empty($stores)): ?>
                                    <?php foreach ($stores as $st): ?>
                                        <option value="<?php echo $st['id']; ?>">
                                            <?php echo html_escape($st['store_name']); ?> <?php echo !empty($st['store_code']) ? '(' . html_escape($st['store_code']) . ')' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Course Filter -->
                        <div class="form-group mr-3 my-1">
                            <label for="filter_eval_course_id" class="mr-2 font-weight-bold text-dark font-13">
                                <i class="mdi mdi-book-open-page-variant text-info mr-1"></i> <?php echo get_phrase('filter_by_course'); ?>:
                            </label>
                            <select class="form-control form-control-sm select2" id="filter_eval_course_id" style="min-width: 220px;" onchange="loadCourseQuizzes(this.value); applyEvalFilters();">
                                <option value="all"><?php echo get_phrase('all_courses'); ?></option>
                                <?php if (!empty($courses)): ?>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?php echo $c['id']; ?>">
                                            <?php echo html_escape($c['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Quiz Filter -->
                        <div class="form-group mr-3 my-1">
                            <label for="filter_eval_quiz_id" class="mr-2 font-weight-bold text-dark font-13">
                                <i class="mdi mdi-help-circle-outline text-secondary mr-1"></i> <?php echo get_phrase('filter_by_quiz'); ?>:
                            </label>
                            <select class="form-control form-control-sm select2" id="filter_eval_quiz_id" style="min-width: 200px;" onchange="applyEvalFilters()">
                                <option value="all"><?php echo get_phrase('all_quizzes'); ?></option>
                                <?php if (!empty($quizzes)): ?>
                                    <?php foreach ($quizzes as $q): ?>
                                        <option value="<?php echo $q['id']; ?>" data-course="<?php echo $q['course_id']; ?>">
                                            <?php echo html_escape($q['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Result Filter -->
                        <div class="form-group mr-3 my-1">
                            <label for="filter_eval_result" class="mr-2 font-weight-bold text-dark font-13">
                                <i class="mdi mdi-filter-variant text-warning mr-1"></i> <?php echo get_phrase('filter_by_result'); ?>:
                            </label>
                            <select class="form-control form-control-sm select2" id="filter_eval_result" style="min-width: 140px;" onchange="syncEvalResultFilter(this.value)">
                                <option value="all"><?php echo get_phrase('all_results'); ?></option>
                                <option value="passed"><?php echo get_phrase('passed'); ?></option>
                                <option value="failed"><?php echo get_phrase('failed'); ?></option>
                            </select>
                        </div>

                        <!-- Date Range: From -->
                        <div class="form-group mr-2 my-1">
                            <label for="filter_eval_date_from" class="mr-2 font-weight-bold text-dark font-13">
                                <i class="mdi mdi-calendar mr-1"></i> <?php echo get_phrase('from'); ?>:
                            </label>
                            <input type="date" class="form-control form-control-sm" id="filter_eval_date_from" onchange="applyEvalFilters()">
                        </div>

                        <!-- Date Range: To -->
                        <div class="form-group mr-3 my-1">
                            <label for="filter_eval_date_to" class="mr-2 font-weight-bold text-dark font-13">
                                <?php echo get_phrase('to'); ?>:
                            </label>
                            <input type="date" class="form-control form-control-sm" id="filter_eval_date_to" onchange="applyEvalFilters()">
                        </div>
                    </div>

                    <!-- Reset Button -->
                    <div class="my-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetEvalFilters()">
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
                        <i class="mdi mdi-format-list-bulleted text-primary mr-1"></i> <?php echo get_phrase('evaluation_records'); ?>
                    </h4>
                    <p class="text-muted font-13 mb-0"><?php echo get_phrase('pharmacist_quiz_results_marks_and_pass_fail_competency_evaluations'); ?></p>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-centered w-100" id="evaluation_report_table">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th><?php echo get_phrase('pharmacist'); ?></th>
                                <th><?php echo get_phrase('assigned_store'); ?></th>
                                <th><?php echo get_phrase('course'); ?></th>
                                <th><?php echo get_phrase('quiz_assessment'); ?></th>
                                <th><?php echo get_phrase('obtained_marks'); ?></th>
                                <th><?php echo get_phrase('score_percentage'); ?></th>
                                <th><?php echo get_phrase('result'); ?></th>
                                <th><?php echo get_phrase('attempt_date'); ?></th>
                                <th class="text-center"><?php echo get_phrase('actions'); ?></th>
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
    var currentEvalResultFilter = 'all';
    var evalReportTable;

    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2();
        }

        evalReportTable = $('#evaluation_report_table').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            autoWidth: false,
            pageLength: 10,
            ajax: {
                url: "<?php echo site_url('admin/server_side_evaluation_report_data'); ?>",
                type: "POST",
                data: function(d) {
                    d.filter_result = currentEvalResultFilter;
                    d.filter_store_id = $('#filter_eval_store_id').val();
                    d.filter_course_id = $('#filter_eval_course_id').val();
                    d.filter_quiz_id = $('#filter_eval_quiz_id').val();
                    d.filter_date_from = $('#filter_eval_date_from').val();
                    d.filter_date_to = $('#filter_eval_date_to').val();
                },
                dataSrc: function(json) {
                    if (json.stats) {
                        $('#stat_eval_total').text(Number(json.stats.total).toLocaleString());
                        $('#stat_eval_passed').text(Number(json.stats.passed_count).toLocaleString());
                        $('#stat_eval_failed').text(Number(json.stats.failed_count).toLocaleString());
                        $('#stat_eval_pass_rate').text(json.stats.pass_rate + '%');
                        $('#stat_eval_avg_score').text(json.stats.avg_score + '%');
                    }
                    return json.data;
                }
            },
            columns: [
                { data: 0, orderable: false, width: '4%' },
                { data: 1, orderable: true, width: '20%' },
                { data: 2, orderable: true, width: '16%' },
                { data: 3, orderable: true, width: '15%' },
                { data: 4, orderable: true, width: '15%' },
                { data: 5, orderable: true, width: '8%' },
                { data: 6, orderable: true, width: '8%' },
                { data: 7, orderable: false, width: '6%' },
                { data: 8, orderable: true, width: '8%' },
                { data: 9, orderable: false, width: '5%', className: 'text-center' }
            ],
            order: [[8, 'desc']],
            language: {
                search: "<?php echo get_phrase('search'); ?>:",
                lengthMenu: "<?php echo get_phrase('display'); ?> _MENU_ <?php echo get_phrase('records'); ?>",
                info: "<?php echo get_phrase('showing'); ?> _START_ <?php echo get_phrase('to'); ?> _END_ <?php echo get_phrase('of'); ?> _TOTAL_ <?php echo get_phrase('records'); ?>",
                infoEmpty: "<?php echo get_phrase('showing'); ?> 0 <?php echo get_phrase('to'); ?> 0 <?php echo get_phrase('of'); ?> 0 <?php echo get_phrase('records'); ?>",
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

    function setResultFilter(res) {
        currentEvalResultFilter = res;
        $('#filter_eval_result').val(res).trigger('change.select2');
        $('.kpi-card').removeClass('active-filter');
        $('#card_res_' + res).addClass('active-filter');

        if (evalReportTable) {
            evalReportTable.ajax.reload();
        }
    }

    function syncEvalResultFilter(res) {
        currentEvalResultFilter = res;
        $('.kpi-card').removeClass('active-filter');
        $('#card_res_' + res).addClass('active-filter');

        if (evalReportTable) {
            evalReportTable.ajax.reload();
        }
    }

    function applyEvalFilters() {
        if (evalReportTable) {
            evalReportTable.ajax.reload();
        }
    }

    function loadCourseQuizzes(courseId) {
        var $quizSelect = $('#filter_eval_quiz_id');
        $quizSelect.find('option:not([value="all"])').each(function() {
            var cId = $(this).data('course');
            if (courseId === 'all' || !courseId || cId == courseId) {
                $(this).prop('disabled', false).show();
            } else {
                $(this).prop('disabled', true).hide();
            }
        });
        $quizSelect.val('all').trigger('change.select2');
    }

    function resetEvalFilters() {
        $('#filter_eval_store_id').val('all').trigger('change.select2');
        $('#filter_eval_course_id').val('all').trigger('change.select2');
        $('#filter_eval_quiz_id').val('all').trigger('change.select2');
        $('#filter_eval_result').val('all').trigger('change.select2');
        $('#filter_eval_date_from').val('');
        $('#filter_eval_date_to').val('');
        setResultFilter('all');
    }

    function exportEvaluationCsv() {
        var storeId = $('#filter_eval_store_id').val() || 'all';
        var courseId = $('#filter_eval_course_id').val() || 'all';
        var quizId = $('#filter_eval_quiz_id').val() || 'all';
        var result = currentEvalResultFilter || 'all';
        var dateFrom = $('#filter_eval_date_from').val() || '';
        var dateTo = $('#filter_eval_date_to').val() || '';
        var search = $('#evaluation_report_table_filter input').val() || '';

        var url = "<?php echo site_url('admin/export_pharmacist_evaluation_csv'); ?>?store_id=" + encodeURIComponent(storeId) +
                  "&course_id=" + encodeURIComponent(courseId) +
                  "&quiz_id=" + encodeURIComponent(quizId) +
                  "&result=" + encodeURIComponent(result) +
                  "&date_from=" + encodeURIComponent(dateFrom) +
                  "&date_to=" + encodeURIComponent(dateTo) +
                  "&search=" + encodeURIComponent(search);

        window.location.href = url;
    }
</script>
