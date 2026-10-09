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
  #server_side_users_data {
    min-width: 1200px;
    width: 100% !important;
  }
  #server_side_users_data th, 
  #server_side_users_data td {
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
</style>

<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                    <a href="<?php echo site_url('admin/user_form/add_user_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo get_phrase('add_pharmacist'); ?></a>
                    <button type="button" class="btn btn-outline-info btn-rounded alignToTitle mr-1" data-toggle="modal" data-target="#bulkImportPharmacistsModal">
                        <i class="mdi mdi-upload"></i> <?php echo get_phrase('bulk_import'); ?>
                    </button>
                    <button type="button" class="btn btn-success btn-rounded alignToTitle mr-1" id="btn_send_mail" style="display: none;" onclick="send_selected_mail()">
                        <i class="mdi mdi-email-outline mr-1"></i> <?php echo get_phrase('send_mail'); ?> (<span class="selected-count">0</span>)
                    </button>
                </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<!-- Bulk Import Pharmacists Modal -->
<div class="modal fade" id="bulkImportPharmacistsModal" tabindex="-1" role="dialog" aria-labelledby="bulkImportPharmacistsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="bulkImportPharmacistsModalLabel"><i class="mdi mdi-account-multiple-plus mr-1"></i> <?php echo get_phrase('bulk_import'); ?> - <?php echo get_phrase('pharmacists'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form action="<?php echo site_url('admin/bulk_import/pharmacists'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="mdi mdi-information-outline mr-1"></i> <strong><?php echo get_phrase('instructions'); ?>:</strong>
                                <ul class="mb-0 mt-1 pl-3">
                                    <li><?php echo get_phrase('supported_file_types'); ?> (.csv, .xlsx).</li>
                                    <li><strong>First Name</strong> (or <code>first_name</code>) is mandatory. <strong>Last Name</strong> and <strong>Email</strong> are optional.</li>
                                    <li>If <strong>Password</strong> is blank, it defaults to <code>12345678</code>.</li>
                                    <li><strong>Store Code</strong> / <strong>Pharmacy Name</strong> links pharmacist to an existing store.</li>
                                    <li>Supports client columns: <code>First Name, Last Name, Email, Password, EMP ID, Gender, State, Pharmacy Name, Phone No., Licence No., Licence Start Date, Licence End Date, Address, Store Code, Designation, Region, Status</code>.</li>
                                </ul>
                            </div>
                            <div class="ml-3">
                                <a href="<?php echo site_url('admin/download_sample_template/pharmacists'); ?>" class="btn btn-success btn-rounded text-nowrap">
                                    <i class="mdi mdi-download"></i> <?php echo get_phrase('download_sample_template'); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mb-3" style="overflow-x: auto !important;">
                        <small class="text-muted font-weight-bold"><?php echo get_phrase('template_columns_preview'); ?>:</small>
                        <table class="table table-bordered table-sm mt-1 mb-0" style="font-size: 12px; min-width: 1300px; white-space: nowrap;">
                            <thead class="thead-light">
                                <tr>
                                    <th>First Name <span class="text-danger">*</span></th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Password</th>
                                    <th>EMP ID</th>
                                    <th>Gender</th>
                                    <th>State</th>
                                    <th>Pharmacy Name</th>
                                    <th>Phone No.</th>
                                    <th>Licence No.</th>
                                    <th>Licence Start Date</th>
                                    <th>Licence End Date</th>
                                    <th>Address</th>
                                    <th>Store Code</th>
                                    <th>Designation</th>
                                    <th>Region</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>shakuntala</td>
                                    <td>pal</td>
                                    <td>shakuntala.pal@davaindia.co.in</td>
                                    <td>sIf2PmA3</td>
                                    <td>DHL04112</td>
                                    <td>Female</td>
                                    <td>Chhattisgarh</td>
                                    <td>GAR NIGAM COLONY</td>
                                    <td>9691564424</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>CCGRAI10</td>
                                    <td>M. Pharm</td>
                                    <td>West</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>Janki</td>
                                    <td>verma</td>
                                    <td>janki.verma@davaindia.co.in</td>
                                    <td>A7mWkNc8</td>
                                    <td>DHL09281</td>
                                    <td>Female</td>
                                    <td>Chhattisgarh</td>
                                    <td>Dava india tikrapara</td>
                                    <td>9131672749</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>CCGRAI11</td>
                                    <td>B. Pharm</td>
                                    <td>West</td>
                                    <td>1</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group">
                        <label for="import_file_pharmacists"><?php echo get_phrase('select_csv_or_excel_file'); ?><span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="import_file_pharmacists" name="import_file" accept=".csv, .xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv" required onchange="$(this).next('.custom-file-label').html(this.files[0].name)">
                            <label class="custom-file-label" for="import_file_pharmacists"><?php echo get_phrase('choose_file'); ?></label>
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

<?php if (!empty($_GET['store_id'])): 
    $filter_store_name = '';
    if ($_GET['store_id'] === 'no_store') {
        $filter_store_name = get_phrase('unassigned_pharmacists');
    } else {
        $store_row = $this->db->get_where('stores', ['id' => $_GET['store_id']])->row_array();
        $filter_store_name = $store_row ? $store_row['store_name'] . (!empty($store_row['store_code']) ? ' (' . $store_row['store_code'] . ')' : '') : 'Store #' . htmlspecialchars($_GET['store_id']);
    }
?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-info d-flex justify-content-between align-items-center mb-3" role="alert">
            <div>
                <i class="mdi mdi-store mr-1 font-16"></i>
                <strong><?php echo get_phrase('filtered_by_store'); ?>:</strong> <?php echo htmlspecialchars($filter_store_name); ?>
            </div>
            <a href="<?php echo site_url('admin/users'); ?>" class="btn btn-sm btn-outline-primary bg-white">
                <i class="mdi mdi-close mr-1"></i><?php echo get_phrase('show_all_pharmacists'); ?>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
  <div class="col-lg-12">
      <div class="card">
        <div class="card-body" data-collapsed="0">
          <h4 class="mb-3 header-title"><?php echo get_phrase('pharmacist'); ?></h4>
          <div class="table-responsive">
          <table class="table table-striped table-centered w-100" id="server_side_users_data">
            <thead>
              <tr>
                <th style="width: 20px;"><input type="checkbox" id="select_all"></th>
                <th>#</th>
                <th><?php echo get_phrase('photo'); ?></th>
                <th><?php echo get_phrase('name'); ?></th>
                <th><?php echo get_phrase('designation'); ?></th>
                <th><?php echo get_phrase('pharmacy_name'); ?></th>
                <th><?php echo get_phrase('state_/_region'); ?></th>
                <th><?php echo get_phrase('employee_id'); ?></th>
                <th><?php echo get_phrase('gender'); ?></th>
                <th><?php echo get_phrase('email'); ?></th>
                <th><?php echo get_phrase('Phone'); ?></th>
                <th><?php echo get_phrase('licence_no'); ?></th>
                <th><?php echo get_phrase('licence_start_date'); ?></th>
                <th><?php echo get_phrase('licence_end_date'); ?></th>
                <th><?php echo get_phrase('enrolled_courses'); ?></th>
                <th><?php echo get_phrase('email_sent'); ?></th>
                <th><?php echo get_phrase('actions'); ?></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
          </div>
      </div>
    </div>
  </div><!-- end col-->
</div>

<script>
  var selectedRows = [];

  function updateSendMailButton() {
    if (selectedRows.length > 0) {
      $('.selected-count').text(selectedRows.length);
      $('#btn_send_mail').fadeIn(150);
    } else {
      $('#btn_send_mail').fadeOut(150);
    }
  }

  function send_selected_mail() {
    if (selectedRows.length === 0) {
      if (typeof error_notify === 'function') {
        error_notify('<?php echo get_phrase('no_records_selected'); ?>');
      } else {
        alert('<?php echo get_phrase('no_records_selected'); ?>');
      }
      return;
    }

    var count = selectedRows.length;
    if (!confirm('<?php echo get_phrase('are_you_sure_you_want_to_send_registration_email_to'); ?> ' + count + ' <?php echo get_phrase('selected_users'); ?>?')) {
      return;
    }

    var $btn = $('#btn_send_mail');
    var originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="mdi mdi-spin mdi-loading mr-1"></i> <?php echo get_phrase('sending'); ?>...');

    $.ajax({
      url: "<?php echo site_url('admin/send_registration_mail'); ?>",
      type: "POST",
      dataType: "json",
      data: {
        'type': 'user',
        'ids': selectedRows,
        '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
      },
      success: function (res) {
        $btn.prop('disabled', false).html(originalHtml);
        if (res.status === 'success') {
          if (typeof success_notify === 'function') {
            success_notify(res.message);
          } else {
            alert(res.message);
          }
          selectedRows = [];
          $('#select_all').prop('checked', false);
          updateSendMailButton();
          $('#server_side_users_data').DataTable().ajax.reload(null, false);
        } else {
          if (typeof error_notify === 'function') {
            error_notify(res.message || 'Error occurred');
          } else {
            alert(res.message || 'Error occurred');
          }
        }
      },
      error: function () {
        $btn.prop('disabled', false).html(originalHtml);
        if (typeof error_notify === 'function') {
          error_notify('<?php echo get_phrase('an_error_occurred'); ?>');
        } else {
          alert('<?php echo get_phrase('an_error_occurred'); ?>');
        }
      }
    });
  }

  function sendRegistrationMailSingle(id, type) {
    if (!confirm('<?php echo get_phrase('are_you_sure_you_want_to_send_registration_email'); ?>?')) {
      return;
    }
    $.ajax({
      url: "<?php echo site_url('admin/send_registration_mail'); ?>",
      type: "POST",
      dataType: "json",
      data: {
        'type': type || 'user',
        'ids': [id],
        '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
      },
      success: function (res) {
        if (res.status === 'success') {
          if (typeof success_notify === 'function') {
            success_notify(res.message);
          } else {
            alert(res.message);
          }
          $('#server_side_users_data').DataTable().ajax.reload(null, false);
        } else {
          if (typeof error_notify === 'function') {
            error_notify(res.message || 'Error occurred');
          } else {
            alert(res.message || 'Error occurred');
          }
        }
      },
      error: function () {
        if (typeof error_notify === 'function') {
          error_notify('<?php echo get_phrase('an_error_occurred'); ?>');
        } else {
          alert('<?php echo get_phrase('an_error_occurred'); ?>');
        }
      }
    });
  }

  $(document).ready(function () {
     var table = $('#server_side_users_data').DataTable({
      responsive: false,
      "processing": true,
      "serverSide": true,
      "ajax":{
        "url": "<?php echo base_url('admin/server_side_users_data') ?>",
        "dataType": "json",
        "type": "POST",
        "data": function(d) {
            d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
            <?php if (!empty($_GET['store_id'])): ?>
            d['store_id'] = '<?php echo htmlspecialchars($_GET['store_id']); ?>';
            <?php endif; ?>
        }
      },
      "order": [[1, "desc"]],
      "columns": [
        { "data": "checkbox", "orderable": false },
        { "data": "key" },
        { "data": "photo", "orderable": false },
        { "data": "name" },
        { "data": "designation" },
        { "data": "pharmacy_name" },
        { "data": "state_region" },
        { "data": "employee_id" },
        { "data": "gender" },
        { "data": "email" },
        { "data": "phone" },
        { "data": "licence_no" },
        { "data": "licence_start_date" },
        { "data": "licence_end_date" },
        { "data": "enrolled_courses", "orderable": false },
        { "data": "email_sent" },
        { "data": "action", "orderable": false }
      ],
       dom: 'Blfrtip', // This positions the buttons
       lengthMenu: [ [10, 25, 50, 100, 250, 500], [10, 25, 50, 100, 250, 500] ],
      buttons: [
        {
            extend: 'csv',
            text: 'Export as CSV',
            filename: function () {
                var currentTime = new Date().toISOString().slice(0, 19).replace(/[-T:]/g, '_');  // Get current time and format it
                return 'pharmacists-' + currentTime;  // File name will be "users-YYYY_MM_DD_HH_MM_SS"
            },
            exportOptions: {
              columns: ':not(:first-child):not(:last-child):not(:nth-child(3))'
            }
        }
      ] 
    });

    // Select all click event
    $('#select_all').on('click', function () {
      var isChecked = $(this).is(':checked');
      $('input.user-checkbox', table.rows().nodes()).each(function () {
        var rowId = $(this).val();
        if (rowId) {
          $(this).prop('checked', isChecked);
          var idx = selectedRows.indexOf(rowId);
          if (isChecked && idx === -1) {
            selectedRows.push(rowId);
          } else if (!isChecked && idx !== -1) {
            selectedRows.splice(idx, 1);
          }
        }
      });
      updateSendMailButton();
    });

    // Individual checkbox click
    $('#server_side_users_data').on('change', 'input.user-checkbox', function () {
      var rowId = $(this).val();
      if (rowId) {
        var idx = selectedRows.indexOf(rowId);
        if ($(this).is(':checked')) {
          if (idx === -1) selectedRows.push(rowId);
        } else {
          if (idx !== -1) selectedRows.splice(idx, 1);
        }
      }
      var allChecked = true;
      var countVisible = 0;
      $('input.user-checkbox', table.rows().nodes()).each(function () {
        countVisible++;
        if (!$(this).is(':checked')) allChecked = false;
      });
      $('#select_all').prop('checked', countVisible > 0 && allChecked);
      updateSendMailButton();
    });

    // Restore checkbox state on table draw
    table.on('draw', function () {
      var allChecked = true;
      var countVisible = 0;
      $('input.user-checkbox', table.rows().nodes()).each(function () {
        countVisible++;
        var rowId = $(this).val();
        var isChecked = selectedRows.indexOf(rowId) !== -1;
        $(this).prop('checked', isChecked);
        if (!isChecked) allChecked = false;
      });
      $('#select_all').prop('checked', countVisible > 0 && allChecked);
      updateSendMailButton();
    });
   });

  function refreshServersideTable(tableId){
    $('#'+tableId).DataTable().ajax.reload();
  }
</script>