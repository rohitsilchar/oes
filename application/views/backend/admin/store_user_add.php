<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                    <a href="<?php echo site_url('admin/store_users'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><?php echo get_phrase('back_to_store_users'); ?></a>
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
                    <h4 class="mb-3 header-title"><?php echo get_phrase('add_new_store_user'); ?></h4>

                    <form class="required-form" action="<?php echo site_url('admin/store_users/add'); ?>" method="post">
                        
                        <div class="form-group mb-3">
                            <label for="store_id"><?php echo get_phrase('store'); ?><span class="required">*</span></label>
                            <select class="form-control select2" data-toggle="select2" name="store_id" id="store_id" required onchange="loadStoreRoles(this.value)">
                                <option value=""><?php echo get_phrase('select_a_store'); ?></option>
                                <?php foreach ($stores as $store): ?>
                                    <option value="<?php echo $store['id']; ?>" data-portal="<?php echo htmlspecialchars($store['portal_url'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($store['store_name']); ?> <?php echo !empty($store['store_code']) ? '('.htmlspecialchars($store['store_code']).')' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="role_id"><?php echo get_phrase('role'); ?><span class="text-danger">*</span></label>
                            <select class="form-control" name="role_id" id="role_id" required onchange="updateRoleTitle(this)">
                                <option value=""><?php echo get_phrase('select_store_first'); ?></option>
                            </select>
                            <div id="no_role_warning" class="alert alert-danger py-1 px-2 font-12 mt-1" style="display: none;">
                                <i class="mdi mdi-alert-circle mr-1"></i> <?php echo get_phrase('this_store_has_no_roles_assigned._please_assign_roles_to_this_store_first.'); ?>
                                <a href="<?php echo site_url('admin/stores'); ?>" target="_blank" class="ml-1 text-danger font-weight-bold text-underline"><?php echo get_phrase('manage_stores'); ?></a>
                            </div>
                            <input type="hidden" name="role_title" id="role_title" value="">
                            <small class="text-muted"><?php echo get_phrase('Shows only the roles assigned to the selected store.'); ?></small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="pharmacist_id"><?php echo get_phrase('linked_pharmacist'); ?></label>
                            <select class="form-control select2" data-toggle="select2" name="pharmacist_id" id="pharmacist_id" onchange="autoFillPharmacistDetails(this)">
                                <option value=""><?php echo get_phrase('select_pharmacist_optional'); ?></option>
                                <?php foreach ($pharmacists as $ph): ?>
                                    <option value="<?php echo $ph['id']; ?>" data-email="<?php echo htmlspecialchars($ph['email']); ?>" data-designation="<?php echo htmlspecialchars($ph['designation'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($ph['first_name'] . ' ' . $ph['last_name'] . ' (' . $ph['email'] . (!empty($ph['employee_id']) ? ' | EMP: ' . $ph['employee_id'] : '') . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="designation"><?php echo get_phrase('designation'); ?></label>
                            <input type="text" class="form-control" id="designation" name="designation" placeholder="e.g. M. Pharm, B. Pharm, Pharmacist">
                        </div>

                        <div class="form-group mb-3">
                            <label for="username"><?php echo get_phrase('username'); ?><span class="required">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required placeholder="e.g. john.doe">
                        </div>

                        <div class="form-group mb-3">
                            <label for="password"><?php echo get_phrase('password'); ?><span class="required">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="password" name="password" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" onclick="generateAutoPassword()"><i class="mdi mdi-refresh"></i> <?php echo get_phrase('auto_generate'); ?></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="portal_link"><?php echo get_phrase('portal_link'); ?></label>
                            <input type="url" class="form-control" id="portal_link" name="portal_link" readonly placeholder="<?php echo get_phrase('auto_filled_from_selected_store'); ?>" style="background-color: #f2f3f7; cursor: not-allowed;">
                            <small class="text-muted"><?php echo get_phrase('Portal link is automatically fetched from the selected store and is not editable.'); ?></small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="notes"><?php echo get_phrase('notes'); ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
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

