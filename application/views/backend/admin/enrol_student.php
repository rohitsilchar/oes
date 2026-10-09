<style>
  .table-responsive {
    display: block;
    width: 100%;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
  }
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12 {
    overflow-x: auto !important;
    padding-bottom: 8px;
  }
  #users_enrolment_datatable {
    width: 100% !important;
    min-width: 1100px;
  }
  #users_enrolment_datatable th, 
  #users_enrolment_datatable td {
    white-space: nowrap;
    vertical-align: middle;
  }
  /* Custom bottom horizontal scrollbar */
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
  .user-selected-chip {
    background: #eef2f7;
    border: 1px solid #ced4da;
    border-radius: 16px;
    padding: 3px 10px;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  .user-checkbox {
    width: 17px;
    height: 17px;
    cursor: pointer;
  }
  #check_all_users {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }
  .course-badge {
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    vertical-align: middle;
  }
  .btn-xs {
    padding: 2px 8px;
    font-size: 11px;
    line-height: 1.4;
    border-radius: 12px;
  }
  .btn-duration-preset {
    font-weight: 500;
    cursor: pointer;
  }
</style>

<!-- start page title -->
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                <h4 class="page-title mb-0"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('course_enrolment'); ?> </h4>
                <div>
                    <a href="<?php echo site_url('admin/enrol_history'); ?>" class="btn btn-outline-primary btn-rounded mr-1">
                        <i class="mdi mdi-history mr-1"></i><?php echo get_phrase('enrol_history'); ?>
                    </a>
                    <button type="button" class="btn btn-outline-info btn-rounded mr-1" data-toggle="modal" data-target="#bulkImportEnrollmentModal">
                        <i class="mdi mdi-upload mr-1"></i> <?php echo get_phrase('bulk_import'); ?>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-rounded" data-toggle="modal" data-target="#manualEnrolmentModal">
                        <i class="mdi mdi-format-list-bulleted mr-1"></i> <?php echo get_phrase('manual_enrolment_form'); ?>
                    </button>
                </div>
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
                                    <td>bhargav@example.com</td>
                                    <td>First Course</td>
                                    <td>180</td>
                                </tr>
                                <tr>
                                    <td>robert.fox@example.com</td>
                                    <td>1</td>
                                    <td>30</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group">
                        <label for="import_file_enrollments_student"><?php echo get_phrase('select_csv_or_excel_file'); ?><span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="import_file_enrollments_student" name="import_file" accept=".csv, .xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv" required onchange="$(this).next('.custom-file-label').html(this.files[0].name)">
                            <label class="custom-file-label" for="import_file_enrollments_student"><?php echo get_phrase('choose_file'); ?></label>
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

<!-- Manual Enrolment Modal (Single / Multi Select2 Form) -->
<div class="modal fade" id="manualEnrolmentModal" tabindex="-1" role="dialog" aria-labelledby="manualEnrolmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="manualEnrolmentModalLabel"><i class="mdi mdi-format-list-bulleted mr-1"></i> <?php echo get_phrase('manual_enrolment_form'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form class="required-form" action="<?php echo site_url('admin/enrol_student/enrol'); ?>" method="post">
                <input type="hidden" name="redirect_to" value="admin/enrol_student">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="multiple_user_id"><?php echo get_phrase('users'); ?><span class="text-danger">*</span></label>
                        <select class="server-side-select2" action="<?php echo base_url('admin/get_select2_user_data'); ?>" name="user_id[]" id="multiple_user_id" multiple="multiple" style="width: 100%;" required>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="multiple_course_id"><?php echo get_phrase('course_to_enrol'); ?><span class="text-danger">*</span></label>
                        <select class="select2 form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose course(s)..." name="course_id[]" id="multiple_course_id" style="width: 100%;" required>
                            <option value=""><?php echo get_phrase('select_a_course'); ?></option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="manual_expiry_days"><?php echo get_phrase('expiry_days'); ?> <small class="text-muted">(<?php echo get_phrase('optional'); ?>)</small></label>
                        <input type="number" min="1" class="form-control" name="expiry_days" id="manual_expiry_days" placeholder="e.g. 30, 60 (Leave blank for course default)">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo get_phrase('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-school mr-1"></i> <?php echo get_phrase('enrol_pharmacist'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3 header-title"><i class="mdi mdi-filter-variant mr-1"></i> <?php echo get_phrase('filter_users_table'); ?></h5>
                <form action="<?php echo site_url('admin/enrol_student'); ?>" method="get">
                    <div class="row">
                        <!-- Store Filter -->
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label for="filter_store_id" class="text-muted font-13 font-weight-bold"><?php echo get_phrase('store'); ?></label>
                            <select class="form-control select2" data-toggle="select2" name="store_id" id="filter_store_id">
                                <option value="all" <?php if ($selected_store_id == 'all') echo 'selected'; ?>><?php echo get_phrase('all_stores'); ?></option>
                                <option value="no_store" <?php if ($selected_store_id == 'no_store') echo 'selected'; ?>><?php echo get_phrase('no_store_assigned'); ?></option>
                                <?php foreach ($stores as $store): ?>
                                    <option value="<?php echo $store['id']; ?>" <?php if ($selected_store_id == $store['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($store['store_name']) . ' (' . htmlspecialchars($store['store_code']) . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Course Filter -->
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label for="filter_course_id" class="text-muted font-13 font-weight-bold"><?php echo get_phrase('course'); ?></label>
                            <select class="form-control select2" data-toggle="select2" name="course_id" id="filter_course_id">
                                <option value="all" <?php if ($selected_course_id == 'all') echo 'selected'; ?>><?php echo get_phrase('all_courses'); ?></option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php if ($selected_course_id == $c['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($c['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Enrolment Status Filter -->
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label for="filter_enrol_status" class="text-muted font-13 font-weight-bold"><?php echo get_phrase('enrolment_status'); ?></label>
                            <select class="form-control select2" data-toggle="select2" name="enrol_status" id="filter_enrol_status">
                                <option value="all" <?php if ($selected_enrol_status == 'all') echo 'selected'; ?>><?php echo get_phrase('all'); ?></option>
                                <option value="enrolled" <?php if ($selected_enrol_status == 'enrolled') echo 'selected'; ?>><?php echo get_phrase('enrolled'); ?></option>
                                <option value="not_enrolled" <?php if ($selected_enrol_status == 'not_enrolled') echo 'selected'; ?>><?php echo get_phrase('not_enrolled'); ?></option>
                            </select>
                        </div>

                        <!-- Account Status Filter -->
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label for="filter_status" class="text-muted font-13 font-weight-bold"><?php echo get_phrase('status'); ?></label>
                            <select class="form-control select2" data-toggle="select2" name="status" id="filter_status">
                                <option value="all" <?php if ($selected_status === 'all') echo 'selected'; ?>><?php echo get_phrase('all'); ?></option>
                                <option value="1" <?php if ($selected_status === '1') echo 'selected'; ?>><?php echo get_phrase('active'); ?></option>
                                <option value="0" <?php if ($selected_status === '0') echo 'selected'; ?>><?php echo get_phrase('inactive'); ?></option>
                            </select>
                        </div>

                        <!-- Role Filter -->
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label for="filter_role" class="text-muted font-13 font-weight-bold"><?php echo get_phrase('role'); ?></label>
                            <select class="form-control select2" data-toggle="select2" name="role" id="filter_role">
                                <option value="all" <?php if ($selected_role == 'all') echo 'selected'; ?>><?php echo get_phrase('all'); ?></option>
                                <option value="pharmacist" <?php if ($selected_role == 'pharmacist') echo 'selected'; ?>><?php echo get_phrase('pharmacists'); ?></option>
                                <option value="instructor" <?php if ($selected_role == 'instructor') echo 'selected'; ?>><?php echo get_phrase('instructors'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12 d-flex justify-content-end align-items-center">
                            <a href="<?php echo site_url('admin/enrol_student'); ?>" class="btn btn-outline-secondary btn-rounded mr-2">
                                <i class="mdi mdi-refresh mr-1"></i><?php echo get_phrase('reset'); ?>
                            </a>
                            <button type="submit" class="btn btn-primary btn-rounded">
                                <i class="mdi mdi-filter mr-1"></i><?php echo get_phrase('apply_filters'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Users Table Card -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="header-title mb-1">
                            <i class="mdi mdi-account-group mr-1 text-primary"></i><?php echo get_phrase('users_list'); ?>
                            <span class="badge badge-secondary ml-1"><?php echo count($users); ?></span>
                        </h4>
                        <p class="text-muted font-13 mb-0"><?php echo get_phrase('select_users_and_click_add_course_for_selected_users'); ?></p>
                    </div>

                    <div class="d-flex align-items-center mt-2 mt-sm-0">
                        <span id="selection_badge" class="badge badge-info p-2 mr-2" style="font-size: 13px; display: none;">
                            <i class="mdi mdi-checkbox-marked-circle-outline mr-1"></i><span id="selected_users_count">0</span> <?php echo get_phrase('selected'); ?>
                        </span>

                        <button type="button" class="btn btn-sm btn-outline-danger btn-rounded mr-2" id="btn_deselect_all" style="display: none;">
                            <i class="mdi mdi-close-circle mr-1"></i><?php echo get_phrase('deselect_all'); ?>
                        </button>

                        <button type="button" class="btn btn-success btn-rounded shadow-sm" id="btn_open_enroll_modal" disabled>
                            <i class="mdi mdi-school mr-1"></i> <?php echo get_phrase('add_course_for_selected_users'); ?>
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="users_enrolment_datatable" class="table table-striped table-centered mb-0 w-100">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;" class="no-sort">
                                    <input type="checkbox" id="check_all_users" onclick="event.stopPropagation();" title="<?php echo get_phrase('select_all'); ?>">
                                </th>
                                <th style="width: 30px;">#</th>
                                <th><?php echo get_phrase('photo'); ?></th>
                                <th><?php echo get_phrase('name'); ?></th>
                                <th><?php echo get_phrase('email'); ?></th>
                                <th><?php echo get_phrase('role'); ?></th>
                                <th><?php echo get_phrase('employee_id'); ?></th>
                                <th><?php echo get_phrase('store'); ?></th>
                                <th><?php echo get_phrase('enrolled_courses'); ?></th>
                                <th><?php echo get_phrase('status'); ?></th>
                                <th><?php echo get_phrase('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $key => $user): 
                                $user_id = $user['id'];
                                $full_name = trim($user['first_name'] . ' ' . $user['last_name']);
                                $enrolled_list = $user_enrolments[$user_id] ?? [];
                                $user_photo = $this->user_model->get_user_image_url($user_id);
                            ?>
                                <tr id="user_row_<?php echo $user_id; ?>">
                                    <td class="text-center">
                                        <input type="checkbox" class="user-checkbox" 
                                               value="<?php echo $user_id; ?>" 
                                               data-name="<?php echo htmlspecialchars($full_name); ?>" 
                                               data-email="<?php echo htmlspecialchars($user['email']); ?>">
                                    </td>
                                    <td><?php echo $key + 1; ?></td>
                                    <td>
                                        <img src="<?php echo $user_photo; ?>" alt="" height="36" width="36" class="img-fluid rounded-circle img-thumbnail shadow-sm">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($full_name); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if (!empty($user['is_instructor'])): ?>
                                            <span class="badge badge-info-lighten"><?php echo get_phrase('instructor'); ?></span>
                                        <?php elseif (!empty($user['store_role_title'])): ?>
                                            <span class="badge badge-primary-lighten"><?php echo htmlspecialchars($user['store_role_title']); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary-lighten"><?php echo get_phrase('pharmacist'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($user['employee_id'])): ?>
                                            <code><?php echo htmlspecialchars($user['employee_id']); ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($user['store_name'])): ?>
                                            <span class="badge badge-primary-lighten"><?php echo htmlspecialchars($user['store_name']); ?></span>
                                            <?php if (!empty($user['store_code'])): ?>
                                                <small class="text-muted d-block"><?php echo htmlspecialchars($user['store_code']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted"><?php echo get_phrase('none'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (count($enrolled_list) > 0): ?>
                                            <div class="d-flex flex-wrap" style="gap: 3px; max-width: 260px;">
                                                <?php foreach ($enrolled_list as $enrol_item): 
                                                    $is_expired = (!empty($enrol_item['expiry_date']) && $enrol_item['expiry_date'] < time());
                                                    $expiry_label = empty($enrol_item['expiry_date']) ? get_phrase('lifetime_access') : ($is_expired ? get_phrase('expired_on') . ' ' . date('d M Y', $enrol_item['expiry_date']) : get_phrase('expires_on') . ' ' . date('d M Y', $enrol_item['expiry_date']));
                                                    $badge_class = $is_expired ? 'badge-danger-lighten' : 'badge-success-lighten';
                                                ?>
                                                    <span class="badge <?php echo $badge_class; ?> course-badge" 
                                                          data-toggle="tooltip" data-placement="top" 
                                                          title="<?php echo htmlspecialchars(($enrol_item['course_title'] ?? 'Course') . ' — ' . $expiry_label); ?>">
                                                        <i class="mdi mdi-book-open-page-variant mr-1"></i><?php echo htmlspecialchars($enrol_item['course_title'] ?? 'Course'); ?>
                                                        <?php if ($is_expired): ?>
                                                            <span class="text-danger ml-1 font-weight-bold">•</span>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge badge-secondary-lighten"><?php echo get_phrase('not_enrolled'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] == 1): ?>
                                            <span class="badge badge-success"><?php echo get_phrase('active'); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><?php echo get_phrase('inactive'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center" style="gap: 5px;">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-single-enrol" 
                                                    data-user-id="<?php echo $user_id; ?>" 
                                                    data-name="<?php echo htmlspecialchars($full_name); ?>" 
                                                    data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                    title="<?php echo get_phrase('enrol_course'); ?>">
                                                <i class="mdi mdi-school mr-1"></i><?php echo get_phrase('enrol'); ?>
                                            </button>
                                            <?php if (count($enrolled_list) > 0): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info btn-rounded btn-edit-enrol" 
                                                        data-user-id="<?php echo $user_id; ?>" 
                                                        data-name="<?php echo htmlspecialchars($full_name); ?>" 
                                                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                        data-role="<?php echo htmlspecialchars(!empty($user['is_instructor']) ? get_phrase('instructor') : (!empty($user['store_role_title']) ? $user['store_role_title'] : get_phrase('pharmacist'))); ?>"
                                                        data-store="<?php echo htmlspecialchars($user['store_name'] ?? get_phrase('none')); ?>"
                                                        data-enrolments='<?php echo json_encode($enrolled_list, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'
                                                        title="<?php echo get_phrase('edit_course_enrolment'); ?>">
                                                    <i class="mdi mdi-pencil mr-1"></i><?php echo get_phrase('edit'); ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
</div>

<!-- Modal: Add Course for Selected Users -->
<div class="modal fade" id="enrollSelectedUsersModal" tabindex="-1" role="dialog" aria-labelledby="enrollSelectedUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="enrollSelectedUsersModalLabel">
                    <i class="mdi mdi-school mr-1 text-success"></i> <?php echo get_phrase('add_course_for_selected_users'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form action="<?php echo site_url('admin/enrol_student/enrol'); ?>" method="post" id="form_bulk_enrol">
                <input type="hidden" name="redirect_to" value="admin/enrol_student">
                <div id="hidden_user_inputs_container"></div>

                <div class="modal-body">
                    <!-- Summary of selected users -->
                    <div class="alert alert-info py-2" role="alert">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-weight-bold">
                                <i class="mdi mdi-account-multiple-check mr-1"></i>
                                <?php echo get_phrase('selected_users'); ?>: (<span id="modal_selected_count">0</span>)
                            </span>
                            <small class="text-muted"><?php echo get_phrase('enrolment_will_be_applied_to_all_listed_users'); ?></small>
                        </div>
                        <div id="modal_selected_chips" class="d-flex flex-wrap mt-2" style="max-height: 120px; overflow-y: auto; gap: 5px;"></div>
                    </div>

                    <!-- Course Selection -->
                    <div class="form-group">
                        <label for="bulk_course_id"><?php echo get_phrase('course_to_enrol'); ?> <span class="text-danger">*</span></label>
                        <select class="select2 form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="<?php echo get_phrase('choose_course(s)...'); ?>" name="course_id[]" id="bulk_course_id" style="width: 100%;" required>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted"><?php echo get_phrase('you_can_select_multiple_courses_to_enrol_all_selected_users_at_once'); ?>.</small>
                    </div>

                    <!-- Custom Expiry Duration -->
                    <div class="form-group">
                        <label for="bulk_expiry_days"><?php echo get_phrase('access_duration_(days)'); ?> <small class="text-muted">(<?php echo get_phrase('optional'); ?>)</small></label>
                        <input type="number" min="1" class="form-control" name="expiry_days" id="bulk_expiry_days" placeholder="e.g. 30, 60, 180 (Leave blank for course default)">
                        <small class="text-muted"><?php echo get_phrase('if_left_blank,_each_selected_course\'s_configured_expiry_period_will_be_applied'); ?>.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-rounded" data-dismiss="modal"><?php echo get_phrase('cancel'); ?></button>
                    <button type="submit" class="btn btn-success btn-rounded" id="btn_submit_bulk_enrol">
                        <i class="mdi mdi-check mr-1"></i> <?php echo get_phrase('enroll_selected_users_now'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Course Enrolment for a User -->
<div class="modal fade" id="editEnrolmentModal" tabindex="-1" role="dialog" aria-labelledby="editEnrolmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h4 class="modal-title font-weight-bold" id="editEnrolmentModalLabel">
                    <i class="mdi mdi-square-edit-outline mr-1 text-info"></i> <?php echo get_phrase('edit_course_enrolment'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form action="<?php echo site_url('admin/enrol_student/update'); ?>" method="post" id="form_edit_enrol">
                <input type="hidden" name="redirect_to" value="admin/enrol_student">
                <input type="hidden" name="user_id" id="edit_enrol_user_id" value="">

                <div class="modal-body p-3">
                    <!-- User Profile Banner -->
                    <div class="card border mb-3 shadow-none bg-light">
                        <div class="card-body py-2 px-3 d-flex flex-wrap justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 font-weight-bold text-dark" id="edit_user_name_display"></h5>
                                <div class="text-muted font-13" id="edit_user_email_display"></div>
                            </div>
                            <div class="mt-1 mt-sm-0">
                                <span class="badge badge-primary-lighten mr-1" id="edit_user_role_display"></span>
                                <span class="badge badge-info-lighten" id="edit_user_store_display"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Course to Enrol (Select2 Multi-select) -->
                    <div class="form-group mb-3">
                        <label for="edit_course_id" class="font-weight-bold">
                            <?php echo get_phrase('course_to_enrol'); ?> <span class="text-danger">*</span>
                        </label>
                        <select class="select2 form-control select2-multiple" data-toggle="select2" multiple="multiple" 
                                data-placeholder="<?php echo get_phrase('choose_course(s)...'); ?>" 
                                name="course_id[]" id="edit_course_id" style="width: 100%;">
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted d-block mt-1">
                            <i class="mdi mdi-information-outline mr-1"></i><?php echo get_phrase('add_or_remove_courses_for_this_pharmacist._deselected_courses_will_be_unenrolled'); ?>.
                        </small>
                    </div>

                    <!-- Access Duration (Days) & Expiry Date -->
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group mb-2">
                                <label for="edit_expiry_days" class="font-weight-bold">
                                    <?php echo get_phrase('access_duration_(days)'); ?>
                                    <small class="text-muted font-weight-normal">(<?php echo get_phrase('optional'); ?>)</small>
                                </label>
                                <input type="number" min="0" class="form-control" name="expiry_days" id="edit_expiry_days" 
                                       placeholder="<?php echo get_phrase('enter_days_or_0_for_lifetime_(leave_blank_to_keep_current)'); ?>">
                            </div>
                            <!-- Preset duration buttons -->
                            <div class="d-flex flex-wrap align-items-center mb-2" style="gap: 5px;">
                                <small class="text-muted mr-1 font-weight-bold"><?php echo get_phrase('presets'); ?>:</small>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-duration-preset" data-days="30">30d</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-duration-preset" data-days="60">60d</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-duration-preset" data-days="90">90d</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-duration-preset" data-days="180">180d</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-duration-preset" data-days="365">365d (1yr)</button>
                                <button type="button" class="btn btn-xs btn-outline-success btn-duration-preset" data-days="0">Lifetime</button>
                                <button type="button" class="btn btn-xs btn-outline-danger btn-duration-preset" data-days="">Clear</button>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label for="edit_expiry_date" class="font-weight-bold">
                                    <?php echo get_phrase('or_specific_expiry_date'); ?>
                                    <small class="text-muted font-weight-normal">(<?php echo get_phrase('optional'); ?>)</small>
                                </label>
                                <input type="date" class="form-control" name="expiry_date" id="edit_expiry_date">
                            </div>
                            <small class="text-muted d-block font-12">
                                <?php echo get_phrase('leave_both_blank_to_keep_existing_expiry_dates_unchanged'); ?>.
                            </small>
                        </div>
                    </div>

                    <!-- Enrolment Breakdown -->
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold mb-0 text-dark">
                                <i class="mdi mdi-book-multiple mr-1 text-primary"></i><?php echo get_phrase('currently_enrolled_courses'); ?> 
                                (<span id="edit_current_courses_count">0</span>)
                            </h6>
                            <small class="text-muted"><?php echo get_phrase('live_status_of_active_enrolments'); ?></small>
                        </div>
                        <div class="table-responsive border rounded" style="max-height: 220px; overflow-y: auto;">
                            <table class="table table-sm table-striped mb-0 font-13" id="edit_enrolments_breakdown_table">
                                <thead class="thead-light">
                                    <tr>
                                        <th><?php echo get_phrase('course_title'); ?></th>
                                        <th><?php echo get_phrase('enrolled_on'); ?></th>
                                        <th><?php echo get_phrase('expiry_date'); ?></th>
                                        <th><?php echo get_phrase('status'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Populated dynamically via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-rounded" data-dismiss="modal"><?php echo get_phrase('cancel'); ?></button>
                    <button type="submit" class="btn btn-info btn-rounded font-weight-bold" id="btn_submit_edit_enrol">
                        <i class="mdi mdi-check-circle mr-1"></i> <?php echo get_phrase('save_changes'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Map to hold selected user info: key=userId, value={name, email}
    var selectedUsers = new Map();

    // Initialize DataTable on the users enrolment table
    var table = $('#users_enrolment_datatable').DataTable({
        keys: true,
        order: [], // no default column sort to maintain server ordering
        columnDefs: [
            { orderable: false, targets: [0, 2, 8, 10] } // Checkbox, Photo, Enrolled Courses, Action not orderable
        ],
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            // Re-sync visual checkbox state from selectedUsers
            $('#users_enrolment_datatable tbody input.user-checkbox').each(function() {
                var userId = String($(this).val());
                this.checked = selectedUsers.has(userId);
            });
            updateCheckAllStatus();
        }
    });

    // Helper: update Check All checkbox based on currently visible checkboxes
    function updateCheckAllStatus() {
        var visibleCheckboxes = $('#users_enrolment_datatable tbody input.user-checkbox');
        if (visibleCheckboxes.length > 0) {
            var allChecked = true;
            visibleCheckboxes.each(function() {
                if (!this.checked) {
                    allChecked = false;
                    return false;
                }
            });
            $('input#check_all_users').prop('checked', allChecked);
        } else {
            $('input#check_all_users').prop('checked', false);
        }
    }

    // Helper: update UI buttons and counter badges
    function updateSelectionUI() {
        var count = selectedUsers.size;
        $('#selected_users_count').text(count);
        $('#modal_selected_count').text(count);

        if (count > 0) {
            $('#selection_badge').show();
            $('#btn_deselect_all').show();
            $('#btn_open_enroll_modal')
                .prop('disabled', false)
                .removeClass('btn-secondary')
                .addClass('btn-success');
        } else {
            $('#selection_badge').hide();
            $('#btn_deselect_all').hide();
            $('#btn_open_enroll_modal')
                .prop('disabled', true)
                .removeClass('btn-success')
                .addClass('btn-secondary');
        }
    }

    // Select / deselect only the checkboxes currently showing on the active page/filter
    function setShowingCheckboxes(isChecked) {
        var showingCheckboxes = $('#users_enrolment_datatable tbody input.user-checkbox');
        showingCheckboxes.each(function() {
            this.checked = isChecked;
            var userId = String($(this).val());
            var userName = $(this).data('name') || '';
            var userEmail = $(this).data('email') || '';

            if (isChecked) {
                selectedUsers.set(userId, { name: userName, email: userEmail });
            } else {
                selectedUsers.delete(userId);
            }
        });

        $('input#check_all_users').prop('checked', isChecked && showingCheckboxes.length > 0);
        updateSelectionUI();
    }

    // Deselect all users across all pages
    function deselectAllUsers() {
        selectedUsers.clear();
        $('#users_enrolment_datatable tbody input.user-checkbox').prop('checked', false);
        $('input#check_all_users').prop('checked', false);
        if (table && typeof table.$ === 'function') {
            table.$('input.user-checkbox').prop('checked', false);
        }
        updateSelectionUI();
    }

    // Header master checkbox: Change listener (delegated to document)
    $(document).on('change', '#check_all_users', function(e) {
        var isChecked = $(this).is(':checked');
        setShowingCheckboxes(isChecked);
    });

    // Clicking anywhere on the first th (containing the checkbox) also toggles
    $(document).on('click', '#users_enrolment_datatable thead th:first-child', function(e) {
        if (!$(e.target).is('#check_all_users')) {
            var masterCb = $('#check_all_users');
            var newChecked = !masterCb.prop('checked');
            masterCb.prop('checked', newChecked).trigger('change');
        }
    });

    // Row checkbox click stops propagation
    $(document).on('click', '#users_enrolment_datatable tbody input.user-checkbox', function(e) {
        e.stopPropagation();
    });

    // Row checkbox change event (delegated to document)
    $(document).on('change', '#users_enrolment_datatable tbody input.user-checkbox', function(e) {
        var userId = String($(this).val());
        var userName = $(this).data('name') || '';
        var userEmail = $(this).data('email') || '';

        if (this.checked) {
            selectedUsers.set(userId, { name: userName, email: userEmail });
        } else {
            selectedUsers.delete(userId);
        }

        updateCheckAllStatus();
        updateSelectionUI();
    });

    // Clicking anywhere in the first td (checkbox cell) also toggles the checkbox
    $(document).on('click', '#users_enrolment_datatable tbody td:first-child', function(e) {
        if (!$(e.target).is('input.user-checkbox')) {
            var cb = $(this).find('input.user-checkbox');
            if (cb.length) {
                var newChecked = !cb.prop('checked');
                cb.prop('checked', newChecked).trigger('change');
            }
        }
    });

    // Deselect All button
    $('#btn_deselect_all').on('click', function() {
        deselectAllUsers();
    });

    // Quick single enrol button per row
    $('#users_enrolment_datatable').on('click', '.btn-single-enrol', function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('name');
        var userEmail = $(this).data('email');

        // Clear and select just this user
        selectedUsers.clear();
        table.$('input.user-checkbox').prop('checked', false);

        var checkbox = table.$('input.user-checkbox[value="' + userId + '"]');
        if (checkbox.length) {
            checkbox.prop('checked', true);
        }
        selectedUsers.set(String(userId), { name: userName, email: userEmail });

        updateCheckAllStatus();
        updateSelectionUI();

        // Directly open the modal
        openEnrollModal();
    });

    // Function to populate and display the modal
    function openEnrollModal() {
        if (selectedUsers.size === 0) {
            if (typeof toastr !== 'undefined') {
                toastr.warning("<?php echo get_phrase('please_select_at_least_one_user_from_the_table'); ?>");
            } else {
                alert("Please select at least one user from the table.");
            }
            return;
        }

        // Populate chips in modal
        var $chipsContainer = $('#modal_selected_chips');
        $chipsContainer.empty();

        var $hiddenContainer = $('#hidden_user_inputs_container');
        $hiddenContainer.empty();

        selectedUsers.forEach(function(info, userId) {
            var chip = $('<div class="user-selected-chip">' +
                '<span><strong>' + $('<div>').text(info.name).html() + '</strong> (' + $('<div>').text(info.email).html() + ')</span>' +
                '<a href="javascript:void(0);" class="text-danger ml-1 remove-user-chip" data-user-id="' + userId + '"><i class="mdi mdi-close"></i></a>' +
                '</div>');
            $chipsContainer.append(chip);

            $hiddenContainer.append('<input type="hidden" name="user_id[]" value="' + userId + '">');
        });

        $('#modal_selected_count').text(selectedUsers.size);

        // Reset course select inside modal
        $('#bulk_course_id').val(null).trigger('change');
        $('#bulk_expiry_days').val('');

        $('#enrollSelectedUsersModal').modal('show');
    }

    // Open modal button click
    $('#btn_open_enroll_modal').on('click', function() {
        openEnrollModal();
    });

    // Remove user chip inside modal
    $('#modal_selected_chips').on('click', '.remove-user-chip', function() {
        var userId = String($(this).data('user-id'));
        selectedUsers.delete(userId);

        // Uncheck corresponding checkbox in datatable
        table.$('input.user-checkbox[value="' + userId + '"]').prop('checked', false);

        // Re-render modal chips
        $(this).closest('.user-selected-chip').remove();
        $('#hidden_user_inputs_container input[value="' + userId + '"]').remove();
        $('#modal_selected_count').text(selectedUsers.size);

        updateCheckAllStatus();
        updateSelectionUI();

        if (selectedUsers.size === 0) {
            $('#enrollSelectedUsersModal').modal('hide');
        }
    });

    // Validate modal form submission
    $('#form_bulk_enrol').on('submit', function(e) {
        if (selectedUsers.size === 0) {
            e.preventDefault();
            if (typeof toastr !== 'undefined') {
                toastr.error("<?php echo get_phrase('no_users_selected'); ?>");
            } else {
                alert("No users selected.");
            }
            return false;
        }

        var courses = $('#bulk_course_id').val();
        if (!courses || courses.length === 0) {
            e.preventDefault();
            if (typeof toastr !== 'undefined') {
                toastr.error("<?php echo get_phrase('please_select_at_least_one_course'); ?>");
            } else {
                alert("Please select at least one course.");
            }
            return false;
        }
    });

    // Helper: format timestamp to readable date string
    function formatEnrolDate(timestamp) {
        if (!timestamp || timestamp == 0) return '—';
        var d = new Date(parseInt(timestamp) * 1000);
        return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    // Helper: format timestamp to expiry badge/label
    function formatExpiryStatus(timestamp) {
        if (!timestamp || timestamp == 0) {
            return '<span class="badge badge-success-lighten"><i class="mdi mdi-infinity mr-1"></i><?php echo get_phrase('lifetime'); ?></span>';
        }
        var ts = parseInt(timestamp);
        var now = Math.floor(Date.now() / 1000);
        var diff = ts - now;
        var d = new Date(ts * 1000);
        var dateStr = d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

        if (diff < 0) {
            var daysAgo = Math.ceil(Math.abs(diff) / 86400);
            return '<span class="badge badge-danger-lighten">' + dateStr + ' (Expired ' + daysAgo + 'd ago)</span>';
        } else {
            var daysLeft = Math.ceil(diff / 86400);
            return '<span class="badge badge-info-lighten">' + dateStr + ' (' + daysLeft + 'd left)</span>';
        }
    }

    // Handle Edit Enrolment button click per row
    $('#users_enrolment_datatable').on('click', '.btn-edit-enrol', function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('name') || '';
        var userEmail = $(this).data('email') || '';
        var userRole = $(this).data('role') || '';
        var userStore = $(this).data('store') || '';
        var enrolments = $(this).data('enrolments') || [];

        // Set hidden user_id
        $('#edit_enrol_user_id').val(userId);

        // Populate user banner
        $('#edit_user_name_display').text(userName);
        $('#edit_user_email_display').text(userEmail);
        $('#edit_user_role_display').text(userRole);
        if (userStore && userStore !== 'None' && userStore !== '') {
            $('#edit_user_store_display').text(userStore).show();
        } else {
            $('#edit_user_store_display').hide();
        }

        // Pre-select existing courses in Select2
        var selectedCourseIds = [];
        if (Array.isArray(enrolments)) {
            selectedCourseIds = enrolments.map(function(item) {
                return String(item.course_id);
            });
        }
        $('#edit_course_id').val(selectedCourseIds).trigger('change');

        // Reset expiry inputs
        $('#edit_expiry_days').val('');
        $('#edit_expiry_date').val('');

        // Populate breakdown table
        var $tbody = $('#edit_enrolments_breakdown_table tbody');
        $tbody.empty();
        $('#edit_current_courses_count').text(enrolments.length);

        if (Array.isArray(enrolments) && enrolments.length > 0) {
            enrolments.forEach(function(item) {
                var isExpired = (item.expiry_date && parseInt(item.expiry_date) < Math.floor(Date.now() / 1000));
                var statusHtml = isExpired 
                    ? '<span class="badge badge-danger"><?php echo get_phrase('expired'); ?></span>' 
                    : '<span class="badge badge-success"><?php echo get_phrase('active'); ?></span>';
                var tr = '<tr>' +
                    '<td><strong>' + $('<div>').text(item.course_title || 'Course #' + item.course_id).html() + '</strong></td>' +
                    '<td>' + formatEnrolDate(item.date_added) + '</td>' +
                    '<td>' + formatExpiryStatus(item.expiry_date) + '</td>' +
                    '<td>' + statusHtml + '</td>' +
                    '</tr>';
                $tbody.append(tr);
            });
        } else {
            $tbody.append('<tr><td colspan="4" class="text-center text-muted py-2"><?php echo get_phrase('no_enrolled_courses'); ?></td></tr>');
        }

        // Show the Edit modal
        $('#editEnrolmentModal').modal('show');
    });

    // Ensure Select2 in Edit Modal renders with full width and proper z-index parent
    $('#editEnrolmentModal').on('shown.bs.modal', function () {
        $('#edit_course_id').select2({
            dropdownParent: $('#editEnrolmentModal'),
            width: '100%'
        });
    });

    // Duration presets click handler
    $(document).on('click', '.btn-duration-preset', function() {
        var days = $(this).data('days');
        $('#edit_expiry_days').val(days).trigger('input');
    });

    // Two-way sync: When Access Duration (Days) changes, calculate and set specific Expiry Date
    $('#edit_expiry_days').on('input change', function() {
        var val = $(this).val().trim();
        if (val === '' || isNaN(val)) {
            $('#edit_expiry_date').val('');
        } else {
            var days = parseInt(val, 10);
            if (days > 0) {
                var target = new Date();
                target.setDate(target.getDate() + days);
                var yyyy = target.getFullYear();
                var mm = String(target.getMonth() + 1).padStart(2, '0');
                var dd = String(target.getDate()).padStart(2, '0');
                $('#edit_expiry_date').val(yyyy + '-' + mm + '-' + dd);
            } else if (days === 0) {
                // Lifetime access
                $('#edit_expiry_date').val('');
            }
        }
    });

    // Two-way sync: When Specific Expiry Date changes, calculate and set Duration (Days)
    $('#edit_expiry_date').on('change', function() {
        var val = $(this).val();
        if (val) {
            var target = new Date(val + 'T00:00:00');
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            var diffTime = target.getTime() - today.getTime();
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (diffDays >= 0) {
                $('#edit_expiry_days').val(diffDays);
            }
        }
    });

    // Validate Edit Enrolment form submission
    $('#form_edit_enrol').on('submit', function(e) {
        var userId = $('#edit_enrol_user_id').val();
        if (!userId) {
            e.preventDefault();
            alert("User ID missing.");
            return false;
        }

        var courses = $('#edit_course_id').val();
        if (!courses || courses.length === 0) {
            if (!confirm("<?php echo get_phrase('you_have_deselected_all_courses._this_will_unenroll_this_pharmacist_from_all_courses._do_you_want_to_proceed?'); ?>")) {
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>
