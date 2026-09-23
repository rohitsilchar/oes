<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                    <a href="<?php echo site_url('admin/instructor_form/add_instructor_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo get_phrase('add_instructor'); ?></a>
                    <button type="button" class="btn btn-outline-info btn-rounded alignToTitle mr-1" data-toggle="modal" data-target="#bulkImportInstructorsModal">
                        <i class="mdi mdi-upload"></i> <?php echo get_phrase('bulk_import'); ?>
                    </button>
                </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<!-- Bulk Import Instructors Modal -->
<div class="modal fade" id="bulkImportInstructorsModal" tabindex="-1" role="dialog" aria-labelledby="bulkImportInstructorsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="bulkImportInstructorsModalLabel"><i class="mdi mdi-account-tie mr-1"></i> <?php echo get_phrase('bulk_import'); ?> - <?php echo get_phrase('instructors'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form action="<?php echo site_url('admin/bulk_import/instructors'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="mdi mdi-information-outline mr-1"></i> <strong><?php echo get_phrase('instructions'); ?>:</strong>
                                <ul class="mb-0 mt-1 pl-3">
                                    <li><?php echo get_phrase('supported_file_types'); ?>.</li>
                                    <li><strong>first_name</strong>, <strong>last_name</strong>, and <strong>email</strong> are mandatory.</li>
                                    <li>If <strong>password</strong> is blank, it defaults to <code>12345678</code>.</li>
                                    <li>If the email matches an existing user, they will be promoted to instructor status.</li>
                                </ul>
                            </div>
                            <div class="ml-3">
                                <a href="<?php echo site_url('admin/download_sample_template/instructors'); ?>" class="btn btn-success btn-rounded text-nowrap">
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
                                    <th>first_name <span class="text-danger">*</span></th>
                                    <th>last_name <span class="text-danger">*</span></th>
                                    <th>email <span class="text-danger">*</span></th>
                                    <th>password</th>
                                    <th>phone</th>
                                    <th>address</th>
                                    <th>biography</th>
                                    <th>status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Robert</td>
                                    <td>Fox</td>
                                    <td>robert.fox@example.com</td>
                                    <td>Pass@1234</td>
                                    <td>9876543210</td>
                                    <td>123 Academic Way, Boston</td>
                                    <td>Specialist in Pharmacology with 10+ years experience</td>
                                    <td>1</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group">
                        <label for="import_file_instructors"><?php echo get_phrase('select_csv_or_excel_file'); ?><span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="import_file_instructors" name="import_file" accept=".csv, .xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv" required onchange="$(this).next('.custom-file-label').html(this.files[0].name)">
                            <label class="custom-file-label" for="import_file_instructors"><?php echo get_phrase('choose_file'); ?></label>
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
  <div class="col-lg-12">
      <div class="card">
        <div class="card-body" data-collapsed="0">
          <h4 class="mb-3 header-title"><?php echo get_phrase('instructor'); ?></h4>
          <table class="table table-striped table-centered w-100" id="server_side_users_data">
            <thead>
              <tr>
                <th>#</th>
                <th><?php echo get_phrase('photo'); ?></th>
                <th><?php echo get_phrase('name'); ?></th>
                <th><?php echo get_phrase('email'); ?></th>
                <th><?php echo get_phrase('Phone'); ?></th>
                <th><?php echo get_phrase('enrolled_courses'); ?></th>
                <th><?php echo get_phrase('actions'); ?></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
      </div>
    </div>
  </div><!-- end col-->
</div>

<script>
  $(document).ready(function () {
     var table = $('#server_side_users_data').DataTable({
      responsive: true,
      "processing": true,
      "serverSide": true,
      "ajax":{
        "url": "<?php echo base_url('admin/server_side_instructors_data') ?>",
        "dataType": "json",
        "type": "POST",
        "data":{  '<?php echo $this->security->get_csrf_token_name(); ?>' : '<?php echo $this->security->get_csrf_hash(); ?>' }
      },
      "columns": [
        { "data": "key" },
        { "data": "photo" },
        { "data": "name" },
        { "data": "email" },
        { "data": "phone" },
        { "data": "enrolled_courses" },
        { "data": "action" }
      ],
      dom: 'Bfrtip',  // This positions the buttons
      buttons: [
        {
            extend: 'csv',
            text: 'Export as CSV',
            filename: function () {
                var currentTime = new Date().toISOString().slice(0, 19).replace(/[-T:]/g, '_');  // Get current time and format it
                return 'instructors-' + currentTime;  // File name will be "users-YYYY_MM_DD_HH_MM_SS"
            },
            exportOptions: {
              columns: ':not(:last-child):not(:nth-child(2))'  // Exclude the last column ("action") and the second column ("photo")
            }
        }
      ]  
    });
   });

  function refreshServersideTable(tableId){
    $('#'+tableId).DataTable().ajax.reload();
  }
</script>