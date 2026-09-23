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
                <div class="row d-flex justify-content-between">
                    <div class="col-xl-6">
                        <form class="form-inline" action="<?php echo site_url('admin/enrol_history/filter_by_date_range') ?>" method="get">
                            <div class="col-xl-10">
                                <div class="form-group">
                                    <div id="reportrange" class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light" style="width: 100%;">
                                        <i class="mdi mdi-calendar"></i>&nbsp;
                                        <span id="selectedValue"><?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , $timestamp_end);?></span> <i class="mdi mdi-menu-down"></i>
                                    </div>
                                    <input id="date_range" type="hidden" name="date_range" value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y" , $timestamp_end);?>">
                                </div>
                            </div>
                            <div class="col-xl-2">
                                <button type="submit" class="btn btn-info" id="submit-button" onclick="update_date_range();"> <?php echo get_phrase('filter');?></button>
                            </div>
                        </form>
                    </div>
                    <div class="col-xl-6">
                        <button type="button" class="btn btn-info float-end" id="export-button" onclick="export_csv();"> <?php echo get_phrase('Export CSV');?></button>
                    </div>
                </div>
                <div class="table-responsive-sm mt-4">
                    <?php if (count($enrol_history->result_array()) > 0): ?>
                        <table class="table table-striped table-centered mb-0">
                            <thead>
                                <tr>
                                    <th><?php echo get_phrase('photo'); ?></th>
                                    <th><?php echo get_phrase('user_name'); ?></th>
                                    <th><?php echo get_phrase('enrolled_course'); ?></th>
                                    <th><?php echo get_phrase('enrollment_date'); ?></th>
                                    <th><?php echo get_phrase('Expiry date'); ?></th>
                                    <th><?php echo get_phrase('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrol_history->result_array() as $enrol):
                                    $user_data = $this->db->get_where('users', array('id' => $enrol['user_id']))->row_array();
                                    $course_data = $this->db->get_where('course', array('id' => $enrol['course_id']))->row_array();?>
                                    <tr class="gradeU" data-enrol-id="<?php echo $enrol['id']; ?>">
                                        <td>
                                            <img src="<?php echo $this->user_model->get_user_image_url($enrol['user_id']); ?>" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">
                                        </td>
                                        <td>
                                            <b><?php echo htmlspecialchars(($user_data['first_name'] ?? 'N/A') . ' ' . ($user_data['last_name'] ?? '')); ?></b><br>
                                            <small><?php echo get_phrase('email') . ': ' . htmlspecialchars($user_data['email'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td><strong><?php if (!empty($course_data)): ?><a href="<?php echo site_url('admin/course_form/course_edit/' . $course_data['id']); ?>" target="_blank"><?php echo htmlspecialchars($course_data['title']); ?></a><?php else: ?><span class="text-muted"><?php echo get_phrase('course_not_found'); ?></span><?php endif; ?></strong></td>
                                        <td><?php echo date('D, d M Y', $enrol['date_added']); ?></td>
                                        <td>
                                        <?php if($enrol['expiry_date']): ?>
                                            <?php echo date('D, d M Y', $enrol['expiry_date']); ?>
                                        <?php else: ?>
                                            <?php echo get_phrase('Lifetime access'); ?>
                                        <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-outline-danger btn-icon btn-rounded btn-sm" onclick="confirm_modal('<?php echo site_url('admin/enrol_history_delete/'.$enrol['id']); ?>');"> <i class="dripicons-trash"></i> </button>
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
<script type="text/javascript">
    function update_date_range()
    {
        var x = $("#selectedValue").html();
        $("#date_range").val(x);
    }

    function export_csv() {
        // Collect all enrol IDs
        let enrolIds = [];
        document.querySelectorAll('tr.gradeU').forEach(row => {
            let enrolId = row.getAttribute('data-enrol-id');
            if (enrolId) {
                enrolIds.push(enrolId);
            }
        });

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

</script>
