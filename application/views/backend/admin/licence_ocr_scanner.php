<!-- Pharmacist Licence OCR Document Scanner Card (Above First Name) -->
<div class="card border border-primary mb-4 bg-light-lighten shadow-sm" id="ocr_scanner_card" style="border-style: dashed !important; border-width: 2px !important; border-radius: 10px; background-color: #f8fbfe;">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
            <div class="d-flex align-items-center">
                <span class="avatar-sm rounded-circle bg-primary-lighten text-primary d-flex align-items-center justify-content-center mr-2" style="width: 38px; height: 38px; min-width: 38px;">
                    <i class="mdi mdi-ocr font-20"></i>
                </span>
                <div>
                    <h5 class="card-title text-primary mb-0 font-weight-bold">
                        <?php echo get_phrase('licence_document_ocr_scanner'); ?>
                    </h5>
                    <p class="text-muted font-12 mb-0">
                        <?php echo get_phrase('auto_extract_licence_number_using_tesseract_ocr_from_pdf_or_image'); ?>
                    </p>
                </div>
            </div>
            <span class="badge badge-primary-lighten badge-pill px-3 py-1 font-12 mt-1 mt-sm-0">
                <i class="mdi mdi-auto-fix mr-1"></i><?php echo get_phrase('auto_detect'); ?>
            </span>
        </div>

        <!-- Hidden File Input (placed outside dropzone to eliminate click event recursion) -->
        <input type="file" id="ocr_licence_doc" accept=".pdf,.png,.jpg,.jpeg,.bmp,.tiff" style="position: absolute; left: -9999px; opacity: 0; width: 1px; height: 1px;">

        <!-- Drag & Drop / File Input Area -->
        <div class="ocr-dropzone p-3 mt-2 text-center rounded border" id="ocr_drop_zone" style="background: #ffffff; border: 2px dashed #727cf5 !important; cursor: pointer; transition: all 0.2s ease;">
            <div id="ocr_upload_prompt">
                <i class="mdi mdi-cloud-upload-outline font-32 text-primary d-block mb-1"></i>
                <div class="font-14 font-weight-semibold text-dark mb-1">
                    <?php echo get_phrase('click_or_drag_and_drop_pharmacist_license_document_here'); ?>
                </div>
                <div class="text-muted font-12 mb-2">
                    <?php echo get_phrase('supports_pdf_scanned_documents_jpg_png'); ?> (e.g. Form 20/21, Amendment Letters)
                </div>
                <button type="button" class="btn btn-sm btn-primary px-3 py-1 font-12 shadow-sm" id="ocr_btn_browse">
                    <i class="mdi mdi-folder-open mr-1"></i> <?php echo get_phrase('browse_file'); ?>
                </button>
            </div>
            <div id="ocr_file_selected_info" style="display: none;" class="text-left">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center mb-1 mb-sm-0">
                        <i class="mdi mdi-file-check text-success font-22 mr-2"></i>
                        <div>
                            <strong id="ocr_selected_filename" class="font-13 text-dark"></strong>
                            <span id="ocr_selected_filesize" class="text-muted font-11 ml-2"></span>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-12 mr-1" id="ocr_btn_repick_file">
                            <i class="mdi mdi-folder-open mr-1"></i><?php echo get_phrase('choose_another'); ?>
                        </button>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 font-12" id="ocr_btn_clear_file">
                            <i class="mdi mdi-close-circle mr-1"></i><?php echo get_phrase('remove'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- OCR Processing Progress Box -->
        <div id="ocr_progress_box" class="mt-3 p-2 bg-white rounded border" style="display: none;">
            <div class="d-flex align-items-center justify-content-between font-12 mb-1">
                <span id="ocr_progress_status" class="text-primary font-weight-semibold">
                    <i class="mdi mdi-loading mdi-spin mr-1"></i> <?php echo get_phrase('processing_document'); ?>...
                </span>
                <span id="ocr_progress_percent" class="font-weight-bold text-primary">0%</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div id="ocr_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;"></div>
            </div>
        </div>

        <!-- OCR Success & Candidates Display Box -->
        <div id="ocr_results_box" class="mt-3" style="display: none;">
            <!-- Primary Success Alert -->
            <div class="alert alert-success d-flex align-items-center justify-content-between p-2 mb-2">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-check-decagram text-success font-22 mr-2"></i>
                    <div>
                        <strong class="font-13 text-success"><?php echo get_phrase('licence_number_detected'); ?>:</strong>
                        <span id="ocr_primary_licence_display" class="badge badge-success font-14 ml-1 px-2 py-1"></span>
                        <span class="text-muted font-12 ml-2 d-none d-sm-inline">(<?php echo get_phrase('auto_populated_into_licence_no_field_below'); ?>)</span>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-success font-11 py-1 px-2" id="ocr_btn_reapply">
                    <i class="mdi mdi-content-copy mr-1"></i><?php echo get_phrase('re_populate'); ?>
                </button>
            </div>

            <!-- Multiple Candidates Pills (if more than 1 license found) -->
            <div id="ocr_multiple_candidates_area" class="p-2 bg-white rounded border mb-2" style="display: none;">
                <div class="d-flex align-items-center mb-1">
                    <i class="mdi mdi-format-list-checks text-primary mr-1"></i>
                    <small class="text-dark font-weight-bold">
                        <?php echo get_phrase('all_detected_licence_numbers'); ?> (<?php echo get_phrase('click_any_to_populate_or_switch'); ?>):
                    </small>
                </div>
                <div id="ocr_candidate_chips" class="d-flex flex-wrap" style="gap: 8px;"></div>
            </div>

            <!-- Optional Detected Fields (Name & Start Date) -->
            <div id="ocr_auxiliary_fields_area" class="p-2 bg-white rounded border" style="display: none;">
                <div class="d-flex align-items-center mb-1">
                    <i class="mdi mdi-account-details-outline text-info mr-1"></i>
                    <small class="text-dark font-weight-bold">
                        <?php echo get_phrase('other_detected_document_details'); ?>:
                    </small>
                </div>
                <div id="ocr_auxiliary_chips" class="d-flex flex-wrap" style="gap: 8px;"></div>
            </div>
        </div>

        <!-- OCR Error / Notice Box -->
        <div id="ocr_error_box" class="mt-2" style="display: none;">
            <div class="alert alert-warning p-2 mb-0 font-12 d-flex align-items-center">
                <i class="mdi mdi-alert-circle-outline font-18 mr-2 text-warning"></i>
                <div id="ocr_error_message"></div>
            </div>
        </div>
    </div>
</div>

<!-- Vendor Scripts for OCR & PDF Processing -->
<script src="<?php echo base_url('assets/backend/js/vendor/pdf.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/tesseract.min.js'); ?>"></script>

<script type="text/javascript">
$(document).ready(function() {
    // Configure PDF.js Worker
    if (typeof pdfjsLib !== 'undefined') {
        pdfjsLib.GlobalWorkerOptions.workerSrc = '<?php echo base_url("assets/backend/js/vendor/pdf.worker.min.js"); ?>';
    }

    var $dropZone = $('#ocr_drop_zone');
    var $fileInput = $('#ocr_licence_doc');
    var $uploadPrompt = $('#ocr_upload_prompt');
    var $fileInfo = $('#ocr_file_selected_info');
    var $progressBox = $('#ocr_progress_box');
    var $progressBar = $('#ocr_progress_bar');
    var $progressStatus = $('#ocr_progress_status');
    var $progressPercent = $('#ocr_progress_percent');
    var $resultsBox = $('#ocr_results_box');
    var $errorBox = $('#ocr_error_box');
    var $primaryLicenceDisplay = $('#ocr_primary_licence_display');
    var $multipleArea = $('#ocr_multiple_candidates_area');
    var $candidateChips = $('#ocr_candidate_chips');
    var $auxiliaryArea = $('#ocr_auxiliary_fields_area');
    var $auxiliaryChips = $('#ocr_auxiliary_chips');

    var currentExtractedData = null;

    function openFilePicker(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var fileInputEl = document.getElementById('ocr_licence_doc');
        if (fileInputEl) {
            fileInputEl.click();
        }
    }

    // Trigger file dialog on clicking anywhere in dropzone, prompt, or browse buttons
    $dropZone.on('click', function(e) {
        if ($(e.target).closest('#ocr_btn_clear_file, #ocr_btn_repick_file').length) return;
        openFilePicker(e);
    });

    $('#ocr_btn_browse').on('click', function(e) {
        openFilePicker(e);
    });

    $('#ocr_btn_repick_file').on('click', function(e) {
        openFilePicker(e);
    });

    // Reset file input value when clicked so choosing the same file always fires change event
    $fileInput.on('click', function(e) {
        e.stopPropagation();
        $(this).val('');
    });

    // Drag and drop event handlers
    $dropZone.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropZone.css({'background-color': '#eef2ff', 'border-color': '#4e73df'});
    });

    $dropZone.on('dragleave dragend drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $dropZone.css({'background-color': '#ffffff', 'border-color': '#727cf5'});
    });

    $dropZone.on('drop', function(e) {
        var dt = e.originalEvent.dataTransfer;
        if (dt && dt.files && dt.files.length) {
            $fileInput[0].files = dt.files;
            handleSelectedFile(dt.files[0]);
        }
    });

    $fileInput.on('change', function(e) {
        if (this.files && this.files.length) {
            handleSelectedFile(this.files[0]);
        }
    });

    $('#ocr_btn_clear_file').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        resetOcrUI();
    });

    function resetOcrUI() {
        $fileInput.val('');
        $uploadPrompt.show();
        $fileInfo.hide();
        $progressBox.hide();
        $resultsBox.hide();
        $errorBox.hide();
        currentExtractedData = null;
    }

    function updateProgress(percent, statusText) {
        percent = Math.min(100, Math.max(0, Math.round(percent)));
        $progressBar.css('width', percent + '%');
        $progressPercent.text(percent + '%');
        if (statusText) {
            $progressStatus.html('<i class="mdi mdi-loading mdi-spin mr-1"></i> ' + statusText);
        }
    }

    function handleSelectedFile(file) {
        if (!file) return;

        $('#ocr_selected_filename').text(file.name);
        var sizeKb = (file.size / 1024).toFixed(1);
        $('#ocr_selected_filesize').text('(' + (sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB') + ')');
        $uploadPrompt.hide();
        $fileInfo.show();

        $errorBox.hide();
        $resultsBox.hide();
        $progressBox.show();
        updateProgress(5, "<?php echo get_phrase('reading_document'); ?>...");

        var ext = file.name.split('.').pop().toLowerCase();

        if (ext === 'pdf') {
            processPdfDocument(file);
        } else if (['png', 'jpg', 'jpeg', 'bmp', 'tiff', 'tif'].indexOf(ext) !== -1) {
            processImageDocument(file);
        } else {
            // Unsupported format
            showOcrError("<?php echo get_phrase('unsupported_file_format_please_upload_pdf_or_image'); ?>");
        }
    }

    // Process PDF using PDF.js first (direct text extraction) and fallback to OCR on canvas
    function processPdfDocument(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var typedarray = new Uint8Array(e.target.result);
            updateProgress(20, "<?php echo get_phrase('parsing_pdf_document_layer'); ?>...");

            pdfjsLib.getDocument({ data: typedarray }).promise.then(function(pdf) {
                var maxPages = Math.min(pdf.numPages, 3);
                var fullText = '';
                var pagePromises = [];

                for (var i = 1; i <= maxPages; i++) {
                    pagePromises.push((function(pageNumber) {
                        return pdf.getPage(pageNumber).then(function(page) {
                            return page.getTextContent().then(function(textContent) {
                                var pageStr = textContent.items.map(function(item) { return item.str; }).join(' ');
                                return pageStr;
                            });
                        });
                    })(i));
                }

                Promise.all(pagePromises).then(function(pageTexts) {
                    fullText = pageTexts.join('\n');

                    // Check if PDF had a valid text stream
                    if (fullText && fullText.trim().length > 30) {
                        updateProgress(65, "<?php echo get_phrase('analyzing_extracted_text'); ?>...");
                        sendExtractedTextToServer(fullText, file);
                    } else {
                        // PDF might be a scanned image! Render page 1 to canvas and run Tesseract
                        updateProgress(35, "<?php echo get_phrase('scanned_pdf_detected_rendering_for_tesseract_ocr'); ?>...");
                        renderPdfPageToCanvas(pdf, 1).then(function(canvas) {
                            runTesseractOnCanvas(canvas, file);
                        }).catch(function(err) {
                            console.warn("Canvas render error:", err);
                            fallbackUploadFileToServer(file);
                        });
                    }
                }).catch(function(err) {
                    console.warn("PDF text content error:", err);
                    fallbackUploadFileToServer(file);
                });
            }).catch(function(err) {
                console.warn("PDF.js error:", err);
                fallbackUploadFileToServer(file);
            });
        };
        reader.onerror = function() {
            fallbackUploadFileToServer(file);
        };
        reader.readAsArrayBuffer(file);
    }

    // Render a PDF page to offscreen canvas at 2x scale for high OCR accuracy
    function renderPdfPageToCanvas(pdf, pageNum) {
        return pdf.getPage(pageNum).then(function(page) {
            var scale = 2.0;
            var viewport = page.getViewport({ scale: scale });
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            var renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            return page.render(renderContext).promise.then(function() {
                return canvas;
            });
        });
    }

    // Process Image file using Tesseract.js
    function processImageDocument(file) {
        updateProgress(25, "<?php echo get_phrase('initializing_tesseract_ocr_engine'); ?>...");
        if (typeof Tesseract === 'undefined') {
            fallbackUploadFileToServer(file);
            return;
        }

        Tesseract.recognize(
            file,
            'eng',
            {
                logger: function(m) {
                    if (m.status === 'recognizing text' && m.progress) {
                        var p = Math.round(25 + (m.progress * 65));
                        updateProgress(p, "<?php echo get_phrase('tesseract_ocr_recognizing_text'); ?> (" + Math.round(m.progress * 100) + "%)...");
                    }
                }
            }
        ).then(function(res) {
            var text = res.data && res.data.text ? res.data.text : '';
            sendExtractedTextToServer(text, file);
        }).catch(function(err) {
            console.warn("Tesseract client OCR error:", err);
            fallbackUploadFileToServer(file);
        });
    }

    // Run Tesseract on rendered Canvas
    function runTesseractOnCanvas(canvas, originalFile) {
        updateProgress(45, "<?php echo get_phrase('running_tesseract_ocr_on_scanned_page'); ?>...");
        if (typeof Tesseract === 'undefined') {
            fallbackUploadFileToServer(originalFile);
            return;
        }

        Tesseract.recognize(
            canvas,
            'eng',
            {
                logger: function(m) {
                    if (m.status === 'recognizing text' && m.progress) {
                        var p = Math.round(45 + (m.progress * 45));
                        updateProgress(p, "<?php echo get_phrase('tesseract_ocr_recognizing'); ?> (" + Math.round(m.progress * 100) + "%)...");
                    }
                }
            }
        ).then(function(res) {
            var text = res.data && res.data.text ? res.data.text : '';
            sendExtractedTextToServer(text, originalFile);
        }).catch(function(err) {
            console.warn("Tesseract canvas OCR error:", err);
            fallbackUploadFileToServer(originalFile);
        });
    }

    // Send extracted text to server endpoint for regex extraction & verification
    function sendExtractedTextToServer(text, originalFile) {
        updateProgress(90, "<?php echo get_phrase('parsing_licence_numbers'); ?>...");

        $.ajax({
            url: "<?php echo site_url('admin/extract_licence_ocr'); ?>",
            type: "POST",
            dataType: "json",
            data: { client_extracted_text: text },
            success: function(response) {
                if (response && response.status && response.all_candidates && response.all_candidates.length) {
                    updateProgress(100, "<?php echo get_phrase('complete'); ?>!");
                    displayOcrResults(response);
                } else if (originalFile) {
                    // Try fallback file upload if text parsing didn't find candidate
                    fallbackUploadFileToServer(originalFile);
                } else {
                    showOcrError(response && response.message ? response.message : "<?php echo get_phrase('no_drug_licence_numbers_found'); ?>");
                }
            },
            error: function() {
                if (originalFile) {
                    fallbackUploadFileToServer(originalFile);
                } else {
                    showOcrError("<?php echo get_phrase('failed_to_communicate_with_server'); ?>");
                }
            }
        });
    }

    // Fallback file upload directly to server endpoint
    function fallbackUploadFileToServer(file) {
        updateProgress(50, "<?php echo get_phrase('analyzing_on_server'); ?>...");

        var formData = new FormData();
        formData.append('licence_doc', file);

        $.ajax({
            url: "<?php echo site_url('admin/extract_licence_ocr'); ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if (response && response.status && response.all_candidates && response.all_candidates.length) {
                    updateProgress(100, "<?php echo get_phrase('complete'); ?>!");
                    displayOcrResults(response);
                } else {
                    showOcrError(response && response.message ? response.message : "<?php echo get_phrase('could_not_extract_licence_number_automatically'); ?>");
                }
            },
            error: function() {
                showOcrError("<?php echo get_phrase('error_processing_document_on_server'); ?>");
            }
        });
    }

    function showOcrError(msg) {
        $progressBox.hide();
        $('#ocr_error_message').html(msg + " <br><small class='text-muted'><?php echo get_phrase('you_can_enter_the_licence_number_manually_in_the_field_below'); ?>.</small>");
        $errorBox.slideDown();
    }

    // Display extracted results and auto-populate
    function displayOcrResults(data) {
        currentExtractedData = data;
        $progressBox.slideUp();
        $errorBox.hide();

        var primary = data.licence_no || (data.all_candidates && data.all_candidates[0]) || '';
        $primaryLicenceDisplay.text(primary);

        // Auto-populate the #licence_no input field
        populateLicenceField(primary);

        // Multiple Candidates Chips
        if (data.all_candidates && data.all_candidates.length > 1) {
            $candidateChips.empty();
            $.each(data.all_candidates, function(idx, lic) {
                var isPrimary = (lic === primary);
                var $chip = $('<button type="button" class="btn btn-sm ' + (isPrimary ? 'btn-primary' : 'btn-outline-primary') + ' font-12 py-1 px-2 candidate-chip mr-1 mb-1">' +
                    '<i class="mdi mdi-checkbox-marked-circle-outline mr-1"></i>' + lic +
                    (isPrimary ? ' <span class="badge badge-light text-primary font-10 ml-1"><?php echo get_phrase('selected'); ?></span>' : '') +
                    '</button>');

                $chip.on('click', function() {
                    $('.candidate-chip').removeClass('btn-primary').addClass('btn-outline-primary').find('.badge').remove();
                    $(this).removeClass('btn-outline-primary').addClass('btn-primary').append(' <span class="badge badge-light text-primary font-10 ml-1"><?php echo get_phrase('selected'); ?></span>');
                    $primaryLicenceDisplay.text(lic);
                    populateLicenceField(lic);
                });

                $candidateChips.append($chip);
            });

            // Option to combine both licenses (e.g. Form 20 & Form 21)
            var combinedLics = data.all_candidates.join(', ');
            var $combinedChip = $('<button type="button" class="btn btn-sm btn-outline-secondary font-12 py-1 px-2 candidate-chip mb-1">' +
                '<i class="mdi mdi-plus-box-multiple mr-1"></i><?php echo get_phrase('use_both'); ?>: ' + combinedLics +
                '</button>');

            $combinedChip.on('click', function() {
                $('.candidate-chip').removeClass('btn-primary').addClass('btn-outline-primary').find('.badge').remove();
                $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
                $primaryLicenceDisplay.text(combinedLics);
                populateLicenceField(combinedLics);
            });

            $candidateChips.append($combinedChip);
            $multipleArea.show();
        } else {
            $multipleArea.hide();
        }

        // Auxiliary fields (Detected Member Name & Start Date)
        $auxiliaryChips.empty();
        var hasAux = false;

        if (data.first_name || data.member_name) {
            var fName = data.first_name || data.member_name;
            var lName = data.last_name || '';
            var $nameChip = $('<button type="button" class="btn btn-sm btn-outline-info font-12 py-1 px-2 mr-1 mb-1">' +
                '<i class="mdi mdi-account mr-1"></i><?php echo get_phrase('fill_name'); ?>: ' + fName + (lName ? ' ' + lName : '') +
                '</button>');

            $nameChip.on('click', function() {
                if ($('#first_name').length) $('#first_name').val(fName).addClass('is-valid');
                if (lName && $('#last_name').length) $('#last_name').val(lName).addClass('is-valid');
                $(this).removeClass('btn-outline-info').addClass('btn-info').append(' <i class="mdi mdi-check"></i>');
                if (typeof success_notify === 'function') {
                    success_notify("<?php echo get_phrase('name_applied_successfully'); ?>");
                }
            });

            $auxiliaryChips.append($nameChip);
            hasAux = true;
        }

        if (data.licence_start_date) {
            var $dateChip = $('<button type="button" class="btn btn-sm btn-outline-info font-12 py-1 px-2 mb-1">' +
                '<i class="mdi mdi-calendar mr-1"></i><?php echo get_phrase('fill_start_date'); ?>: ' + data.licence_start_date +
                '</button>');

            $dateChip.on('click', function() {
                if ($('#licence_start_date').length) {
                    $('#licence_start_date').val(data.licence_start_date).addClass('is-valid');
                }
                $(this).removeClass('btn-outline-info').addClass('btn-info').append(' <i class="mdi mdi-check"></i>');
                if (typeof success_notify === 'function') {
                    success_notify("<?php echo get_phrase('start_date_applied_successfully'); ?>");
                }
            });

            $auxiliaryChips.append($dateChip);
            hasAux = true;
        }

        if (hasAux) {
            $auxiliaryArea.show();
        } else {
            $auxiliaryArea.hide();
        }

        $resultsBox.slideDown();

        if (typeof success_notify === 'function') {
            success_notify(data.message || "<?php echo get_phrase('licence_extracted_successfully'); ?>");
        }
    }

    function populateLicenceField(val) {
        var $licField = $('#licence_no');
        if ($licField.length) {
            $licField.val(val);

            // Visual feedback: green pulse animation
            $licField.addClass('is-valid border-success')
                     .css({'background-color': '#e8f5e9', 'transition': 'background-color 0.4s ease'});

            setTimeout(function() {
                $licField.css('background-color', '');
            }, 1800);
        }
    }

    $('#ocr_btn_reapply').on('click', function() {
        if (currentExtractedData) {
            var primary = currentExtractedData.licence_no || (currentExtractedData.all_candidates && currentExtractedData.all_candidates[0]);
            if (primary) {
                populateLicenceField(primary);
                if (typeof success_notify === 'function') {
                    success_notify("<?php echo get_phrase('licence_number_re_populated'); ?>");
                }
            }
        }
    });
});
</script>