<script>
function loadStoreRoles(storeId) {
    var roleSelect = $('#role_id');
    var portalInput = $('#portal_link');

    if (!storeId) {
        roleSelect.html('<option value=""><?php echo get_phrase("select_store_first"); ?></option>');
        $('#role_title').val('');
        portalInput.val('');
        $('#no_role_warning').hide();
        return;
    }

    // Auto-fill portal link from selected store option data attribute
    var selectedOption = $('#store_id option:selected');
    var defaultPortal = selectedOption.data('portal') || '';
    portalInput.val(defaultPortal);

    $.ajax({
        url: '<?php echo site_url("admin/get_store_roles/"); ?>' + storeId,
        type: 'GET',
        dataType: 'json',
        success: function(roles) {
            var options = '<option value=""><?php echo get_phrase("select_a_role"); ?></option>';
            if (roles && roles.length > 0) {
                $.each(roles, function(i, r) {
                    options += '<option value="' + r.id + '" data-name="' + r.role_name + '">' + r.role_name + '</option>';
                });
                roleSelect.html(options);
                $('#no_role_warning').hide();
            } else {
                options = '<option value=""><?php echo get_phrase("no_roles_assigned_to_this_store"); ?></option>';
                roleSelect.html(options);
                $('#no_role_warning').show();
            }
            $('#role_title').val('');
        },
        error: function() {
            roleSelect.html('<option value=""><?php echo get_phrase("failed_to_load_roles"); ?></option>');
            $('#no_role_warning').hide();
        }
    });
}

function updateRoleTitle(selectEl) {
    var selected = $(selectEl).find('option:selected');
    var title = selected.data('name') || selected.text();
    if ($(selectEl).val()) {
        $('#role_title').val(title);
        $(selectEl).removeClass('is-invalid');
    } else {
        $('#role_title').val('');
    }
}

function autoFillPharmacistDetails(selectEl) {
    var selected = $(selectEl).find('option:selected');
    var email = selected.data('email');
    if (email && !$('#username').val()) {
        var username = email.split('@')[0];
        $('#username').val(username);
    }
    var designation = selected.data('designation');
    if (designation && !$('#designation').val()) {
        $('#designation').val(designation);
    }
}

function generateAutoPassword() {
    var chars = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%";
    var pass = "";
    for (var i = 0; i < 10; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    $('#password').val(pass);
}

$(document).ready(function() {
    if (!$('#password').val()) {
        generateAutoPassword();
    }
    var initialStoreId = $('#store_id').val();
    if (initialStoreId) {
        var portal = $('#store_id option:selected').data('portal') || '';
        $('#portal_link').val(portal);
        loadStoreRoles(initialStoreId);
    }
    $('#store_id').on('change', function() {
        var portal = $(this).find('option:selected').data('portal') || '';
        $('#portal_link').val(portal);
        $(this).removeClass('is-invalid');
    });

    $('form.required-form').on('submit', function(e) {
        var storeId = $('#store_id').val();
        var roleId = $('#role_id').val();

        if (!storeId) {
            e.preventDefault();
            if (typeof toastr !== 'undefined') {
                toastr.error('<?php echo get_phrase('please_select_a_store'); ?>');
            } else {
                alert('<?php echo get_phrase('please_select_a_store'); ?>');
            }
            $('#store_id').focus();
            return false;
        }

        if (!roleId || roleId === '' || roleId === '0') {
            e.preventDefault();
            $('#role_id').addClass('is-invalid');
            if (typeof toastr !== 'undefined') {
                toastr.error('<?php echo get_phrase('role_is_mandatory_please_select_a_role'); ?>');
            } else {
                alert('<?php echo get_phrase('role_is_mandatory_please_select_a_role'); ?>');
            }
            $('#role_id').focus();
            return false;
        }

        $('#role_id').removeClass('is-invalid');

        if (!$('#portal_link').val()) {
            var portal = $('#store_id option:selected').data('portal') || '';
            $('#portal_link').val(portal);
        }
    });
});
</script>
