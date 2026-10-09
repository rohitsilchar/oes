<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo get_settings('system_name'); ?> - Protected PDF Viewer</title>
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
        background-color: #0f172a;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        overflow: hidden;
    }

    #my_pdf_viewer {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        background-color: #0f172a;
        overflow: hidden;
    }
    
    #canvas_container {
        flex: 1;
        width: 100%;
        height: calc(100% - 50px);
        overflow: auto;
        background: #0f172a;
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
    }

    /* Navigation & Controls */
    #navigation_controls {
        background: #1e293b;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        z-index: 9990;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.3);
    }

    #go_previous, #go_next {
        width: 120px;
        height: 36px;
        background-color: #6366f1;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        color: #fff;
        font-size: 13.5px;
        font-weight: 600;
        transition: background-color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin: 0 6px;
    }

    #go_previous:hover, #go_next:hover {
        background-color: #4f46e5;
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
        border: 1px solid rgba(255, 255, 255, 0.25);
        background-color: #0f172a;
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
        background: rgba(30, 41, 59, 0.92);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        padding: 4px 8px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .zoom-btn {
        background: #334155;
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
        background: #475569;
    }

    .zoom-btn:active {
        transform: scale(0.95);
    }
  </style>

  <script src="<?php echo site_url('assets/global/pdf-canvas/'); ?>pdf.min.js"></script>
  <script src="<?php echo site_url('assets/global/pdf-canvas/'); ?>pdf.worker.min.js"></script>
</head>
<body>

    <!-- PDF Viewer Root -->
    <div id="my_pdf_viewer">
        <div id="canvas_container">
            <canvas id="pdf_renderer"></canvas>
        </div>
 
        <div id="navigation_controls">
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
           PDF.JS RENDERING ENGINE & CONTROLS
           ========================================================================= */
        var myState = {
            pdf: null,
            currentPage: 1,
            zoom: 1.4
        };

        var currentRenderTask = null;

        pdfjsLib.getDocument('<?php echo site_url('files?course_id='.$course_id.'&lesson_id='.$lesson_id.'&type=image'); ?>').then(function(pdf) {
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

            // Cancel any previous render still executing
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
                    if (err && err.name === 'RenderingCancelledException') {
                        return;
                    }
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