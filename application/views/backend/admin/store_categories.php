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
  }
  #basic-datatable th, #basic-datatable td {
    white-space: nowrap;
    vertical-align: middle;
  }
</style>

<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> 
                    <i class="mdi mdi-shape-plus title_icon"></i> <?php echo $page_title; ?>
                    <a href="<?php echo site_url('admin/store_category_form/add_category_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
                        <i class="mdi mdi-plus"></i> <?php echo get_phrase('add_new_category'); ?>
                    </a>
                    <button type="button" class="btn btn-outline-info btn-rounded alignToTitle mr-1" data-toggle="modal" data-target="#bulkImportStoreCategoriesModal">
                        <i class="mdi mdi-upload"></i> <?php echo get_phrase('bulk_import'); ?>
                    </button>
                    <a href="<?php echo site_url('admin/stores'); ?>" class="btn btn-outline-secondary btn-rounded alignToTitle mr-1">
                        <i class="mdi mdi-store"></i> <?php echo get_phrase('stores_list'); ?>
                    </a>
                </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<!-- Bulk Import Store Categories Modal -->
<div class="modal fade" id="bulkImportStoreCategoriesModal" tabindex="-1" role="dialog" aria-labelledby="bulkImportStoreCategoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="bulkImportStoreCategoriesModalLabel">
                    <i class="mdi mdi-shape-plus mr-1"></i> <?php echo get_phrase('bulk_import'); ?> - <?php echo get_phrase('store_categories'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form action="<?php echo site_url('admin/bulk_import/store_categories'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="mdi mdi-information-outline mr-1"></i> <strong><?php echo get_phrase('instructions'); ?>:</strong>
                                <ul class="mb-0 mt-1 pl-3">
                                    <li><?php echo get_phrase('supported_file_types'); ?> (.csv, .xlsx).</li>
                                    <li><strong>category_name</strong> is mandatory (e.g. <code>DAVAINDIA COCO</code>, <code>DAVAINDIA FOFO</code>).</li>
                                    <li><strong>code</strong> is optional short code (e.g. <code>COCO</code>, <code>FOFO</code>).</li>
                                    <li><strong>status</strong> 1 for Active, 0 for Inactive.</li>
                                </ul>
                            </div>
                            <div class="ml-3">
                                <a href="<?php echo site_url('admin/download_sample_template/store_categories'); ?>" class="btn btn-success btn-rounded text-nowrap">
                                    <i class="mdi mdi-download"></i> <?php echo get_phrase('download_sample_template'); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="import_file_categories"><?php echo get_phrase('select_csv_or_excel_file'); ?><span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="import_file_categories" name="import_file" accept=".csv, .xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv" required onchange="$(this).next('.custom-file-label').html(this.files[0].name)">
                            <label class="custom-file-label" for="import_file_categories"><?php echo get_phrase('choose_file'); ?></label>
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
                <h4 class="mb-3 header-title"><?php echo get_phrase('store_categories_master'); ?></h4>
                <div class="table-responsive mt-3">
                    <table id="basic-datatable" class="table table-striped table-centered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo get_phrase('category_name'); ?></th>
                                <th><?php echo get_phrase('code'); ?></th>
                                <th><?php echo get_phrase('description'); ?></th>
                                <th><?php echo get_phrase('status'); ?></th>
                                <th><?php echo get_phrase('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $key => $cat) : ?>
                                <tr>
                                    <td><?php echo $key + 1; ?></td>
                                    <td>
                                        <strong><i class="mdi mdi-tag-outline text-primary mr-1"></i><?php echo htmlspecialchars($cat['category_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($cat['code'])): ?>
                                            <span class="badge badge-light"><?php echo htmlspecialchars($cat['code']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($cat['description'] ?? ''); ?></td>
                                    <td>
                                        <?php if ($cat['status'] == 1) : ?>
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
                                                <li>
                                                    <a class="dropdown-item" href="<?php echo site_url('admin/store_category_form/edit_category_form/' . $cat['id']); ?>">
                                                        <i class="mdi mdi-pencil mr-1"></i><?php echo get_phrase('edit'); ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/store_categories/delete/' . $cat['id']); ?>');">
                                                        <i class="mdi mdi-delete mr-1 text-danger"></i><?php echo get_phrase('delete'); ?>
                                                    </a>
                                                </li>
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
