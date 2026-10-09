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

                <h4 class="header-title mb-3"><?php echo get_phrase('pharmacist_add_form'); ?></h4>

                <form class="required-form" action="<?php echo site_url('admin/users/add'); ?>" enctype="multipart/form-data" method="post">
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
                                            <label class="col-md-3 col-form-label" for="first_name"><?php echo get_phrase('first_name'); ?><span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="last_name"><?php echo get_phrase('last_name'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="last_name" name="last_name">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="employee_id"><?php echo get_phrase('employee_id'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="e.g. EMP-001">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="gender"><?php echo get_phrase('gender'); ?></label>
                                            <div class="col-md-9">
                                                <select class="form-control select2" data-toggle="select2" name="gender" id="gender">
                                                    <option value=""><?php echo get_phrase('select_gender'); ?></option>
                                                    <option value="Male"><?php echo get_phrase('male'); ?></option>
                                                    <option value="Female"><?php echo get_phrase('female'); ?></option>
                                                    <option value="Other"><?php echo get_phrase('other'); ?></option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="designation"><?php echo get_phrase('designation'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="designation" name="designation" list="designation_suggestions" placeholder="e.g. B. Pharm, M. Pharm, Pharmacist">
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
                                                <input type="text" class="form-control" id="licence_no" name="licence_no" placeholder="e.g. LIC-123456">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="licence_start_date"><?php echo get_phrase('licence_start_date'); ?></label>
                                            <div class="col-md-9">
                                                <input type="date" class="form-control" id="licence_start_date" name="licence_start_date">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="licence_end_date"><?php echo get_phrase('licence_end_date'); ?></label>
                                            <div class="col-md-9">
                                                <input type="date" class="form-control" id="licence_end_date" name="licence_end_date">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="mac_address"><?php echo get_phrase('mac_address'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control text-uppercase" id="mac_address" name="mac_address" placeholder="e.g. 9C:67:D6:81:7F:31 (Optional)">
                                                <small class="text-muted"><?php echo get_phrase('leave_empty_to_automatically_bind_mac_address_on_first_login'); ?></small>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="linkedin_link"><?php echo get_phrase('biography'); ?></label>
                                            <div class="col-md-9">
                                                <textarea name="biography" id = "summernote-basic" class="form-control"></textarea>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="phone"><?php echo get_phrase('Phone'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="phone" name="phone">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="pharmacy_name"><?php echo get_phrase('pharmacy_name'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="pharmacy_name" name="pharmacy_name" placeholder="e.g. GAR NIGAM COLONY, Dava india tikrapara">
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
                                                        <option value="<?php echo $store['id']; ?>" data-name="<?php echo htmlspecialchars($store['store_name']); ?>" data-state="<?php echo htmlspecialchars($store['state'] ?? ''); ?>" data-zone="<?php echo htmlspecialchars($store['zone'] ?? ''); ?>"><?php echo htmlspecialchars($store['store_name']); ?> <?php echo !empty($store['store_code']) ? '('.htmlspecialchars($store['store_code']).')' : ''; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="state"><?php echo get_phrase('state'); ?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="state" name="state" list="state_suggestions" placeholder="e.g. Chhattisgarh, Assam, Maharashtra">
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
                                                <input type="text" class="form-control" id="region" name="region" list="region_suggestions" placeholder="e.g. West, East, North, South, Central">
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
                                                <input type="text" class="form-control" id="address" name="address">
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="user_image"><?php echo get_phrase('user_image'); ?></label>
                                            <div class="col-md-9">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="user_image" name="user_image" accept="image/*" onchange="changeTitleOfImageUploader(this)">
                                                        <label class="custom-file-label" for="user_image"><?php echo get_phrase('choose_user_image'); ?></label>
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
                                            <label class="col-md-3 col-form-label" for="email"><?php echo get_phrase('email'); ?></label>
                                            <div class="col-md-9">
                                                <input type="email" id="email" name="email" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3">
                                            <label class="col-md-3 col-form-label" for="password"><?php echo get_phrase('password'); ?><span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="password" id="password" name="password" class="form-control" required>
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
                                    <a href="javascript:;" class="btn btn-info"> <i class="mdi mdi-arrow-left-bold mr-1"></i> <?php echo get_phrase('previous'); ?> </a>
                                </li>
                                <li class="next list-inline-item ml-auto">
                                    <a href="javascript:;" class="btn btn-info"> <?php echo get_phrase('next'); ?> <i class="mdi mdi-arrow-right-bold ml-1"></i> </a>
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
