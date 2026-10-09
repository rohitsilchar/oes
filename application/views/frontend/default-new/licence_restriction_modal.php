<?php 
$is_licence_missing = is_pharmacist_licence_missing(); 
$current_page_name = isset($page_name) ? $page_name : '';
$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$is_profile_page = ($current_page_name == 'user_profile' || 
                    $current_page_name == 'profile' || 
                    strpos($request_uri, 'user_profile') !== false || 
                    strpos($request_uri, 'home/profile') !== false || 
                    strpos($request_uri, 'licence_ocr') !== false || 
                    strpos($request_uri, 'tab=licence_ocr') !== false);
?>

<?php if ($is_licence_missing && !$is_profile_page): ?>
<!-- Access Denied Modal for Missing Pharmacist Licence -->
<div class="modal fade" id="licenceAccessDeniedModal" tabindex="-1" aria-labelledby="licenceAccessDeniedModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content access-denied-modal-content">
            <!-- Close Button -->
            <button type="button" class="ad-close-btn" data-bs-dismiss="modal" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>

            <!-- Hero Section -->
            <div class="ad-hero-wrap">
                <div class="ad-badge-container">
                    <div class="ad-pulse-ring-outer"></div>
                    <div class="ad-pulse-ring-inner"></div>
                    <div class="ad-icon-circle">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="ad-mini-lock-badge">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>

                <div>
                    <div>
                        <span class="ad-pill-badge">
                            <span class="ad-pill-dot"></span>
                            <?php echo get_phrase('access_restricted') ?: 'ACCESS RESTRICTED'; ?>
                        </span>
                    </div>
                    <h3 class="ad-main-title" id="licenceAccessDeniedModalLabel">
                        <?php echo get_phrase('access_denied') ?: 'Access Denied'; ?>
                    </h3>
                    <div class="ad-sub-title">
                        <?php echo get_phrase('pharmacist_licence_required') ?: 'Pharmacist Licence Required'; ?>
                    </div>
                    <p class="ad-description">
                        <?php echo get_phrase('access_to_this_page_is_denied_because_you_have_not_added_your_pharmacist_licence_number') ?: 'Access to this page is denied because your Pharmacist Licence Number has not been registered yet. Please add your licence details or scan your certificate to unlock full access.'; ?>
                    </p>
                </div>
            </div>

            <!-- Info / Benefit Container -->
            <div class="ad-info-box">
                <div class="ad-info-row">
                    <div class="ad-info-icon red">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <div class="ad-info-content">
                        <h6><?php echo get_phrase('mandatory_compliance') ?: 'Mandatory Compliance Required'; ?></h6>
                        <p><?php echo get_phrase('course_access_and_learning_modules_are_strictly_reserved_for_registered_pharmacists') ?: 'Courses, learning materials, and certifications are strictly reserved for registered pharmacists.'; ?></p>
                    </div>
                </div>
                <div class="ad-info-row">
                    <div class="ad-info-icon blue">
                        <i class="fas fa-magic"></i>
                    </div>
                    <div class="ad-info-content">
                        <h6><?php echo get_phrase('fast_ocr_verification') ?: 'Instant OCR Verification'; ?></h6>
                        <p><?php echo get_phrase('upload_your_state_council_certificate_or_drug_licence_to_auto_extract_details_instantly') ?: 'Upload your council certificate or drug licence (PDF / Image) to auto-extract details in 5 seconds.'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="ad-action-footer">
                <a href="<?php echo site_url('home/profile/user_profile?tab=licence_ocr'); ?>" class="btn-ad-primary" id="btn_modal_add_licence" onclick="if (typeof bootstrap !== 'undefined' && bootstrap.Modal) { var m = bootstrap.Modal.getInstance(document.getElementById('licenceAccessDeniedModal')); if (m) { m.hide(); } } $('.modal-backdrop').remove(); $('body').removeClass('modal-open');">
                    <i class="fas fa-file-invoice"></i>
                    <span><?php echo get_phrase('upload_licence_document_via_ocr') ?: 'Add Licence / Upload via OCR'; ?></span>
                    <i class="fas fa-arrow-right ms-auto ad-btn-arrow"></i>
                </a>
                <button type="button" class="btn-ad-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-home"></i>
                    <span><?php echo get_phrase('return_to_home_page') ?: 'Return to Home Page'; ?></span>
                </button>
                <div class="ad-secure-note">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo get_phrase('quick_10_second_verification_unlocks_all_features_instantly') ?: 'Quick 10-second verification • Unlocks all features instantly'; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
