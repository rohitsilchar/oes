<?php
    $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    $translated_month = array(
        get_phrase('jan'), get_phrase('feb'), get_phrase('mar'), get_phrase('apr'),
        get_phrase('may'), get_phrase('jun'), get_phrase('jul'), get_phrase('aug'),
        get_phrase('sep'), get_phrase('oct'), get_phrase('nov'), get_phrase('dec')
    );

    $month_wise_enrolments = array();
    $month_wise_income = array();

    for ($i = 0; $i < 12; $i++) {
        $first_day_of_month = "1 " . ucfirst($months[$i]) . " " . date("Y") . ' 00:00:00';
        $last_day_of_month = date("t", strtotime($first_day_of_month)) . " " . ucfirst($months[$i]) . " " . date("Y") . ' 23:59:59';
        $start_ts = strtotime($first_day_of_month);
        $end_ts = strtotime($last_day_of_month);

        // Monthly enrolments count
        $this->db->where('date_added >=', $start_ts);
        $this->db->where('date_added <=', $end_ts);
        $enrol_count = $this->db->count_all_results('enrol');
        $month_wise_enrolments[] = $enrol_count;

        // Monthly revenue
        $this->db->select_sum('admin_revenue');
        $this->db->where('date_added >=', $start_ts);
        $this->db->where('date_added <=', $end_ts);
        $rev_row = $this->db->get('payment')->row();
        $rev = ($rev_row && isset($rev_row->admin_revenue)) ? $rev_row->admin_revenue : 0;
        $month_wise_income[] = ($rev > 0) ? (float)$rev : 0;
    }

    $status_wise_courses = $this->crud_model->get_status_wise_courses();
    $number_of_active_course = isset($status_wise_courses['active']) ? $status_wise_courses['active']->num_rows() : 0;
    $number_of_pending_course = isset($status_wise_courses['pending']) ? $status_wise_courses['pending']->num_rows() : 0;

    $lic_yes = isset($licensed_pharmacists) ? (int)$licensed_pharmacists : 0;
    $total_p = isset($total_pharmacists) ? (int)$total_pharmacists : 0;
    $lic_no = max(0, $total_p - $lic_yes);

    // Store staffing chart data
    $store_chart_labels = array();
    $store_chart_data = array();
    if (!empty($store_distribution)) {
        foreach ($store_distribution as $st) {
            $lbl = !empty($st['store_code']) ? $st['store_name'] . ' (' . $st['store_code'] . ')' : $st['store_name'];
            $store_chart_labels[] = $lbl;
            $store_chart_data[] = (int)($st['pharmacist_count'] ?? 0);
        }
    } else {
        $store_chart_labels = array(get_phrase('no_stores'));
        $store_chart_data = array(0);
    }
?>

