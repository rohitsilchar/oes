<style>
  .table-responsive {
    display: block;
    width: 100%;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
  }
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12 {
    overflow-x: auto !important;
  }
  #basic-datatable {
    width: 100% !important;
    min-width: 1100px;
  }
  #basic-datatable th, #basic-datatable td {
    white-space: nowrap;
    vertical-align: middle;
  }
  /* Visible horizontal scrollbar styling */
  .table-responsive::-webkit-scrollbar,
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12::-webkit-scrollbar {
    height: 8px;
  }
  .table-responsive::-webkit-scrollbar-track,
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
  }
  .table-responsive::-webkit-scrollbar-thumb,
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border-radius: 4px;
  }
  .table-responsive::-webkit-scrollbar-thumb:hover,
  .dataTables_wrapper .row:nth-child(2) > div.col-sm-12::-webkit-scrollbar-thumb:hover {
    background: #6c757d;
  }
</style>

<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                    <a href="<?php echo site_url('admin/store_user_form/add_store_user_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo get_phrase('add_new_store_user'); ?></a>
                    <button type="button" class="btn btn-outline-info btn-rounded alignToTitle mr-1" data-toggle="modal" data-target="#bulkImportStoreUsersModal">
                        <i class="mdi mdi-upload"></i> <?php echo get_phrase('bulk_import'); ?>
                    </button>
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Import Store Users Modal -->
<div class="modal fade" id="bulkImportStoreUsersModal" tabindex="-1" role="dialog" aria-labelledby="bulkImportStoreUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="bulkImportStoreUsersModalLabel"><i class="mdi mdi-account-key mr-1"></i> <?php echo get_phrase('bulk_import'); ?> - <?php echo get_phrase('store_users'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form action="<?php echo site_url('admin/bulk_import/store_users'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="mdi mdi-information-outline mr-1"></i> <strong><?php echo get_phrase('instructions'); ?>:</strong>
                                <ul class="mb-0 mt-1 pl-3">
                                    <li><?php echo get_phrase('supported_file_types'); ?>.</li>
                                    <li><strong>store_code</strong> and <strong>role_name</strong> are mandatory.</li>
                                    <li><strong>username</strong> is mandatory (must be unique per store).</li>
                                    <li><strong>pharmacist_email</strong> is optional to link an existing pharmacist.</li>
                                    <li>If <strong>password</strong> is empty, a secure password is auto-generated.</li>
                                    <li>If <strong>portal_link</strong> is empty, it automatically pulls the portal URL from the store.</li>
                                </ul>
                            </div>
                            <div class="ml-3">
                                <a href="<?php echo site_url('admin/download_sample_template/store_users'); ?>" class="btn btn-success btn-rounded text-nowrap">
                                    <i class="mdi mdi-download"></i> <?php echo get_phrase('download_sample_template'); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mb-3" style="overflow-x: auto !important;">
                        <small class="text-muted font-weight-bold"><?php echo get_phrase('template_columns_preview'); ?>:</small>
                        <table class="table table-bordered table-sm mt-1 mb-0" style="font-size: 12px; min-width: 800px; white-space: nowrap;">
                            <thead class="thead-light">
                                <tr>
                                    <th>store_code <span class="text-danger">*</span></th>
                                    <th>role_name <span class="text-danger">*</span></th>
                                    <th>pharmacist_email</th>
                                    <th>username <span class="text-danger">*</span></th>
                                    <th>password</th>
                                    <th>portal_link</th>
                                    <th>notes</th>
                                    <th>status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>STR-001</td>
                                    <td>Senior Pharmacist</td>
                                    <td>john.doe@example.com</td>
                                    <td>john.apollo</td>
                                    <td>Pass123!</td>
                                    <td></td>
                                    <td>Assigned to Apollo downtown</td>
                                    <td>1</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group">
                        <label for="import_file_store_users"><?php echo get_phrase('select_csv_or_excel_file'); ?><span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="import_file_store_users" name="import_file" accept=".csv, .xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv" required onchange="$(this).next('.custom-file-label').html(this.files[0].name)">
                            <label class="custom-file-label" for="import_file_store_users"><?php echo get_phrase('choose_file'); ?></label>
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
                <h4 class="mb-3 header-title"><?php echo get_phrase('store_users'); ?></h4>
                <div class="table-responsive mt-4">
                    <table id="basic-datatable" class="table table-striped table-centered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo get_phrase('store'); ?></th>
                                <th><?php echo get_phrase('linked_pharmacist'); ?></th>
                                <th><?php echo get_phrase('username'); ?></th>
                                <th><?php echo get_phrase('role'); ?></th>
                                <th><?php echo get_phrase('portal_link'); ?></th>
                                <th><?php echo get_phrase('status'); ?></th>
                                <th><?php echo get_phrase('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($store_users as $key => $su) : ?>
                                <tr>
                                    <td><?php echo $key + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($su['store_name'] ?? 'N/A'); ?></strong></td>
                                    <td>
                                        <?php if (!empty($su['pharmacist_name'])): ?>
                                            <div><?php echo htmlspecialchars($su['pharmacist_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($su['pharmacist_email'] ?? ''); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted"><?php echo get_phrase('none'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($su['username']); ?></code></td>
                                    <td><span class="badge badge-primary-lighten"><?php echo htmlspecialchars($su['role_title']); ?></span></td>
                                    <td>
                                        <?php if (!empty($su['portal_link'])): ?>
                                            <a href="<?php echo htmlspecialchars($su['portal_link']); ?>" target="_blank" class="text-info"><?php echo htmlspecialchars($su['portal_link']); ?></a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($su['status'] == 1) : ?>
                                            <span class="badge badge-success"><?php echo get_phrase('active'); ?></span>
                                        <?php else : ?>
                                            <span class="badge badge-danger"><?php echo get_phrase('inactive'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="dropright dropright">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="<?php echo site_url('admin/store_user_form/edit_store_user_form/' . $su['id']); ?>"><?php echo get_phrase('edit'); ?></a></li>
                                                <li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/store_users/delete/' . $su['id']); ?>');"><?php echo get_phrase('delete'); ?></a></li>
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
