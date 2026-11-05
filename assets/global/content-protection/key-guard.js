/*
 * Key guard for protected course content.
 *
 * Blocks:  Ctrl/Cmd + P  (print, incl. Ctrl+Shift+P)
 *          Ctrl/Cmd + S  (save page)
 *          PrintScreen   (cancels the key and clears the clipboard so a
 *                         clipboard screenshot cannot be pasted)
 *          window.print()
 *
 * Usage:
 *   <script src=".../key-guard.js" data-always="1"></script>   // always on (player, PDF reader)
 *   <script src=".../key-guard.js"></script>
 *   <script>OESKeyGuard.add(function () { return isModalOpen(); });</script>   // on only when the callback is true
 *
 * Note: Windows handles Win+Shift+S, Win+PrtScn and Snipping Tool itself,
 * before the browser sees the key, so no page script can stop those.
 */
(function (w, d) {
    'use strict';

    var currentScript = d.currentScript;
    var api = w.OESKeyGuard;

    if (!api) {
        var guards = [];

        var isActive = function () {
            for (var i = 0; i < guards.length; i++) {
                try {
                    if (guards[i]()) return true;
                } catch (err) {}
            }
            return false;
        };

        var isPrintScreen = function (e) {
            return e.key === 'PrintScreen' || e.code === 'PrintScreen' || e.keyCode === 44;
        };

        var isPrintOrSave = function (e) {
            if (!(e.ctrlKey || e.metaKey)) return false;
            var key = (e.key || '').toLowerCase();
            return key === 'p' || key === 's' ||
                e.code === 'KeyP' || e.code === 'KeyS' ||
                e.keyCode === 80 || e.keyCode === 83;
        };

        var cancel = function (e) {
            try {
                e.preventDefault();
                e.stopPropagation();
            } catch (err) {}
            return false;
        };

        var overwriteClipboard = function () {
            var text = ' ';
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).catch(function () {});
                    return;
                }
            } catch (err) {}
            try {
                var ta = d.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', '');
                ta.style.position = 'fixed';
                ta.style.top = '-1000px';
                d.body.appendChild(ta);
                ta.select();
                d.execCommand('copy');
                d.body.removeChild(ta);
            } catch (err) {}
        };

        // Windows writes the PrtScn image to the clipboard a moment after the
        // key event, so overwrite it now and a few more times shortly after.
        var scrubClipboard = function () {
            overwriteClipboard();
            [100, 300, 700, 1200, 2000].forEach(function (ms) {
                setTimeout(overwriteClipboard, ms);
            });
        };

        var onKeyDown = function (e) {
            if (!isActive()) return;
            if (isPrintOrSave(e)) return cancel(e);
            if (isPrintScreen(e)) {
                scrubClipboard();
                return cancel(e);
            }
        };

        var onKeyUp = function (e) {
            if (!isActive()) return;
            if (isPrintScreen(e)) {
                scrubClipboard();
                return cancel(e);
            }
        };

        w.addEventListener('keydown', onKeyDown, true);
        w.addEventListener('keyup', onKeyUp, true);

        var nativePrint = w.print;
        w.print = function () {
            if (isActive()) return;
            return nativePrint.apply(w, arguments);
        };

        var printCssAdded = false;
        var addPrintCss = function () {
            if (printCssAdded) return;
            printCssAdded = true;
            var style = d.createElement('style');
            style.textContent = '@media print { html, body { display: none !important; } }';
            (d.head || d.documentElement).appendChild(style);
        };

        api = w.OESKeyGuard = {
            add: function (fn) {
                if (typeof fn === 'function') guards.push(fn);
            },
            always: function () {
                guards.push(function () { return true; });
                // Browser menu > Print cannot be cancelled from a page, so make it print a blank page.
                addPrintCss();
            }
        };
    }

    if (currentScript && currentScript.getAttribute('data-always') === '1') {
        api.always();
    }
})(window, document);
