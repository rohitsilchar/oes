<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo get_settings('system_name'); ?> - Protected Document Viewer</title>
  <link rel="icon" href="<?php echo base_url('uploads/system/'.get_frontend_settings('favicon')); ?>" type="image/x-icon">
  <style>
    * {
        box-sizing: border-box;
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
        -webkit-touch-callout: none !important;
    }

    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        background-color: #0b0f19;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        overflow: hidden;
    }

    #my_pdf_viewer {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        background-color: #0b0f19;
        overflow: hidden;
    }
    
    #canvas_container {
        flex: 1;
        width: 100%;
        height: calc(100% - 50px);
        overflow: auto;
        background: #0b0f19;
        text-align: center;
        padding: 20px 10px 80px 10px;
        position: relative;
    }

    #pdf_renderer {
        display: inline-block;
        margin: 0 auto;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 2px 8px rgba(0, 0, 0, 0.3);
        border-radius: 4px;
        background-color: #ffffff;
        vertical-align: middle;
        pointer-events: none;
    }

    /* Screen Capture Protection Shield Overlay */
    #screen_shield {
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        background: #0b0f19;
        z-index: 2147483647;
        pointer-events: none;
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        text-align: center;
        padding: 24px;
        color: #f87171;
    }

    #screen_shield .shield-icon {
        width: 52px;
        height: 52px;
        margin-bottom: 14px;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ef4444;
    }

    #screen_shield .shield-title {
        font-size: 17px;
        font-weight: 700;
        color: #f87171;
        margin-bottom: 6px;
    }

    #screen_shield .shield-desc {
        font-size: 13px;
        color: #94a3b8;
    }

    /* Print Protection */
    @media print {
        html, body, #my_pdf_viewer, #canvas_container, #pdf_renderer, #navigation_controls, #zoom_controls {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            height: 0 !important;
            width: 0 !important;
        }
        body::before {
            content: "Printing and screen capture are strictly prohibited for this document." !important;
            display: block !important;
            color: #ef4444 !important;
            font-size: 24px !important;
            font-weight: bold !important;
            text-align: center !important;
            padding-top: 80px !important;
            font-family: sans-serif !important;
        }
    }

    /* Navigation & Controls */
    #navigation_controls {
        background: #111827;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        z-index: 9990;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.3);
    }

    #go_previous, #go_next {
        width: 120px;
        height: 36px;
        background-color: #4f46e5;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        transition: background-color 0.15s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin: 0 6px;
    }

    #go_previous:hover, #go_next:hover {
        background-color: #4338ca;
    }

    .page-indicator-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        gap: 6px;
    }

    #current_page {
        height: 34px;
        width: 54px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background-color: #1f2937;
        text-align: center;
        font-weight: 700;
        font-size: 14px;
        color: #fff;
        border-radius: 6px;
        outline: none;
    }

    #current_page:focus {
        border-color: #6366f1;
    }

    .total-pages {
        font-size: 13.5px;
        color: #94a3b8;
        font-weight: 500;
    }

    #zoom_controls {
        position: fixed;
        bottom: 60px;
        right: 18px;
        z-index: 9992;
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(17, 24, 39, 0.92);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        padding: 4px 8px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
    }

    .zoom-btn {
        background: #374151;
        color: #f8fafc;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, transform 0.1s;
    }

    .zoom-btn:hover {
        background: #4b5563;
    }

    .zoom-btn:active {
        transform: scale(0.95);
    }
  </style>

  <?php
    $lesson_type = isset($lesson_type) ? $lesson_type : get_lesson_type($lesson_id);
    $is_image = ($lesson_type == 'image_file' || $lesson_type == 'wasabi_image_file');
    $file_stream_url = site_url('files?course_id='.$course_id.'&lesson_id='.$lesson_id.'&type=image');
  ?>

  <?php if (!$is_image) : ?>
  <script src="<?php echo site_url('assets/global/pdf-canvas/'); ?>pdf.min.js"></script>
  <script src="<?php echo site_url('assets/global/pdf-canvas/'); ?>pdf.worker.min.js"></script>
  <?php endif; ?>
