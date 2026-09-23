<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                    <a href="<?php echo site_url('admin/stores'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><?php echo get_phrase('back_to_stores'); ?></a>
                </h4>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="col-lg-12">
                    <h4 class="mb-3 header-title"><?php echo get_phrase('add_new_store'); ?></h4>

                    <form class="required-form" action="<?php echo site_url('admin/stores/add'); ?>" method="post">
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="store_name"><?php echo get_phrase('store_name'); ?><span class="required">*</span></label>
                                <input type="text" class="form-control" id="store_name" name="store_name" required placeholder="e.g. Apollo Pharmacy - Downtown">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="store_code"><?php echo get_phrase('store_code'); ?></label>
                                <input type="text" class="form-control" id="store_code" name="store_code" placeholder="e.g. STR-001">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="phone"><?php echo get_phrase('phone'); ?></label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="email"><?php echo get_phrase('email'); ?></label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="portal_url"><?php echo get_phrase('portal_url'); ?></label>
                            <input type="url" class="form-control" id="portal_url" name="portal_url" placeholder="https://store.domain.com/login">
                            <small class="text-muted"><?php echo get_phrase('The portal or system login link associated with this store.'); ?></small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="assigned_role_ids"><?php echo get_phrase('assigned_roles'); ?><span class="required">*</span></label>
                            <select class="form-control select2" data-toggle="select2" name="assigned_role_ids[]" id="assigned_role_ids" multiple="multiple" required>
                                <?php foreach ($store_roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>">
                                        <?php echo htmlspecialchars($role['role_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted"><?php echo get_phrase('Select multiple roles allowed for this store. These roles will be available when adding store users.'); ?></small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="address"><?php echo get_phrase('address'); ?></label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label for="city"><?php echo get_phrase('city'); ?></label>
                                <input type="text" class="form-control" id="city" name="city">
                            </div>

                            <div class="col-md-4 form-group mb-3">
                                <label for="state"><?php echo get_phrase('state'); ?></label>
                                <input type="text" class="form-control" id="state" name="state">
                            </div>

                            <div class="col-md-4 form-group mb-3">
                                <label for="pin_code"><?php echo get_phrase('pin_code'); ?></label>
                                <input type="text" class="form-control" id="pin_code" name="pin_code" placeholder="e.g. 560001">
                            </div>
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