<script type="text/javascript">
(function($) {
    "use strict";

    var enrolChartInstance = null;
    var storeChartInstance = null;
    var complianceChartInstance = null;

    function initDashboardCharts() {
        if (typeof Chart === 'undefined') {
            return;
        }

        Chart.defaults.global.defaultFontFamily = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
        Chart.defaults.global.defaultFontColor = '#8391a2';

        // 1. Enrollment Trends Area Line Chart
        var enrolCanvas = document.getElementById('enrollment-trend-chart');
        if (enrolCanvas) {
            var ctx = enrolCanvas.getContext('2d');
            var gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(114, 124, 245, 0.40)');
            gradient.addColorStop(0.7, 'rgba(114, 124, 245, 0.05)');
            gradient.addColorStop(1, 'rgba(114, 124, 245, 0.0)');

            enrolChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($translated_month); ?>,
                    datasets: [{
                        label: "<?php echo get_phrase('course_enrollments'); ?>",
                        backgroundColor: gradient,
                        borderColor: '#727cf5',
                        pointBackgroundColor: '#727cf5',
                        pointBorderColor: '#ffffff',
                        pointHoverBackgroundColor: '#ffffff',
                        pointHoverBorderColor: '#727cf5',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 3,
                        lineTension: 0.35,
                        fill: true,
                        data: <?php echo json_encode($month_wise_enrolments); ?>
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        backgroundColor: '#313a46',
                        titleFontColor: '#ffffff',
                        bodyFontColor: '#ffffff',
                        bodyFontSize: 13,
                        xPadding: 12,
                        yPadding: 10,
                        displayColors: false,
                        cornerRadius: 6,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return ' ' + tooltipItem.yLabel + ' <?php echo get_phrase('enrolled_records'); ?>';
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                fontColor: '#98a6ad',
                                fontSize: 11
                            }
                        }],
                        yAxes: [{
                            gridLines: {
                                color: 'rgba(235, 237, 242, 0.7)',
                                borderDash: [4, 4],
                                drawBorder: false
                            },
                            ticks: {
                                beginAtZero: true,
                                precision: 0,
                                fontColor: '#98a6ad',
                                fontSize: 11,
                                padding: 10
                            }
                        }]
                    }
                }
            });
        }

        // 2. Store Staffing Distribution Bar Chart
        var storeCanvas = document.getElementById('store-staffing-chart');
        if (storeCanvas) {
            var ctxStore = storeCanvas.getContext('2d');
            storeChartInstance = new Chart(ctxStore, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($store_chart_labels); ?>,
                    datasets: [{
                        label: "<?php echo get_phrase('pharmacists_assigned'); ?>",
                        backgroundColor: '#ffbc00',
                        hoverBackgroundColor: '#e5a900',
                        borderRadius: 4,
                        borderWidth: 0,
                        data: <?php echo json_encode($store_chart_data); ?>
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        backgroundColor: '#313a46',
                        titleFontColor: '#ffffff',
                        bodyFontColor: '#ffffff',
                        cornerRadius: 6,
                        callbacks: {
                            label: function(tooltipItem) {
                                return ' ' + tooltipItem.yLabel + ' <?php echo get_phrase('pharmacists'); ?>';
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                fontColor: '#98a6ad',
                                fontSize: 11
                            }
                        }],
                        yAxes: [{
                            gridLines: {
                                color: 'rgba(235, 237, 242, 0.7)',
                                borderDash: [4, 4],
                                drawBorder: false
                            },
                            ticks: {
                                beginAtZero: true,
                                precision: 0,
                                fontColor: '#98a6ad',
                                fontSize: 11,
                                padding: 10
                            }
                        }]
                    }
                }
            });
        }

        // 3. Course & Compliance Doughnut Chart
        var complianceCanvas = document.getElementById('compliance-status-chart');
        if (complianceCanvas) {
            var ctx2 = complianceCanvas.getContext('2d');
            complianceChartInstance = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: [
                        "<?php echo get_phrase('licensed_pharmacists'); ?>",
                        "<?php echo get_phrase('pending_licence'); ?>",
                        "<?php echo get_phrase('active_courses'); ?>"
                    ],
                    datasets: [{
                        data: [
                            <?php echo max(0, $lic_yes); ?>,
                            <?php echo max(0, $lic_no); ?>,
                            <?php echo max(0, $number_of_active_course); ?>
                        ],
                        backgroundColor: [
                            '#0acf97',
                            '#fa5c7c',
                            '#727cf5'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverBorderColor: '#ffffff'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    cutoutPercentage: 75,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        backgroundColor: '#313a46',
                        titleFontColor: '#ffffff',
                        bodyFontColor: '#ffffff',
                        xPadding: 12,
                        yPadding: 10,
                        cornerRadius: 6
                    }
                }
            });
        }
    }

    // Toggle between Enrollment and Store distribution views
    window.toggleDashboardChart = function(chartType) {
        if (chartType === 'enrol') {
            $('#chart-wrap-enrol').show();
            $('#chart-wrap-store').hide();
            $('#btn-view-enrol').addClass('btn-primary').removeClass('btn-outline-primary');
            $('#btn-view-store').addClass('btn-outline-primary').removeClass('btn-primary');
            if (enrolChartInstance) enrolChartInstance.resize();
        } else {
            $('#chart-wrap-enrol').hide();
            $('#chart-wrap-store').show();
            $('#btn-view-store').addClass('btn-primary').removeClass('btn-outline-primary');
            $('#btn-view-enrol').addClass('btn-outline-primary').removeClass('btn-primary');
            if (storeChartInstance) storeChartInstance.resize();
        }
    };

    $(document).ready(function() {
        initDashboardCharts();
    });
})(window.jQuery);
</script>
