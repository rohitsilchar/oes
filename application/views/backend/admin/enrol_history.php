<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('enrol_history'); ?>
                    <a href="<?php echo site_url('admin/enrol_student'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo get_phrase('enrol_a_student'); ?></a>
                    <button type="button" class="btn btn-outline-info btn-rounded alignToTitle mr-1" data-toggle="modal" data-target="#bulkImportEnrollmentModal">
                        <i class="mdi mdi-upload"></i> <?php echo get_phrase('bulk_import'); ?>
                    </button>
                </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<!-- Bulk Import Course Enrollment Modal -->
<div class="modal fade" id="bulkImportEnrollmentModal" tabindex="-1" role="dialog" aria-labelledby="bulkImportEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="bulkImportEnrollmentModalLabel"><i class="mdi mdi-school mr-1"></i> <?php echo get_phrase('bulk_import'); ?> - <?php echo get_phrase('course_enrollment'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form action="<?php echo site_url('admin/bulk_import/enrollments'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="mdi mdi-information-outline mr-1"></i> <strong><?php echo get_phrase('instructions'); ?>:</strong>
                                <ul class="mb-0 mt-1 pl-3">
                                    <li><?php echo get_phrase('supported_file_types'); ?>.</li>
                                    <li><strong>user_email</strong> (or employee_id / user_id) is mandatory.</li>
                                    <li><strong>course_title</strong> (or course_id) is mandatory. Multiple courses can also be comma-separated.</li>
                                    <li><strong>expiry_days</strong> is optional. If empty, the course's default expiry duration is applied.</li>
                                    <li>If already enrolled, the existing enrollment record will be updated.</li>
                                </ul>
                            </div>
                            <div class="ml-3">
                                <a href="<?php echo site_url('admin/download_sample_template/enrollments'); ?>" class="btn btn-success btn-rounded text-nowrap">
                                    <i class="mdi mdi-download"></i> <?php echo get_phrase('download_sample_template'); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mb-3" style="overflow-x: auto !important;">
                        <small class="text-muted font-weight-bold"><?php echo get_phrase('template_columns_preview'); ?>:</small>
                        <table class="table table-bordered table-sm mt-1 mb-0" style="font-size: 12px; min-width: 600px; white-space: nowrap;">
                            <thead class="thead-light">
                                <tr>
                                    <th>user_email <span class="text-danger">*</span></th>
                                    <th>course_title <span class="text-danger">*</span></th>
                                    <th>expiry_days</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>john.doe@example.com</td>
                                    <td>First Course</td>
                                    <td>180</td>
                                </tr>
                                <tr>
                                    <td>jane.smith@example.com</td>
                                    <td>1</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group">
                        <label for="import_file_enrollments"><?php echo get_phrase('select_csv_or_excel_file'); ?><span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="import_file_enrollments" name="import_file" accept=".csv, .xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv" required onchange="$(this).next('.custom-file-label').html(this.files[0].name)">
                            <label class="custom-file-label" for="import_file_enrollments"><?php echo get_phrase('choose_file'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo get_phrase('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-upload"></i> <?php echo get_phrase('import'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3 header-title"><?php echo get_phrase('enrol_histories'); ?></h4>
                <div class="row mb-3">
                    <div class="col-xl-12">
                        <form id="enrol_filter_form" action="<?php echo site_url('admin/enrol_history/filter_by_date_range') ?>" method="get" onsubmit="update_date_range();">
                            <div class="row align-items-end">
                                <!-- Date Range Filter -->
                                <div class="col-xl-4 col-md-5 col-sm-12 mb-2">
                                    <label class="text-muted font-13 font-weight-bold"><?php echo get_phrase('date_range'); ?></label>
                                    <div id="reportrange" class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue" data-cancel-class="btn-light" style="width: 100%;">
                                        <i class="mdi mdi-calendar"></i>&nbsp;
                                        <span id="selectedValue"><?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , $timestamp_end);?></span> <i class="mdi mdi-menu-down"></i>
                                    </div>
                                    <input id="date_range" type="hidden" name="date_range" value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y" , $timestamp_end);?>">
                                </div>

                                <!-- Course Filter -->
                                <div class="col-xl-4 col-md-4 col-sm-12 mb-2">
                                    <label for="filter_course_id" class="text-muted font-13 font-weight-bold"><?php echo get_phrase('course'); ?></label>
                                    <select class="form-control select2" data-toggle="select2" name="course_id" id="filter_course_id">
                                        <option value="all" <?php if (empty($selected_course_id) || $selected_course_id == 'all') echo 'selected'; ?>><?php echo get_phrase('all_courses'); ?></option>
                                        <?php 
                                        $filter_courses = !empty($courses) ? $courses : $this->crud_model->get_courses()->result_array();
                                        foreach ($filter_courses as $c): ?>
                                            <option value="<?php echo $c['id']; ?>" <?php if (!empty($selected_course_id) && $selected_course_id == $c['id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($c['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Action Buttons -->
                                <div class="col-xl-4 col-md-3 col-sm-12 mb-2 d-flex justify-content-between align-items-center">
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-info mr-1" id="submit-button" onclick="update_date_range();">
                                            <i class="mdi mdi-filter mr-1"></i><?php echo get_phrase('filter');?>
                                        </button>
                                        <a href="<?php echo site_url('admin/enrol_history'); ?>" class="btn btn-light" title="<?php echo get_phrase('reset'); ?>">
                                            <i class="mdi mdi-refresh"></i>
                                        </a>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-info" id="export-button" onclick="export_csv();">
                                            <i class="mdi mdi-download mr-1"></i><?php echo get_phrase('Export CSV');?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive-sm mt-4">
                    <?php if (count($enrol_history->result_array()) > 0): ?>
                        <table id="basic-datatable" class="table table-striped table-centered mb-0" data-page-length="10" data-order='[[3, "desc"]]'>
                            <thead>
                                <tr>
                                    <th data-orderable="false"><?php echo get_phrase('photo'); ?></th>
                                    <th><?php echo get_phrase('user_name'); ?></th>
                                    <th><?php echo get_phrase('enrolled_course'); ?></th>
                                    <th><?php echo get_phrase('enrollment_date'); ?></th>
                                    <th><?php echo get_phrase('Expiry date'); ?></th>
                                    <th data-orderable="false"><?php echo get_phrase('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrol_history->result_array() as $enrol):
                                    $user_name = trim(($enrol['first_name'] ?? 'N/A') . ' ' . ($enrol['last_name'] ?? ''));
                                    $user_email = $enrol['email'] ?? 'N/A';
                                    $course_title = $enrol['course_title'] ?? '';
                                    $user_img = $this->user_model->get_user_image_url($enrol['user_id'], $enrol['user_image'] ?? null);
                                ?>
                                    <tr class="gradeU" data-enrol-id="<?php echo $enrol['id']; ?>">
                                        <td>
                                            <img src="<?php echo $user_img; ?>" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">
                                        </td>
                                        <td>
                                            <b><?php echo htmlspecialchars($user_name); ?></b><br>
                                            <small><?php echo get_phrase('email') . ': ' . htmlspecialchars($user_email); ?></small>
                                        </td>
                                        <td><strong><?php if (!empty($course_title)): ?><a href="<?php echo site_url('admin/course_form/course_edit/' . $enrol['course_id']); ?>" target="_blank"><?php echo htmlspecialchars($course_title); ?></a><?php else: ?><span class="text-muted"><?php echo get_phrase('course_not_found'); ?></span><?php endif; ?></strong></td>
                                        <td data-order="<?php echo $enrol['date_added']; ?>"><?php echo date('D, d M Y', $enrol['date_added']); ?></td>
                                        <td data-order="<?php echo !empty($enrol['expiry_date']) ? $enrol['expiry_date'] : '9999999999'; ?>">
                                        <?php if($enrol['expiry_date']): ?>
                                            <?php echo date('D, d M Y', $enrol['expiry_date']); ?>
                                        <?php else: ?>
                                            <?php echo get_phrase('Lifetime access'); ?>
                                        <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-outline-info btn-icon btn-rounded btn-sm mr-1 btn-edit-enrol-history" 
                                                    data-enrol-id="<?php echo $enrol['id']; ?>"
                                                    data-user-name="<?php echo htmlspecialchars($user_name); ?>"
                                                    data-user-email="<?php echo htmlspecialchars($user_email); ?>"
                                                    data-course-id="<?php echo $enrol['course_id']; ?>"
                                                    data-course-title="<?php echo htmlspecialchars($course_title); ?>"
                                                    data-expiry-date="<?php echo !empty($enrol['expiry_date']) ? date('Y-m-d', $enrol['expiry_date']) : ''; ?>"
                                                    data-is-lifetime="<?php echo empty($enrol['expiry_date']) ? '1' : '0'; ?>"
                                                    title="<?php echo get_phrase('edit'); ?>">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-icon btn-rounded btn-sm" onclick="confirm_modal('<?php echo site_url('admin/enrol_history_delete/'.$enrol['id']); ?>');" title="<?php echo get_phrase('delete'); ?>"> <i class="dripicons-trash"></i> </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <?php if (count($enrol_history->result_array()) == 0): ?>
                        <div class="img-fluid w-100 text-center">
                        <img style="opacity: 1; width: 100px;" src="<?php echo base_url('assets/backend/images/file-search.svg'); ?>"><br>
                        <?php echo get_phrase('no_data_found'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<!-- Modal: Edit Single Enrolment History Record -->
<?php $available_courses = !empty($courses) ? $courses : $this->crud_model->get_courses()->result_array(); ?>
<div class="modal fade" id="editEnrolHistoryModal" tabindex="-1" role="dialog" aria-labelledby="editEnrolHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h4 class="modal-title font-weight-bold" id="editEnrolHistoryModalLabel">
                    <i class="mdi mdi-square-edit-outline mr-1 text-info"></i> <?php echo get_phrase('edit_enrolment_record'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form action="" method="post" id="form_edit_enrol_history">
                <div class="modal-body p-3">
                    <!-- User Info Banner -->
                    <div class="card border mb-3 shadow-none bg-light">
                        <div class="card-body py-2 px-3">
                            <h5 class="mb-0 font-weight-bold text-dark" id="history_modal_user_name"></h5>
                            <div class="text-muted font-13" id="history_modal_user_email"></div>
                        </div>
                    </div>

                    <!-- Course to Enrol -->
                    <div class="form-group mb-3">
                        <label for="history_edit_course_id" class="font-weight-bold">
                            <?php echo get_phrase('course_to_enrol'); ?> <span class="text-danger">*</span>
                        </label>
                        <select class="form-control select2" data-toggle="select2" name="course_id" id="history_edit_course_id" required style="width: 100%;">
                            <?php foreach ($available_courses as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Access Duration (Days) -->
                    <div class="form-group mb-2">
                        <label for="history_edit_expiry_days" class="font-weight-bold">
                            <?php echo get_phrase('access_duration_(days)'); ?>
                            <small class="text-muted font-weight-normal">(<?php echo get_phrase('optional'); ?>)</small>
                        </label>
                        <input type="number" min="0" class="form-control" name="expiry_days" id="history_edit_expiry_days" 
                               placeholder="<?php echo get_phrase('enter_days_or_0_for_lifetime_(leave_blank_to_keep_current)'); ?>">
                    </div>

                    <!-- Presets -->
                    <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 5px;">
                        <small class="text-muted mr-1 font-weight-bold"><?php echo get_phrase('presets'); ?>:</small>
                        <button type="button" class="btn btn-xs btn-outline-secondary btn-history-duration-preset" data-days="30" style="padding: 2px 8px; font-size: 11px; border-radius: 12px;">30d</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary btn-history-duration-preset" data-days="60" style="padding: 2px 8px; font-size: 11px; border-radius: 12px;">60d</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary btn-history-duration-preset" data-days="90" style="padding: 2px 8px; font-size: 11px; border-radius: 12px;">90d</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary btn-history-duration-preset" data-days="180" style="padding: 2px 8px; font-size: 11px; border-radius: 12px;">180d</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary btn-history-duration-preset" data-days="365" style="padding: 2px 8px; font-size: 11px; border-radius: 12px;">365d (1yr)</button>
                        <button type="button" class="btn btn-xs btn-outline-success btn-history-duration-preset" data-days="0" style="padding: 2px 8px; font-size: 11px; border-radius: 12px;">Lifetime</button>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-history-duration-preset" data-days="" style="padding: 2px 8px; font-size: 11px; border-radius: 12px;">Clear</button>
                    </div>

                    <!-- Specific Expiry Date -->
                    <div class="form-group mb-2">
                        <label for="history_edit_expiry_date" class="font-weight-bold">
                            <?php echo get_phrase('or_specific_expiry_date'); ?>
                            <small class="text-muted font-weight-normal">(<?php echo get_phrase('optional'); ?>)</small>
                        </label>
                        <input type="date" class="form-control" name="expiry_date" id="history_edit_expiry_date">
                        <small class="text-muted font-12 d-block mt-1">
                            <?php echo get_phrase('leave_both_blank_to_keep_the_existing_expiry_date_unchanged'); ?>.
                        </small>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-rounded" data-dismiss="modal"><?php echo get_phrase('cancel'); ?></button>
                    <button type="submit" class="btn btn-info btn-rounded font-weight-bold">
                        <i class="mdi mdi-check-circle mr-1"></i> <?php echo get_phrase('save_changes'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    function update_date_range()
    {
        var x = $("#selectedValue").html();
        $("#date_range").val(x);
    }

    function export_csv() {
        // Collect all enrol IDs across all pages or filtered rows
        let enrolIds = [];
        if (window.jQuery && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#basic-datatable')) {
            var dt = $('#basic-datatable').DataTable();
            var rows = dt.rows({ search: 'applied' }).nodes();
            $(rows).each(function() {
                var enrolId = $(this).attr('data-enrol-id');
                if (enrolId) {
                    enrolIds.push(enrolId);
                }
            });
            // Fallback to all rows in table if empty
            if (enrolIds.length === 0) {
                dt.$('tr.gradeU').each(function() {
                    var enrolId = $(this).attr('data-enrol-id');
                    if (enrolId) {
                        enrolIds.push(enrolId);
                    }
                });
            }
        } else {
            document.querySelectorAll('tr.gradeU').forEach(row => {
                let enrolId = row.getAttribute('data-enrol-id');
                if (enrolId) {
                    enrolIds.push(enrolId);
                }
            });
        }

        if (enrolIds.length === 0) {
            if (typeof error_notify === 'function') {
                error_notify('<?php echo get_phrase('no_records_to_export'); ?>');
            } else {
                alert('No records to export');
            }
            return;
        }

        // Send the enrol IDs to the server using AJAX
        $.ajax({
            url: "<?php echo site_url('admin/export_enrol_history_csv'); ?>",
            method: "POST",
            data: { enrol_ids: enrolIds },
            success: function(response) {
                // Trigger download
                let blob = new Blob([response], { type: 'text/csv' });
                let link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'enrol_history.csv';
                link.click();
            },
            error: function(xhr, status, error) {
                console.error('Error generating CSV:', error);
            }
        });
    }

    $(document).ready(function() {
        // Edit button click in enrol history (delegated to work across paginated/filtered DataTables rows)
        $(document).on('click', '.btn-edit-enrol-history', function() {
            var enrolId = $(this).data('enrol-id');
            var userName = $(this).data('user-name') || '';
            var userEmail = $(this).data('user-email') || '';
            var courseId = $(this).data('course-id');
            var expiryDate = $(this).data('expiry-date') || '';
            var isLifetime = $(this).data('is-lifetime');

            $('#form_edit_enrol_history').attr('action', '<?php echo site_url("admin/enrol_history_edit"); ?>/' + enrolId);
            $('#history_modal_user_name').text(userName);
            $('#history_modal_user_email').text(userEmail);
            $('#history_edit_course_id').val(courseId).trigger('change');

            if (isLifetime == '1' || isLifetime === 1) {
                $('#history_edit_expiry_days').val('');
                $('#history_edit_expiry_date').val('');
            } else if (expiryDate) {
                $('#history_edit_expiry_date').val(expiryDate);
                // Calculate days from today
                var target = new Date(expiryDate + 'T00:00:00');
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                var diffDays = Math.ceil((target.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
                $('#history_edit_expiry_days').val(diffDays > 0 ? diffDays : '');
            } else {
                $('#history_edit_expiry_days').val('');
                $('#history_edit_expiry_date').val('');
            }

            $('#editEnrolHistoryModal').modal('show');
        });

        // Ensure Select2 in modal works cleanly
        $('#editEnrolHistoryModal').on('shown.bs.modal', function () {
            $('#history_edit_course_id').select2({
                dropdownParent: $('#editEnrolHistoryModal'),
                width: '100%'
            });
        });

        // History preset click handler
        $(document).on('click', '.btn-history-duration-preset', function() {
            var days = $(this).data('days');
            $('#history_edit_expiry_days').val(days).trigger('input');
        });

        // Two-way sync: Days -> Date
        $('#history_edit_expiry_days').on('input change', function() {
            var val = $(this).val().trim();
            if (val === '' || isNaN(val)) {
                $('#history_edit_expiry_date').val('');
            } else {
                var days = parseInt(val, 10);
                if (days > 0) {
                    var target = new Date();
                    target.setDate(target.getDate() + days);
                    var yyyy = target.getFullYear();
                    var mm = String(target.getMonth() + 1).padStart(2, '0');
                    var dd = String(target.getDate()).padStart(2, '0');
                    $('#history_edit_expiry_date').val(yyyy + '-' + mm + '-' + dd);
                } else if (days === 0) {
                    $('#history_edit_expiry_date').val('');
                }
            }
        });

        // Two-way sync: Date -> Days
        $('#history_edit_expiry_date').on('change', function() {
            var val = $(this).val();
            if (val) {
                var target = new Date(val + 'T00:00:00');
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                var diffDays = Math.ceil((target.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
                if (diffDays >= 0) {
                    $('#history_edit_expiry_days').val(diffDays);
                }
            }
        });
    });
</script>
