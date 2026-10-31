<script type="text/javascript">
function showAjaxModal(url, title, modalType)
{
    $('#modal_ajax .modal-dialog').removeClass('modal-sm');
    $('#modal_ajax .modal-dialog').removeClass('modal-md');
    $('#modal_ajax .modal-dialog').removeClass('modal-lg');
    $('#modal_ajax .modal-dialog').removeClass('modal-xl');
    $('#modal_ajax .modal-dialog').removeClass('modal-fullscreen');

    if(modalType){
        $('#modal_ajax .modal-dialog').addClass('modal-'+modalType);
    }
    // SHOWING AJAX PRELOADER IMAGE
    jQuery('#modal_ajax .modal-body').html('<div class="w-100 text-center pt-5"><img loading="lazy" class="mt-5 mb-5" width="80px" src="<?= base_url(); ?>assets/global/gif/page-loader-2.gif"></div>');

    // LOADING THE AJAX MODAL
    jQuery('#modal_ajax').modal('show', {backdrop: 'true'});

    // SHOW AJAX RESPONSE ON REQUEST SUCCESS
    $.ajax({
        url: url,
        success: function(response)
        {
            jQuery('#modal_ajax .title').html(title);
            jQuery('#modal_ajax .modal-body').html(response);
        }
    });
}

function lesson_preview(url, title){
    // SHOWING AJAX PRELOADER IMAGE
    jQuery('#lesson_preview .title').html(title);
    jQuery('#lesson_preview .modal-body').html('<div class="w-100 text-center pt-5"><img loading="lazy" class="mt-5 mb-5" width="80px" src="<?= base_url(); ?>assets/global/gif/page-loader-2.gif"></div>');

    // LOADING THE AJAX MODAL
    jQuery('#lesson_preview').modal('show', {backdrop: 'true'});

    // SHOW AJAX RESPONSE ON REQUEST SUCCESS
    $.ajax({
        url: url,
        success: function(response)
        {
            jQuery('#lesson_preview .modal-body').html(response);
        }
    });
}
</script>

<!-- (Ajax Modal)-->
<div class="modal fade" id="modal_ajax">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="title text-white"></h5>
                <button type="button" class="btn btn-secondary ms-auto py-0 px-2" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" style="overflow:auto;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default text-white" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    #lesson_preview, .modal, .modal-backdrop {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
}
</style>

