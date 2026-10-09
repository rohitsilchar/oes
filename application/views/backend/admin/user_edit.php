<?php
    $user_data = $this->db->get_where('users', array('id' => $user_id))->row_array();
    $social_links = !empty($user_data['social_links']) ? json_decode($user_data['social_links'], true) : [];
    if (!is_array($social_links)) $social_links = [];
    $payment_keys = !empty($user_data['payment_keys']) ? json_decode($user_data['payment_keys'], true) : [];
    if (!is_array($payment_keys)) $payment_keys = [];
    $paypal_keys = $payment_keys['paypal'] ?? [];
    $stripe_keys = $payment_keys['stripe'] ?? [];
    $razorpay_keys = $payment_keys['razorpay'] ?? [];
    $display_password = $this->user_model->get_user_plain_password($user_id);
?>
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?> </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">

                <h4 class="header-title mb-3"><?php echo get_phrase('pharmacist_edit_form'); ?></h4>

                <form class="required-form" action="<?php echo site_url('admin/users/edit/'.$user_id); ?>" enctype="multipart/form-data" method="post">
                    <div id="progressbarwizard">
                        <ul class="nav nav-pills nav-justified form-wizard-header mb-3">
                            <li class="nav-item">
                                <a href="#basic_info" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                    <i class="mdi mdi-face-profile mr-1"></i>
                                    <span class="d-none d-sm-inline"><?php echo get_phrase('basic_info'); ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#login_credentials" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                    <i class="mdi mdi-lock mr-1"></i>
                                    <span class="d-none d-sm-inline"><?php echo get_phrase('login_credentials'); ?></span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="#finish" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                    <i class="mdi mdi-checkbox-marked-circle-outline mr-1"></i>
                                    <span class="d-none d-sm-inline"><?php echo get_phrase('finish'); ?></span>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content b-0 mb-0">

                            <div id="bar" class="progress mb-3" style="height: 7px;">
                                <div class="bar progress-bar progress-bar-striped progress-bar-animated bg-success"></div>
                            </div>

                            <div class="tab-pane" id="basic_info">
                                <div class="row">
                                    <div class="col-12">
                                        <?php include 'licence_ocr_scanner.php'; ?>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="first_name"><?php echo get_phrase('first_name'); ?> <span class="required">*</span> </label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user_data['first_name']; ?>" required>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="last_name"><?php echo get_phrase('last_name'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user_data['last_name']; ?>">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="employee_id"><?php echo get_phrase('employee_id'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="employee_id" name="employee_id" value="<?php echo htmlspecialchars($user_data['employee_id'] ?? ''); ?>" placeholder="e.g. EMP-001">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="gender"><?php echo get_phrase('gender'); ?></label>
                                            <div class="col-md-9">
                                                <select class="form-control select2" data-toggle="select2" name="gender" id="gender">
                                                    <option value=""><?php echo get_phrase('select_gender'); ?></option>
                                                    <option value="Male" <?php if (($user_data['gender'] ?? '') == 'Male') echo 'selected'; ?>><?php echo get_phrase('male'); ?></option>
                                                    <option value="Female" <?php if (($user_data['gender'] ?? '') == 'Female') echo 'selected'; ?>><?php echo get_phrase('female'); ?></option>
                                                    <option value="Other" <?php if (($user_data['gender'] ?? '') == 'Other') echo 'selected'; ?>><?php echo get_phrase('other'); ?></option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="designation"><?php echo get_phrase('designation'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="designation" name="designation" value="<?php echo htmlspecialchars($user_data['designation'] ?? ''); ?>" list="designation_suggestions" placeholder="e.g. B. Pharm, M. Pharm, Pharmacist">
                                                <datalist id="designation_suggestions">
                                                    <option value="B. Pharm">
                                                    <option value="M. Pharm">
                                                    <option value="D. Pharm">
                                                    <option value="Pharmacist">
                                                    <option value="Senior Pharmacist">
                                                    <option value="Dispenser">
                                                </datalist>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="licence_no"><?php echo get_phrase('licence_no'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="licence_no" name="licence_no" value="<?php echo htmlspecialchars($user_data['licence_no'] ?? ''); ?>" placeholder="e.g. LIC-123456">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="licence_start_date"><?php echo get_phrase('licence_start_date'); ?></label>
                                            <div class="col-md-9">
                                                <input type="date" class="form-control" id="licence_start_date" name="licence_start_date" value="<?php echo htmlspecialchars($user_data['licence_start_date'] ?? ''); ?>">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="licence_end_date"><?php echo get_phrase('licence_end_date'); ?></label>
                                            <div class="col-md-9">
                                                <input type="date" class="form-control" id="licence_end_date" name="licence_end_date" value="<?php echo htmlspecialchars($user_data['licence_end_date'] ?? ''); ?>">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="mac_address"><?php echo get_phrase('registered_mac_address'); ?></label>
                                            <div class="col-md-9">
                                                <div class="input-group">
                                                    <input type="text" class="form-control text-uppercase" id="mac_address" name="mac_address" value="<?php echo htmlspecialchars($user_data['mac_address'] ?? ''); ?>" placeholder="e.g. 9C:67:D6:81:7F:31">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-warning" type="button" onclick="$('#mac_address').val('');" title="<?php echo get_phrase('clear_mac_address'); ?>">
                                                            <i class="mdi mdi-refresh"></i> <?php echo get_phrase('clear_reset'); ?>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?php echo get_phrase('clear_and_save_to_allow_user_to_bind_a_new_device_on_next_login'); ?></small>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="linkedin_link"><?php echo get_phrase('biography'); ?></label>
                                            <div class="col-md-9">
                                                <textarea name="biography" id = "summernote-basic" class="form-control"><?php echo $user_data['biography']; ?></textarea>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="phone"><?php echo get_phrase('Phone'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" value="<?php echo $user_data['phone']; ?>" id="phone" name="phone">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="pharmacy_name"><?php echo get_phrase('pharmacy_name'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="pharmacy_name" name="pharmacy_name" value="<?php echo htmlspecialchars($user_data['pharmacy_name'] ?? ''); ?>" placeholder="e.g. GAR NIGAM COLONY, Dava india tikrapara">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="store_id"><?php echo get_phrase('store'); ?> / <?php echo get_phrase('shop'); ?></label>
                                            <div class="col-md-9">
                                                <select class="form-control select2" data-toggle="select2" name="store_id" id="store_id">
                                                    <option value=""><?php echo get_phrase('select_a_store'); ?></option>
                                                    <?php 
                                                    $all_stores = $this->db->where('status', 1)->get('stores')->result_array();
                                                    foreach ($all_stores as $store): ?>
                                                        <option value="<?php echo $store['id']; ?>" data-name="<?php echo htmlspecialchars($store['store_name']); ?>" data-state="<?php echo htmlspecialchars($store['state'] ?? ''); ?>" data-zone="<?php echo htmlspecialchars($store['zone'] ?? ''); ?>" <?php if (isset($user_data['store_id']) && $user_data['store_id'] == $store['id']) echo 'selected'; ?>><?php echo htmlspecialchars($store['store_name']); ?> <?php echo !empty($store['store_code']) ? '('.htmlspecialchars($store['store_code']).')' : ''; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="state"><?php echo get_phrase('state'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($user_data['state'] ?? ''); ?>" list="state_suggestions" placeholder="e.g. Chhattisgarh, Assam, Maharashtra">
                                                <datalist id="state_suggestions">
                                                    <option value="Andhra Pradesh">
                                                    <option value="Assam">
                                                    <option value="Bihar">
                                                    <option value="Chhattisgarh">
                                                    <option value="Delhi">
                                                    <option value="Gujarat">
                                                    <option value="Haryana">
                                                    <option value="Karnataka">
                                                    <option value="Kerala">
                                                    <option value="Madhya Pradesh">
                                                    <option value="Maharashtra">
                                                    <option value="Odisha">
                                                    <option value="Punjab">
                                                    <option value="Rajasthan">
                                                    <option value="Tamil Nadu">
                                                    <option value="Telangana">
                                                    <option value="Uttar Pradesh">
                                                    <option value="Uttarakhand">
                                                    <option value="West Bengal">
                                                </datalist>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="region"><?php echo get_phrase('region'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="region" name="region" value="<?php echo htmlspecialchars($user_data['region'] ?? ''); ?>" list="region_suggestions" placeholder="e.g. West, East, North, South, Central">
                                                <datalist id="region_suggestions">
                                                    <option value="West">
                                                    <option value="East">
                                                    <option value="North">
                                                    <option value="South">
                                                    <option value="Central">
                                                    <option value="North East">
                                                </datalist>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="address"><?php echo get_phrase('address'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" value="<?php echo $user_data['address']; ?>" id="address" name="address">
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="user_image"><?php echo get_phrase('user_image'); ?></label>
                                            <div class="col-md-9">
                                                <div class="d-flex">
                                                  <div class="">
                                                      <img class = "rounded-circle img-thumbnail" src="<?php echo $this->user_model->get_user_image_url($user_data['id']);?>" alt="" style="height: 50px; width: 50px;">
                                                  </div>
                                                  <div class="flex-grow-1 mt-1 pl-3">
                                                      <div class="input-group">
                                                          <div class="custom-file">
                                                              <input type="file" class="custom-file-input" name = "user_image" id="user_image" onchange="changeTitleOfImageUploader(this)" accept="image/*">
                                                              <label class="custom-file-label ellipsis" for="user_image"><?php echo get_phrase('choose_user_image'); ?></label>
                                                          </div>
                                                      </div>
                                                  </div>
                                              </div>
                                            </div>
                                        </div>
                                    </div> <!-- end col -->
                                </div> <!-- end row -->
                            </div>

                            <div class="tab-pane" id="login_credentials">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="email"> <?php echo get_phrase('email'); ?> </label>
                                            <div class="col-md-9">
                                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="password"><?php echo get_phrase('password'); ?> <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <div class="input-group">
                                                    <input type="text" id="password" name="password" class="form-control" value="<?php echo htmlspecialchars($display_password); ?>" placeholder="<?php echo get_phrase('password'); ?>" required autocomplete="new-password">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary" id="togglePasswordBtn" onclick="togglePasswordVisibility()" title="<?php echo get_phrase('show_or_hide_password'); ?>">
                                                            <i class="mdi mdi-eye-off-outline" id="togglePasswordIcon"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info" id="copyPasswordBtn" onclick="copyPasswordToClipboard()" title="<?php echo get_phrase('copy_password'); ?>">
                                                            <i class="mdi mdi-content-copy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-primary" onclick="generateAutoPassword()" title="<?php echo get_phrase('auto_generate_password'); ?>">
                                                            <i class="mdi mdi-refresh"></i> <?php echo get_phrase('auto_generate'); ?>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted mt-1">
                                                    <i class="mdi mdi-information-outline"></i> <?php echo get_phrase('you_can_view,_edit,_or_auto-generate_the_password'); ?>.
                                                </small>
                                            </div>
                                        </div>
                                    </div> <!-- end col -->
                                </div> <!-- end row -->
                            </div>

                            
                            <div class="tab-pane" id="finish">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="text-center">
                                            <h2 class="mt-0"><i class="mdi mdi-check-all"></i></h2>
                                            <h3 class="mt-0"><?php echo get_phrase('thank_you'); ?> !</h3>

                                            <p class="w-75 mb-2 mx-auto"><?php echo get_phrase('you_are_just_one_click_away'); ?></p>

                                            <div class="mb-3">
                                                <button type="button" class="btn btn-primary" onclick="checkRequiredFields()" name="button"><?php echo get_phrase('submit'); ?></button>
                                            </div>
                                        </div>
                                    </div> <!-- end col -->
                                </div> <!-- end row -->
                            </div>

                            <ul class="list-inline mb-0 wizard d-flex justify-content-between align-items-center">
                                <li class="previous list-inline-item mr-auto">
                                    <a href="javascript:;" class="btn btn-info"><i class="mdi mdi-arrow-left-bold mr-1"></i> <?php echo get_phrase('previous'); ?></a>
                                </li>
                                <li class="next list-inline-item ml-auto">
                                    <a href="javascript:;" class="btn btn-info"><?php echo get_phrase('next'); ?> <i class="mdi mdi-arrow-right-bold ml-1"></i></a>
                                </li>
                            </ul>

                        </div> <!-- tab-content -->
                    </div> <!-- end #progressbarwizard-->
                </form>

            </div> <!-- end card-body -->
        </div> <!-- end card-->
    </div>
</div>

<style>
/* Wizard navigation buttons alignment */
.wizard {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
}

.wizard .previous {
    margin-right: auto !important;
    text-align: left !important;
}

.wizard .next {
    margin-left: auto !important;
    text-align: right !important;
}

/* Hide Next button on finish tab (last step) */
#finish.active ~ .wizard .next,
#finish.active + .wizard .next,
#progressbarwizard .wizard li.next.disabled {
    display: none !important;
}
</style>

<script>
$(document).ready(function() {
    function toggleWizardNextBtn() {
        if ($('#finish').hasClass('active')) {
            $('#progressbarwizard .wizard .next').hide();
        } else {
            $('#progressbarwizard .wizard .next').show();
        }
    }

    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        if ($(e.target).attr('href') === '#finish') {
            $('#progressbarwizard .wizard .next').hide();
        } else {
            $('#progressbarwizard .wizard .next').show();
        }
    });

    $('#progressbarwizard .wizard li a').on('click', function() {
        setTimeout(toggleWizardNextBtn, 50);
    });

    toggleWizardNextBtn();
});

function togglePasswordVisibility() {
    var passwordInput = $('#password');
    var icon = $('#togglePasswordIcon');
    if (passwordInput.attr('type') === 'password') {
        passwordInput.attr('type', 'text');
        icon.removeClass('mdi-eye-outline').addClass('mdi-eye-off-outline');
    } else {
        passwordInput.attr('type', 'password');
        icon.removeClass('mdi-eye-off-outline').addClass('mdi-eye-outline');
    }
}

function generateAutoPassword() {
    var chars = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%";
    var pass = "";
    for (var i = 0; i < 10; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    var passwordInput = $('#password');
    passwordInput.val(pass);
    passwordInput.attr('type', 'text');
    $('#togglePasswordIcon').removeClass('mdi-eye-outline').addClass('mdi-eye-off-outline');

    if (typeof $.NotificationApp !== 'undefined') {
        $.NotificationApp.send("<?php echo get_phrase('password_generated'); ?>", "<?php echo get_phrase('new_password'); ?>: " + pass, "top-right", "rgba(0,0,0,0.2)", "info");
    }
}

function copyPasswordToClipboard() {
    var pass = $('#password').val();
    if (!pass || pass.trim() === '') {
        if (typeof $.NotificationApp !== 'undefined') {
            $.NotificationApp.send("<?php echo get_phrase('heads_up'); ?>!", "<?php echo get_phrase('password_field_is_empty'); ?>", "top-right", "rgba(0,0,0,0.2)", "warning");
        } else {
            alert('Password field is empty');
        }
        return;
    }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(pass).then(function() {
            if (typeof $.NotificationApp !== 'undefined') {
                $.NotificationApp.send("<?php echo get_phrase('success'); ?>!", "<?php echo get_phrase('password_copied_to_clipboard'); ?>", "top-right", "rgba(0,0,0,0.2)", "success");
            }
        }).catch(function() {
            fallbackCopyText(pass);
        });
    } else {
        fallbackCopyText(pass);
    }
}

function fallbackCopyText(text) {
    var tempInput = document.createElement("input");
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    try {
        document.execCommand("copy");
        if (typeof $.NotificationApp !== 'undefined') {
            $.NotificationApp.send("<?php echo get_phrase('success'); ?>!", "<?php echo get_phrase('password_copied_to_clipboard'); ?>", "top-right", "rgba(0,0,0,0.2)", "success");
        }
    } catch (e) {
        console.error('Could not copy text: ', e);
    }
    document.body.removeChild(tempInput);
}

$(document).ready(function() {
    $('#store_id').on('change', function() {
        var selected = $(this).find('option:selected');
        var sName = selected.data('name');
        var sState = selected.data('state');
        var sZone = selected.data('zone');
        if (sName && !$('#pharmacy_name').val()) {
            $('#pharmacy_name').val(sName);
        }
        if (sState && !$('#state').val()) {
            $('#state').val(sState);
        }
        if (sZone && !$('#region').val()) {
            $('#region').val(sZone);
        }
    });
});
</script>

