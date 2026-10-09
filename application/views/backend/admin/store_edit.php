<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                    <a href="<?php echo site_url('admin/stores'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
                        <i class="mdi mdi-arrow-left"></i> <?php echo get_phrase('back_to_stores'); ?>
                    </a>
                </h4>
            </div>
        </div>
    </div>
</div>

<?php 
$assigned_roles = json_decode($store_data['assigned_role_ids'] ?: '[]', true);
$phone_val = !empty($store_data['phone']) ? $store_data['phone'] : (!empty($store_data['mobile']) ? $store_data['mobile'] : '');
?>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card">
            <div class="card-body">
                <div class="col-lg-12">
                    <h4 class="mb-3 header-title"><?php echo get_phrase('edit_store'); ?></h4>

                    <form class="required-form" action="<?php echo site_url('admin/stores/edit/' . $store_data['id']); ?>" method="post">
                        <!-- Store Name & Code -->
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="store_name"><?php echo get_phrase('store_name'); ?><span class="required text-danger">*</span></label>
                                <input type="text" class="form-control" id="store_name" name="store_name" value="<?php echo htmlspecialchars($store_data['store_name']); ?>" required>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="store_code"><?php echo get_phrase('store_code'); ?></label>
                                <input type="text" class="form-control" id="store_code" name="store_code" value="<?php echo htmlspecialchars($store_data['store_code'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Store Category & Zone -->
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="category_id" class="mb-0 font-weight-bold"><?php echo get_phrase('store_category'); ?></label>
                                    <a href="<?php echo site_url('admin/store_category_form/add_category_form'); ?>" target="_blank" class="font-12 text-primary">
                                        <i class="mdi mdi-plus-circle-outline"></i> <?php echo get_phrase('add_category'); ?>
                                    </a>
                                </div>
                                <select class="form-control select2" data-toggle="select2" name="category_id" id="category_id">
                                    <option value=""><?php echo get_phrase('select_category'); ?>...</option>
                                    <?php if (!empty($store_categories)): ?>
                                        <?php foreach ($store_categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php if (!empty($store_data['category_id']) && $store_data['category_id'] == $cat['id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($cat['category_name']); ?> <?php echo !empty($cat['code']) ? '(' . htmlspecialchars($cat['code']) . ')' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="zone" class="font-weight-bold"><?php echo get_phrase('zone'); ?></label>
                                <input type="text" class="form-control" id="zone" name="zone" list="zone_suggestions" value="<?php echo htmlspecialchars($store_data['zone'] ?? ''); ?>" placeholder="e.g. EAST, WEST, NORTH, SOUTH" style="text-transform: uppercase;">
                                <datalist id="zone_suggestions">
                                    <option value="EAST">
                                    <option value="WEST">
                                    <option value="NORTH">
                                    <option value="SOUTH">
                                    <option value="CENTRAL">
                                    <option value="NORTH EAST">
                                </datalist>
                            </div>
                        </div>

                        <!-- Contact Person & Store Live Date -->
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="contact_person"><?php echo get_phrase('store_contact_person'); ?></label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($store_data['contact_person'] ?? ''); ?>" placeholder="e.g. SHAHINUR ISLAM">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="live_date"><?php echo get_phrase('store_live_date'); ?></label>
                                <input type="date" class="form-control" id="live_date" name="live_date" value="<?php echo htmlspecialchars($store_data['live_date'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Mobile & Email -->
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="phone"><?php echo get_phrase('mobile_/_phone'); ?></label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone_val); ?>">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="email"><?php echo get_phrase('email'); ?></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($store_data['email'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Portal URL -->
                        <div class="form-group mb-3">
                            <label for="portal_url"><?php echo get_phrase('portal_url'); ?></label>
                            <input type="url" class="form-control" id="portal_url" name="portal_url" value="<?php echo htmlspecialchars($store_data['portal_url'] ?? ''); ?>" placeholder="https://store.domain.com/login">
                        </div>

                        <!-- Assigned Roles -->
                        <div class="form-group mb-3">
                            <label for="assigned_role_ids"><?php echo get_phrase('assigned_roles'); ?><span class="required text-danger">*</span></label>
                            <select class="form-control select2" data-toggle="select2" name="assigned_role_ids[]" id="assigned_role_ids" multiple="multiple" required>
                                <?php foreach ($store_roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>" <?php if (in_array($role['id'], $assigned_roles)) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($role['role_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Address -->
                        <div class="form-group mb-3">
                            <label for="address"><?php echo get_phrase('address'); ?></label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($store_data['address'] ?? ''); ?></textarea>
                        </div>

                        <!-- City, State, Pin Code -->
                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label for="city"><?php echo get_phrase('city'); ?></label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($store_data['city'] ?? ''); ?>">
                            </div>

                            <div class="col-md-4 form-group mb-3">
                                <label for="state"><?php echo get_phrase('state'); ?></label>
                                <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($store_data['state'] ?? ''); ?>">
                            </div>

                            <div class="col-md-4 form-group mb-3">
                                <label for="pin_code"><?php echo get_phrase('pin_code'); ?></label>
                                <input type="text" class="form-control" id="pin_code" name="pin_code" value="<?php echo htmlspecialchars($store_data['pin_code'] ?? ''); ?>" placeholder="e.g. 560001">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group mb-3">
                            <label for="status"><?php echo get_phrase('status'); ?></label>
                            <select class="form-control select2" data-toggle="select2" name="status" id="status">
                                <option value="1" <?php if ($store_data['status'] == 1) echo 'selected'; ?>><?php echo get_phrase('active'); ?></option>
                                <option value="0" <?php if ($store_data['status'] == 0) echo 'selected'; ?>><?php echo get_phrase('inactive'); ?></option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-rounded">
                            <i class="mdi mdi-check mr-1"></i><?php echo get_phrase("update_store"); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