<div class="modal" id="lesson_preview" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true" data-bs-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="position: relative; overflow: hidden; background: #0b0f19; min-height: 650px;">
            <!-- Anti-Screenshot Protected Shield Overlay -->
            <div id="modal_screen_shield" style="position: absolute; inset: 0; width: 100%; height: 100%; background: #0b0f19; z-index: 99999; display: none; align-items: center; justify-content: center; flex-direction: column; text-align: center; padding: 24px; color: #f87171; pointer-events: none;">
                <div style="width: 52px; height: 52px; margin-bottom: 12px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: center; color: #ef4444;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <div style="font-size: 17px; font-weight: 700; color: #f87171; margin-bottom: 6px;"><?php echo get_phrase('Protected Content'); ?></div>
                <div style="font-size: 13px; color: #94a3b8;"><?php echo get_phrase('Screenshots, screen recording, and print capture are prohibited for this document.'); ?></div>
            </div>

            <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1); background: #111827; height: 50px;">
                <h5 class="title text-white"></h5>
                <button type="button" class="btn btn-secondary ms-auto py-0 px-2" data-bs-dismiss="modal" aria-hidden="true" onclick="if(typeof player !== 'undefined' && player && player.pause) player.pause();">&times;</button>
            </div>
            <div class="modal-body" style="overflow:auto; background: #0b0f19; padding: 0; min-height: 600px; height: 600px;">
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function() {
    var modal = document.getElementById('lesson_preview');
    var shield = document.getElementById('modal_screen_shield');
    var isLocked = false;
    var lockTimer = null;
    var isMouseInsideModal = false;

    function isModalOpen() {
        return modal && (modal.classList.contains('show') || $(modal).is(':visible'));
    }

    function getModalBody() {
        return modal ? modal.querySelector('.modal-body') : null;
    }

    function wipeClip() {
        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText('').catch(function() {});
            }
        } catch(e) {}
    }

    function notifyIframe(action) {
        try {
            var ifr = modal ? modal.querySelector('iframe') : null;
            if (ifr && ifr.contentWindow) {
                ifr.contentWindow.postMessage({ type: 'SECURITY_EVENT', action: action }, '*');
            }
        } catch(e) {}
    }

    function hideContent() {
        var mb = getModalBody();
        if (mb) {
            mb.style.visibility = 'hidden';
            mb.style.opacity = '0';
        }
        var ifr = modal ? modal.querySelector('iframe') : null;
        if (ifr) {
            ifr.style.visibility = 'hidden';
            ifr.style.opacity = '0';
        }
        if (shield) {
            shield.style.display = 'flex';
        }
        notifyIframe('HIDE');
    }

    function showContent() {
        if (isLocked) return;
        if (!document.hasFocus() || document.hidden) return;

        var mb = getModalBody();
        if (mb) {
            mb.style.visibility = 'visible';
            mb.style.opacity = '1';
        }
        var ifr = modal ? modal.querySelector('iframe') : null;
        if (ifr) {
            ifr.style.visibility = 'visible';
            ifr.style.opacity = '1';
        }
        if (shield) {
            shield.style.display = 'none';
        }
        notifyIframe('SHOW');
    }
        if (shield) {
            shield.style.display = 'none';
        }
        notifyIframe('SHOW');
    }

    function lockAndHide(duration) {
        isLocked = true;
        hideContent();
        wipeClip();
        if (lockTimer) clearTimeout(lockTimer);
        lockTimer = setTimeout(function() {
            isLocked = false;
            if (isModalOpen() && document.hasFocus() && !document.hidden && isMouseInsideModal) {
                showContent();
            }
        }, duration || 2500);
    }

    // Modal Content Hover Tracking (Cursor-Present Protection)
    // When the mouse leaves the modal to click Snipping Tool or another window, content vanishes instantly!
    var modalContent = modal ? modal.querySelector('.modal-content') : null;
    if (modalContent) {
        modalContent.addEventListener('mouseenter', function() {
            isMouseInsideModal = true;
            if (isModalOpen() && document.hasFocus() && !document.hidden && !isLocked) {
                showContent();
            }
        });

        modalContent.addEventListener('mouseleave', function() {
            isMouseInsideModal = false;
            if (isModalOpen()) {
                hideContent();
            }
        });
    }

    // 1. Loss of window focus (Snipping tool, Win+Shift+S, Alt-Tab, external capture tools)
    window.addEventListener('blur', function() {
        if (!isModalOpen()) return;
        // If mouse is already outside modal (e.g. user moved to Snipping Tool), hide at 0ms!
        if (!isMouseInsideModal) {
            isLocked = true;
            hideContent();
        } else {
            // Mouse was over modal, verify if focus moved to iframe or an external app
            setTimeout(function() {
                if (!document.hasFocus() || document.hidden) {
                    isLocked = true;
                    hideContent();
                }
            }, 15);
        }
    });

    // 2. Window focus regain
    window.addEventListener('focus', function() {
        if (!isModalOpen()) return;
        isLocked = false;
        setTimeout(function() {
            if (document.hasFocus() && !document.hidden && isMouseInsideModal) {
                showContent();
            }
        }, 30);
    });

    // 3. Document visibility change (tab minimize/switch)
    document.addEventListener('visibilitychange', function() {
        if (!isModalOpen()) return;
        if (document.hidden) {
            hideContent();
        } else if (!isLocked && document.hasFocus() && isMouseInsideModal) {
            showContent();
        }
    });

    // 4. Mouse movement only restores if window GENUINELY has focus & mouse is inside modal
    window.addEventListener('mousemove', function(e) {
        if (!isModalOpen()) return;
        if (!document.hasFocus() || document.hidden || isLocked) {
            return;
        }
        // Verify cursor is inside modal content rect
        if (modalContent) {
            var rect = modalContent.getBoundingClientRect();
            if (e.clientX >= rect.left && e.clientX <= rect.right && e.clientY >= rect.top && e.clientY <= rect.bottom) {
                isMouseInsideModal = true;
                var mb = getModalBody();
                if (mb && (mb.style.visibility === 'hidden' || mb.style.display === 'none')) {
                    showContent();
                }
            } else if (isMouseInsideModal) {
                isMouseInsideModal = false;
                hideContent();
            }
        }
    });

    // 5. Mouse leaving the browser window
    document.addEventListener('mouseleave', function() {
        if (!isModalOpen()) return;
        isMouseInsideModal = false;
        hideContent();
    });

    // 6. Screenshot & modifier keyboard interception
    window.addEventListener('keydown', function(e) {
        if (!isModalOpen()) return;
        var key = e.key || '';
        var keyCode = e.keyCode || e.which || 0;

        var isPrintScreen = (key === 'PrintScreen' || keyCode === 44);
        var isModifier = (key === 'Meta' || key === 'OS' || keyCode === 91 || keyCode === 92 || e.shiftKey || e.ctrlKey || e.metaKey || e.altKey);

        if (isPrintScreen || isModifier) {
            lockAndHide(2500);
            if (isPrintScreen || (e.shiftKey && (e.metaKey || e.ctrlKey))) {
                try { e.preventDefault(); e.stopPropagation(); } catch(err) {}
            }
        }
    }, true);

    window.addEventListener('keyup', function(e) {
        if (!isModalOpen()) return;
        var key = e.key || '';
        var keyCode = e.keyCode || 0;
        if (key === 'PrintScreen' || keyCode === 44) {
            lockAndHide(2500);
            try { e.preventDefault(); e.stopPropagation(); } catch(err) {}
        }
    }, true);

    // 7. Prevent context menu, drag, copy
    modal.addEventListener('contextmenu', function(e) { e.preventDefault(); return false; }, true);
    modal.addEventListener('dragstart', function(e) { e.preventDefault(); return false; }, true);
    modal.addEventListener('selectstart', function(e) { e.preventDefault(); return false; }, true);
    modal.addEventListener('copy', function(e) { e.preventDefault(); wipeClip(); return false; }, true);

    // 8. Listen for messages from iframe
    window.addEventListener('message', function(e) {
        if (!e.data || e.data.type !== 'SECURITY_EVENT') return;
        if (e.data.action === 'LOCK') {
            lockAndHide(2500);
        }
    });
})();
</script>