/* Access Denied Modal Container */
#licenceAccessDeniedModal {
    z-index: 99999 !important;
}
body.modal-open .modal-backdrop {
    z-index: 99990 !important;
}

#licenceAccessDeniedModal .modal-dialog {
    max-width: 490px;
    margin: 1.75rem auto;
}
#licenceAccessDeniedModal .modal-content.access-denied-modal-content {
    border: none;
    border-radius: 28px;
    box-shadow: 0 30px 90px -15px rgba(0, 0, 0, 0.38), 0 0 1px 1px rgba(244, 63, 94, 0.15);
    background: #ffffff;
    overflow: hidden;
    position: relative;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    animation: adModalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes adModalFadeIn {
    from { opacity: 0; transform: translateY(14px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Close Button */
.ad-close-btn {
    position: absolute;
    top: 18px;
    right: 18px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(241, 245, 249, 0.95);
    border: 1px solid rgba(226, 232, 240, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 10;
}
.ad-close-btn:hover {
    background: #fee2e2;
    color: #e11d48;
    transform: rotate(90deg);
}

/* Hero Wrap */
.ad-hero-wrap {
    position: relative;
    padding: 38px 26px 12px;
    background: radial-gradient(circle at 50% 18%, #fff1f2 0%, #ffffff 82%);
    text-align: center;
}

/* Pulse Badge Graphics */
.ad-badge-container {
    position: relative;
    display: inline-block;
    margin-bottom: 14px;
}
.ad-pulse-ring-outer {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 106px;
    height: 106px;
    border-radius: 50%;
    background: rgba(244, 63, 94, 0.12);
    animation: adPulseOuter 2.4s infinite ease-in-out;
}
.ad-pulse-ring-inner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 88px;
    height: 88px;
    border-radius: 50%;
    background: rgba(244, 63, 94, 0.22);
    animation: adPulseInner 2.4s infinite ease-in-out;
}
@keyframes adPulseOuter {
    0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.6; }
    50% { transform: translate(-50%, -50%) scale(1.18); opacity: 0.15; }
}
@keyframes adPulseInner {
    0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.8; }
    50% { transform: translate(-50%, -50%) scale(1.12); opacity: 0.3; }
}
.ad-icon-circle {
    position: relative;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(145deg, #f43f5e 0%, #e11d48 55%, #9f1239 100%);
    box-shadow: 0 14px 28px -6px rgba(225, 29, 72, 0.48), inset 0 2px 4px rgba(255, 255, 255, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 28px;
    margin: 0 auto;
    border: 4px solid #ffffff;
}
.ad-mini-lock-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dc2626;
    font-size: 12px;
}

/* Pill Badge */
.ad-pill-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #fee2e2;
    color: #be123c;
    border: 1px solid rgba(244, 63, 94, 0.25);
    padding: 5px 14px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.9px;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.ad-pill-badge .ad-pill-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #e11d48;
    box-shadow: 0 0 6px #e11d48;
}

/* Titles */
.ad-main-title {
    font-size: 25px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 3px;
    letter-spacing: -0.5px;
    line-height: 1.25;
}
.ad-sub-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #e11d48;
    margin-bottom: 8px;
}
.ad-description {
    font-size: 13.5px;
    color: #64748b;
    line-height: 1.55;
    margin-bottom: 0;
    padding: 0 10px;
}

/* Info Box */
.ad-info-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 15px 18px;
    margin: 16px 24px 20px;
    display: flex;
    flex-direction: column;
    gap: 11px;
}
.ad-info-row {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    text-align: left;
}
.ad-info-row:not(:last-child) {
    padding-bottom: 11px;
    border-bottom: 1px dashed #e2e8f0;
}
.ad-info-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.04);
}
.ad-info-icon.red {
    background: #fee2e2;
    color: #e11d48;
    border: 1px solid #fecdd3;
}
.ad-info-icon.blue {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #dbeafe;
}
.ad-info-content h6 {
    font-size: 13.5px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2px;
}
.ad-info-content p {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 0;
    line-height: 1.45;
}

