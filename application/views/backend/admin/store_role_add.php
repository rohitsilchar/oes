<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                    <a href="<?php echo site_url('admin/store_roles'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><?php echo get_phrase('back_to_roles'); ?></a>
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
                    <h4 class="mb-3 header-title"><?php echo get_phrase('add_new_role'); ?></h4>

                    <form class="required-form" action="<?php echo site_url('admin/store_roles/add'); ?>" method="post">
                        <div class="form-group mb-3">
                            <label for="role_name"><?php echo get_phrase('role_name'); ?><span class="required">*</span></label>
                            <input type="text" class="form-control" id="role_name" name="role_name" required placeholder="e.g. Senior Pharmacist, Cashier, Dispenser">
                        </div>

                        <div class="form-group mb-3">
                            <label for="description"><?php echo get_phrase('description'); ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label for="status"><?php echo get_phrase('status'); ?></label>
                            <select class="form-control select2" data-toggle="select2" name="status" id="status">
                                <option value="1"><?php echo get_phrase('active'); ?></option>
                                <option value="0"><?php echo get_phrase('inactive'); ?></option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary"><?php echo get_phrase("submit"); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