<script type="text/javascript">
function confirm_modal(delete_url)
{
    jQuery('#modal-4').modal('show', {backdrop: 'static'});
    document.getElementById('delete_link').setAttribute('href' , delete_url);
}
</script>

<!-- (Normal Modal)-->
<div class="modal fade" id="modal-4">
    <div class="modal-dialog">
        <div class="modal-content" style="margin-top:100px;">

            <div class="modal-header">
                <h5 class="modal-title text-center"><?php echo site_phrase('are_you_sure'); ?> ?</h5>
                <button type="button" class="btn btn-outline-secondary px-1 py-0" data-bs-dismiss="modal" aria-hidden="true"><i class="fas fa-times-circle"></i></button>
            </div>


            <div class="modal-footer" style="margin:0px; border-top:0px; text-align:center;">
                <a href="" class="btn btn-danger btn-yes" id="delete_link"><?php echo site_phrase('yes');?></a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo site_phrase('no');?></button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
function async_modal() {
    const asyncModal = new Promise(function(resolve, reject){
        $('#modal-4').modal('show');
        $('#modal-4 .btn-yes').click(function(){
            resolve(true);
        });
        $('#modal-4 .btn-cancel').click(function(){
            resolve(false);
        });
    });
    return asyncModal;
}
</script>