/* Action Buttons */
.ad-action-footer {
    padding: 0 24px 24px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.btn-ad-primary {
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 15px;
    padding: 13.5px 22px;
    font-size: 14.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 11px;
    box-shadow: 0 12px 24px -5px rgba(37, 99, 235, 0.45);
    transition: all 0.25s ease;
    text-decoration: none;
    cursor: pointer;
}
.btn-ad-primary:hover {
    background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 50%, #2563eb 100%);
    box-shadow: 0 16px 30px -5px rgba(37, 99, 235, 0.55);
    transform: translateY(-2px);
    color: #ffffff !important;
}
.btn-ad-primary .ad-btn-arrow {
    transition: transform 0.2s ease;
}
.btn-ad-primary:hover .ad-btn-arrow {
    transform: translateX(4px);
}

.btn-ad-secondary {
    background: #ffffff;
    color: #64748b !important;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 11px 18px;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
    text-decoration: none;
    cursor: pointer;
}
.btn-ad-secondary:hover {
    background: #f8fafc;
    color: #1e293b !important;
    border-color: #cbd5e1;
}

.ad-secure-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 11.5px;
    color: #94a3b8;
    font-weight: 500;
    margin-top: 3px;
}
.ad-secure-note i {
    color: #10b981;
}
</style>

<script type="text/javascript">
window.is_pharmacist_licence_missing = true;

window.showLicenceDeniedModal = function() {
    // If currently on user profile or licence OCR scanner page, NEVER show modal
    var currentLoc = (window.location.href || '').toLowerCase();
    if (currentLoc.indexOf('user_profile') !== -1 || currentLoc.indexOf('home/profile') !== -1 || currentLoc.indexOf('tab=licence_ocr') !== -1 || currentLoc.indexOf('#licence_ocr') !== -1) {
        return false;
    }

    var modalEl = document.getElementById('licenceAccessDeniedModal');
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var myModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            myModal.show();
        } else if (typeof jQuery !== 'undefined') {
            jQuery('#licenceAccessDeniedModal').modal('show');
        }
    }
};

$(document).ready(function() {
    // 1. Auto-show modal if redirected by backend flashdata
    <?php 
    $ci_inst =& get_instance();
    if ($ci_inst && isset($ci_inst->session) && $ci_inst->session->flashdata('licence_missing_modal')): 
    ?>
        setTimeout(function() {
            window.showLicenceDeniedModal();
        }, 200);
    <?php endif; ?>

    // 2. Client-side link interception for restricted pages
    function isSafeUrl(url) {
        if (!url || url === '#' || url.indexOf('javascript:') === 0 || url.indexOf('tel:') === 0 || url.indexOf('mailto:') === 0) {
            return true;
        }

        try {
            var targetUrl = new URL(url, window.location.href);
            // Ignore external sites
            if (targetUrl.origin !== window.location.origin) {
                return true;
            }

            var fullUrl = targetUrl.href.toLowerCase();
            var cleanPath = (targetUrl.origin + targetUrl.pathname).replace(/\/$/, '').toLowerCase();

            // Allow User Profile page and licence OCR tab unconditionally
            if (fullUrl.indexOf('user_profile') !== -1 || fullUrl.indexOf('home/profile') !== -1 || fullUrl.indexOf('tab=licence_ocr') !== -1 || fullUrl.indexOf('#licence_ocr') !== -1) {
                return true;
            }

            // Allow Home page and subpaths
            var homeBase = '<?php echo site_url("home"); ?>'.replace(/\/$/, '').toLowerCase();
            var rootBase = '<?php echo site_url(); ?>'.replace(/\/$/, '').toLowerCase();

            if (cleanPath === homeBase || cleanPath === rootBase || cleanPath === (homeBase + '/index')) {
                return true;
            }

            // Allow Logout
            var logoutBase = '<?php echo site_url("login/logout"); ?>'.replace(/\/$/, '').toLowerCase();
            if (cleanPath === logoutBase) {
                return true;
            }

            // All other links under current site are restricted!
            return false;
        } catch(e) {
            return true;
        }
    }

    $(document).on('click', 'a', function(e) {
        var $link = $(this);
        var href = $link.attr('href');

        // Never intercept the modal button itself, dismissals, or tabs
        if ($link.is('#btn_modal_add_licence') || $link.hasClass('btn-ad-primary') || $link.attr('data-bs-toggle') || $link.attr('data-bs-dismiss') || $link.attr('data-toggle')) {
            return;
        }

        if (!isSafeUrl(href)) {
            e.preventDefault();
            e.stopPropagation();
            window.showLicenceDeniedModal();
            return false;
        }
    });
});
</script>
<?php else: ?>
<script type="text/javascript">
window.is_pharmacist_licence_missing = false;
window.showLicenceDeniedModal = function() {
    return false;
};
</script>
<?php endif; ?>
