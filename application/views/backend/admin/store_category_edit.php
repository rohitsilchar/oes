<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                    <a href="<?php echo site_url('admin/store_categories'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
                        <i class="mdi mdi-arrow-left"></i> <?php echo get_phrase('back_to_categories'); ?>
                    </a>
                </h4>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <div class="col-lg-12">
                    <h4 class="mb-3 header-title"><?php echo get_phrase('edit_store_category'); ?></h4>

                    <form class="required-form" action="<?php echo site_url('admin/store_categories/edit/' . $category_data['id']); ?>" method="post">
                        <div class="form-group mb-3">
                            <label for="category_name"><?php echo get_phrase('category_name'); ?><span class="required text-danger">*</span></label>
                            <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category_data['category_name']); ?>" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="code"><?php echo get_phrase('code'); ?> <small class="text-muted">(<?php echo get_phrase('optional'); ?>)</small></label>
                            <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($category_data['code'] ?? ''); ?>" style="text-transform: uppercase;">
                        </div>

                        <div class="form-group mb-3">
                            <label for="description"><?php echo get_phrase('description'); ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($category_data['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label for="status"><?php echo get_phrase('status'); ?></label>
                            <select class="form-control select2" data-toggle="select2" name="status" id="status">
                                <option value="1" <?php if ($category_data['status'] == 1) echo 'selected'; ?>><?php echo get_phrase('active'); ?></option>
                                <option value="0" <?php if ($category_data['status'] == 0) echo 'selected'; ?>><?php echo get_phrase('inactive'); ?></option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-rounded">
                            <i class="mdi mdi-check mr-1"></i><?php echo get_phrase("update_category"); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
