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
    min-width: 1300px;
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
                    <a href="<?php echo site_url('admin/store_form/add_store_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
                        <i class="mdi mdi-plus"></i><?php echo get_phrase('add_new_store'); ?>
                    </a>
                    <button type="button" class="btn btn-outline-info btn-rounded alignToTitle mr-1" data-toggle="modal" data-target="#bulkImportStoresModal">
                        <i class="mdi mdi-upload"></i> <?php echo get_phrase('bulk_import'); ?>
                    </button>
                    <a href="<?php echo site_url('admin/store_categories'); ?>" class="btn btn-outline-success btn-rounded alignToTitle mr-1">
                        <i class="mdi mdi-shape-plus"></i> <?php echo get_phrase('store_categories'); ?>
                    </a>
                </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<!-- Bulk Import Stores Modal -->
<div class="modal fade" id="bulkImportStoresModal" tabindex="-1" role="dialog" aria-labelledby="bulkImportStoresModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="bulkImportStoresModalLabel"><i class="mdi mdi-store mr-1"></i> <?php echo get_phrase('bulk_import'); ?> - <?php echo get_phrase('stores'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form action="<?php echo site_url('admin/bulk_import/stores'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="mdi mdi-information-outline mr-1"></i> <strong><?php echo get_phrase('instructions'); ?>:</strong>
                                <ul class="mb-0 mt-1 pl-3">
                                    <li><?php echo get_phrase('supported_file_types'); ?> (.csv, .xlsx).</li>
                                    <li><strong>Store Name</strong> (or <code>store_name</code>) is mandatory.</li>
                                    <li><strong>Store Code</strong> (or <code>store_code</code>) is used to identify/update stores uniquely.</li>
                                    <li><strong>Store Category</strong> (or <code>store_category</code>, e.g. <code>DAVAINDIA COCO</code>) will automatically link to or create the category in Store Categories master.</li>
                                    <li>Supports client columns: <code>Store Code, Email, State, Zone, Store Category, Mobile, Store Name, Store Contact Person, Store Live Date, Address</code>.</li>
                                </ul>
                            </div>
                            <div class="ml-3">
                                <a href="<?php echo site_url('admin/download_sample_template/stores'); ?>" class="btn btn-success btn-rounded text-nowrap">
                                    <i class="mdi mdi-download"></i> <?php echo get_phrase('download_sample_template'); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mb-3" style="overflow-x: auto !important;">
                        <small class="text-muted font-weight-bold"><?php echo get_phrase('template_columns_preview'); ?>:</small>
                        <table class="table table-bordered table-sm mt-1 mb-0" style="font-size: 12px; min-width: 1100px; white-space: nowrap;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Store Code</th>
                                    <th>Email</th>
                                    <th>State</th>
                                    <th>Zone</th>
                                    <th>Store Category</th>
                                    <th>Mobile</th>
                                    <th>Store Name <span class="text-danger">*</span></th>
                                    <th>Store Contact Person</th>
                                    <th>Store Live Date</th>
                                    <th>Address</th>
                                    <th>City</th>
                                    <th>Pin Code</th>
                                    <th>Portal URL</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>CASBAK1750</td>
                                    <td>howlyroad1750@davaindia.co.in</td>
                                    <td>ASSAM</td>
                                    <td>EAST</td>
                                    <td>DAVAINDIA COCO</td>
                                    <td>6358818453</td>
                                    <td>PATHSALA</td>
                                    <td>SHAHINUR ISLAM</td>
                                    <td>19-02-2025</td>
                                    <td>LY ROAD, PO- PATHSALA, PS- PATHSALA, Dist. BAJALI</td>
                                    <td>Barpeta</td>
                                    <td>781325</td>
                                    <td>https://store1.domain.com/login</td>
                                    <td>1</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group">
                        <label for="import_file_stores"><?php echo get_phrase('select_csv_or_excel_file'); ?><span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="import_file_stores" name="import_file" accept=".csv, .xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv" required onchange="$(this).next('.custom-file-label').html(this.files[0].name)">
                            <label class="custom-file-label" for="import_file_stores"><?php echo get_phrase('choose_file'); ?></label>
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
                <h4 class="mb-3 header-title"><?php echo get_phrase('stores'); ?></h4>
                <div class="table-responsive mt-4">
                    <table id="basic-datatable" class="table table-striped table-centered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo get_phrase('store_code'); ?></th>
                                <th><?php echo get_phrase('store_name'); ?></th>
                                <th><?php echo get_phrase('category'); ?></th>
                                <th><?php echo get_phrase('zone_/_state'); ?></th>
                                <th><?php echo get_phrase('contact_person'); ?></th>
                                <th><?php echo get_phrase('mobile_/_email'); ?></th>
                                <th><?php echo get_phrase('live_date'); ?></th>
                                <th><?php echo get_phrase('portal_url'); ?></th>
                                <th><?php echo get_phrase('assigned_roles'); ?></th>
                                <th><?php echo get_phrase('status'); ?></th>
                                <th><?php echo get_phrase('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stores as $key => $store) : 
                                $assigned_role_ids = json_decode($store['assigned_role_ids'] ?: '[]', true);
                                $cat_display = !empty($store['store_category']) ? $store['store_category'] : (!empty($categories_map[$store['category_id'] ?? 0]) ? $categories_map[$store['category_id']] : '');
                                $phone_display = !empty($store['mobile']) ? $store['mobile'] : (!empty($store['phone']) ? $store['phone'] : '');
                            ?>
                                <tr>
                                    <td><?php echo $key + 1; ?></td>
                                    <td>
                                        <span class="badge badge-dark"><?php echo htmlspecialchars($store['store_code'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($store['store_name']); ?></strong></td>
                                    <td>
                                        <?php if (!empty($cat_display)): ?>
                                            <span class="badge badge-info-lighten px-2 py-1"><i class="mdi mdi-tag-outline mr-1"></i><?php echo htmlspecialchars($cat_display); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($store['zone'])): ?>
                                            <span class="badge badge-primary-lighten mr-1"><?php echo htmlspecialchars($store['zone']); ?></span>
                                        <?php endif; ?>
                                        <span class="text-dark"><?php echo htmlspecialchars($store['state'] ?? ''); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($store['contact_person'])): ?>
                                            <div><i class="mdi mdi-account text-muted mr-1"></i><strong><?php echo htmlspecialchars($store['contact_person']); ?></strong></div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($phone_display)): ?>
                                            <div><i class="mdi mdi-phone text-muted mr-1"></i><?php echo htmlspecialchars($phone_display); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($store['email'])): ?>
                                            <div><i class="mdi mdi-email text-muted mr-1"></i><?php echo htmlspecialchars($store['email']); ?></div>
                                        <?php endif; ?>
                                        <?php if (empty($phone_display) && empty($store['email'])): ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($store['live_date'])): ?>
                                            <span class="badge badge-light"><i class="mdi mdi-calendar mr-1"></i><?php echo htmlspecialchars($store['live_date']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($store['portal_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($store['portal_url']); ?>" target="_blank" class="text-info"><i class="mdi mdi-open-in-new mr-1"></i>Link</a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($assigned_role_ids)) {
                                            foreach ($assigned_role_ids as $r_id) {
                                                if (isset($roles_map[$r_id])) {
                                                    echo '<span class="badge badge-secondary mr-1 mb-1">' . htmlspecialchars($roles_map[$r_id]) . '</span>';
                                                }
                                            }
                                        } else {
                                            echo '<span class="text-muted">' . get_phrase('none') . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($store['status'] == 1) : ?>
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
                                                <li><a class="dropdown-item" href="<?php echo site_url('admin/store_form/edit_store_form/' . $store['id']); ?>"><i class="mdi mdi-pencil mr-1"></i><?php echo get_phrase('edit'); ?></a></li>
                                                <li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/stores/delete/' . $store['id']); ?>');"><i class="mdi mdi-delete mr-1 text-danger"></i><?php echo get_phrase('delete'); ?></a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>