</head>
<body oncontextmenu="return false;" onselectstart="return false;" ondragstart="return false;">

    <!-- Anti-Screenshot Dark Shield (Overlays during focus loss / window blur / screenshot attempt) -->
    <div id="screen_shield">
        <div class="shield-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        <div class="shield-title"><?php echo get_phrase('Protected Content'); ?></div>
        <div class="shield-desc"><?php echo get_phrase('Screenshots, screen recording, and print capture are prohibited for this document.'); ?></div>
    </div>

    <!-- Protected Document Canvas Viewer Root -->
    <div id="my_pdf_viewer">
        <div id="canvas_container">
            <canvas id="pdf_renderer"></canvas>
        </div>
 
        <div id="navigation_controls" <?php if ($is_image) echo 'style="display:none;"'; ?>>
            <button id="go_previous" title="<?php echo get_phrase('Previous Page'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                <span><?php echo get_phrase('Previous'); ?></span>
            </button>
            <div class="page-indicator-wrap">
                <input id="current_page" value="1" type="number" min="1" />
                <span class="total-pages">/ <span id="page_count">--</span></span>
            </div>
            <button id="go_next" title="<?php echo get_phrase('Next Page'); ?>">
                <span><?php echo get_phrase('Next'); ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
        </div>
 
        <div id="zoom_controls">  
            <button id="zoom_out" class="zoom-btn" title="<?php echo get_phrase('Zoom Out'); ?>">-</button>
            <button id="zoom_in" class="zoom-btn" title="<?php echo get_phrase('Zoom In'); ?>">+</button>
            <button id="fullscreen_btn" class="zoom-btn" title="<?php echo get_phrase('Toggle Fullscreen'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" height="14" viewBox="0 96 960 960" width="14" fill="currentColor">
                    <path d="M200 856V663h60v133h133v60H200Zm0-367V296h193v60H260v133h-60Zm367 367v-60h133V663h60v193H567Zm133-367V356H567v-60h193v193h-60Z"/>
                </svg>
            </button>
        </div>
    </div>

    <script>
        /* =========================================================================
           AIRTIGHT ANTI-SCREENSHOT & CONTENT PROTECTION ENGINE
           ========================================================================= */
        var container = document.getElementById("canvas_container");
        var shield = document.getElementById("screen_shield");
        var isLocked = false;
        var lockTimer = null;

        function wipeClipboard() {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText('').catch(function() {});
                }
            } catch(e) {}
        }

        function isFocused() {
            try {
                if (window.parent && window.parent.document) {
                    if (!window.parent.document.hasFocus() || window.parent.document.hidden) {
                        return false;
                    }
                }
            } catch(e) {}
            return document.hasFocus() && !document.hidden;
        }

        function hidePdf() {
            if (container) {
                container.style.visibility = "hidden";
                container.style.opacity = "0";
            }
            if (shield) shield.style.display = "flex";
        }

        function showPdf() {
            if (isLocked) return;
            if (!isFocused()) return;
            if (container) {
                container.style.visibility = "visible";
                container.style.opacity = "1";
            }
            if (shield) shield.style.display = "none";
        }

        function lockAndHide(duration) {
            isLocked = true;
            hidePdf();
            wipeClipboard();
            // Inform parent window
            try {
                if (window.parent) {
                    window.parent.postMessage({ type: 'SECURITY_EVENT', action: 'LOCK' }, '*');
                }
            } catch(e) {}

            if (lockTimer) clearTimeout(lockTimer);
            lockTimer = setTimeout(function() {
                isLocked = false;
                if (isFocused()) {
                    showPdf();
                }
            }, duration || 2500);
        }

        // 1. Loss of window focus: immediately hides document when Snipping Tool, Win+Shift+S, PrtScn, or external capture tools steal focus
        window.addEventListener('blur', function() {
            isLocked = true;
            hidePdf();
        });

        // 2. Tab or window visibility change (minimizing, Alt+Tab, etc.)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                isLocked = true;
                hidePdf();
            } else if (!isLocked && isFocused()) {
                showPdf();
            }
        });

        // 3. Regaining focus restores document
        window.addEventListener('focus', function() {
            isLocked = false;
            setTimeout(function() {
                if (isFocused()) {
                    showPdf();
                }
            }, 50);
        });

        // 4. Mouse movement only restores if window GENUINELY has focus
        // Crucial: Prevents Snipping Tool rectangle drag from exposing content
        window.addEventListener('mousemove', function() {
            if (!isLocked && isFocused()) {
                if (container && container.style.visibility === 'hidden') {
                    showPdf();
                }
            }
        });

        // 5. Mouse leaving viewer
        document.addEventListener('mouseleave', function() {
            hidePdf();
        });

        // 6. Two-way parent communication
        window.addEventListener('message', function(e) {
            if (!e.data || e.data.type !== 'SECURITY_EVENT') return;
            if (e.data.action === 'HIDE') {
                isLocked = true;
                hidePdf();
            } else if (e.data.action === 'SHOW') {
                isLocked = false;
                if (isFocused()) {
                    showPdf();
                }
            }
        });

        // 7. Intercept screenshot, print, and devtools keyboard shortcuts
        window.addEventListener('keydown', function(e) {
            var key = e.key || '';
            var code = e.code || '';
            var keyCode = e.keyCode || e.which || 0;

            // Allow typing numbers inside page input
            if (e.target && e.target.id === 'current_page') {
                if ((keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105)) return;
                if (keyCode === 13 || keyCode === 8 || keyCode === 46 || keyCode === 37 || keyCode === 39 || keyCode === 9) return;
            }

            var isPrintScreen = (key === 'PrintScreen' || code === 'PrintScreen' || keyCode === 44);
            var isModifierKey = (key === 'Meta' || key === 'OS' || keyCode === 91 || keyCode === 92 || e.shiftKey || e.ctrlKey || e.metaKey || e.altKey);

            if (isPrintScreen || isModifierKey) {
                lockAndHide(2500);
                if (isPrintScreen || (e.shiftKey && (e.metaKey || e.ctrlKey))) {
                    try { e.preventDefault(); e.stopPropagation(); } catch(err) {}
                }
            }
        }, true);

        window.addEventListener('keyup', function(e) {
            var key = e.key || '';
            var keyCode = e.keyCode || e.which || 0;
            if (key === 'PrintScreen' || keyCode === 44) {
                try { e.preventDefault(); e.stopPropagation(); } catch(err) {}
                lockAndHide(2500);
            }
        }, true);

        // 8. Print protection
        window.addEventListener('beforeprint', function() {
            hidePdf();
        });
        window.addEventListener('afterprint', function() {
            if (isFocused()) showPdf();
        });

        // 9. Disable context menu, text selection, drag & copy
        window.addEventListener('contextmenu', function(e) { e.preventDefault(); return false; }, true);
        window.addEventListener('dragstart', function(e) { e.preventDefault(); return false; }, true);
        window.addEventListener('selectstart', function(e) { e.preventDefault(); return false; }, true);
        window.addEventListener('copy', function(e) { e.preventDefault(); wipeClipboard(); return false; }, true);
        window.addEventListener('cut', function(e) { e.preventDefault(); wipeClipboard(); return false; }, true);

        /* =========================================================================
           PROTECTED CANVAS RENDERING ENGINE (PDF & IMAGE SUPPORT)
           ========================================================================= */
        <?php if ($is_image) : ?>
        // Image Canvas Renderer
        (function() {
            var canvas = document.getElementById("pdf_renderer");
            var ctx = canvas.getContext('2d');
            var img = new Image();
            img.crossOrigin = 'anonymous';
            var zoom = 1.0;

            function draw() {
                if (!img.naturalWidth) return;
                var baseWidth = Math.min(window.innerWidth - 30, img.naturalWidth);
                var scale = (baseWidth / img.naturalWidth) * zoom;
                canvas.width = img.naturalWidth * scale;
                canvas.height = img.naturalHeight * scale;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            }

            img.onload = function() {
                draw();
            };
            img.src = '<?php echo $file_stream_url; ?>';

            document.getElementById('zoom_in').addEventListener('click', function() {
                zoom += 0.2;
                draw();
            });

            document.getElementById('zoom_out').addEventListener('click', function() {
                if (zoom > 0.4) {
                    zoom -= 0.2;
                    draw();
                }
            });
        })();
        <?php else : ?>
        // PDF.js Canvas Renderer
        var myState = {
            pdf: null,
            currentPage: 1,
            zoom: 1.4
        };

        var currentRenderTask = null;

        pdfjsLib.getDocument('<?php echo $file_stream_url; ?>').then(function(pdf) {
            myState.pdf = pdf;
            var numPages = pdf.numPages || (pdf._pdfInfo && pdf._pdfInfo.numPages) || 1;
            var pageCountElem = document.getElementById("page_count");
            if (pageCountElem) pageCountElem.textContent = numPages;
            var curPageElem = document.getElementById("current_page");
            if (curPageElem) curPageElem.max = numPages;
            render();
        }).catch(function(err) {
            console.error("Failed to load PDF:", err);
        });

        function render() {
            if (!myState.pdf) return;

            if (currentRenderTask) {
                try {
                    currentRenderTask.cancel();
                } catch(e) {}
            }

            myState.pdf.getPage(myState.currentPage).then(function(page) {
                var canvas = document.getElementById("pdf_renderer");
                var ctx = canvas.getContext('2d');
                var viewport = page.getViewport(myState.zoom);

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                currentRenderTask = page.render({
                    canvasContext: ctx,
                    viewport: viewport
                });

                var renderPromise = (currentRenderTask && currentRenderTask.promise) ? currentRenderTask.promise : currentRenderTask;
                renderPromise.then(function() {
                    currentRenderTask = null;
                }).catch(function(err) {
                    if (err && err.name === 'RenderingCancelledException') return;
                    console.error("Render error:", err);
                });
            });
        }
        
        document.getElementById('go_previous').addEventListener('click', function(e) {
            if (myState.currentPage > 1) {
                myState.currentPage -= 1;
                document.getElementById("current_page").value = myState.currentPage;
                render();
                var c = document.getElementById("canvas_container");
                if (c) c.scrollTo(0, 0);
            }
        });

        document.getElementById('go_next').addEventListener('click', function(e) {
            var totalPages = myState.pdf ? (myState.pdf.numPages || (myState.pdf._pdfInfo && myState.pdf._pdfInfo.numPages) || 1) : 1;
            if (myState.currentPage < totalPages) {
                myState.currentPage += 1;
                document.getElementById("current_page").value = myState.currentPage;
                render();
                var c = document.getElementById("canvas_container");
                if (c) c.scrollTo(0, 0);
            }
        });

        document.getElementById('current_page').addEventListener('keypress', function(e) {
            if (myState.pdf == null) return;
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {
                var totalPages = myState.pdf.numPages || (myState.pdf._pdfInfo && myState.pdf._pdfInfo.numPages) || 1;
                var desiredPage = document.getElementById('current_page').valueAsNumber;
                if (desiredPage >= 1 && desiredPage <= totalPages) {
                    myState.currentPage = desiredPage;
                    document.getElementById("current_page").value = desiredPage;
                    render();
                }
            }
        });

        document.getElementById('zoom_in').addEventListener('click', function(e) {
            if (myState.pdf == null) return;
            myState.zoom += 0.2;
            render();
        });

        document.getElementById('zoom_out').addEventListener('click', function(e) {
            if (myState.pdf == null) return;
            if (myState.zoom > 0.6) {
                myState.zoom -= 0.2;
                render();
            }
        });
        <?php endif; ?>

        document.getElementById('fullscreen_btn').addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(function() {});
            } else if (document.exitFullscreen) {
                document.exitFullscreen().catch(function() {});
            }
        });
    </script>
</body>
</html>