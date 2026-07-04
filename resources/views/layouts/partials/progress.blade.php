{{-- Global progress/loading indicator for save / delete / process actions.
     Shows on every navigating <form> submit and clears when the next page loads.
     AJAX forms (that call preventDefault) are skipped — they manage their own spinner.
     Opt out per form with data-no-progress. Trigger manually with window.AppProgress.show()/hide(). --}}
<div id="app-progress-bar" aria-hidden="true"></div>
<div id="app-progress-overlay" role="status" aria-live="polite" aria-hidden="true">
    <div class="app-progress-card">
        <span class="app-spin app-spin-lg"></span>
        <span class="app-progress-text">{{ __('Processing...') }}</span>
    </div>
</div>

<style>
    #app-progress-bar {
        position: fixed; top: 0; left: 0; height: 3px; width: 0;
        background: #2563eb; box-shadow: 0 0 8px rgba(37, 99, 235, .6);
        z-index: 10000; opacity: 0; pointer-events: none;
    }
    #app-progress-bar.active { opacity: 1; width: 92%; transition: width 12s cubic-bezier(.1,.85,.25,1); }
    #app-progress-bar.done   { opacity: 0; width: 100%; transition: width .2s ease, opacity .3s ease .15s; }

    #app-progress-overlay {
        position: fixed; inset: 0; z-index: 9999; display: none;
        align-items: center; justify-content: center;
        background: rgba(15, 23, 42, .18); backdrop-filter: blur(1px);
    }
    #app-progress-overlay.active { display: flex; }
    .app-progress-card {
        display: flex; align-items: center; gap: .75rem;
        background: #fff; color: #334155;
        padding: .85rem 1.25rem; border-radius: 1rem;
        border: 1px solid #e2e8f0; box-shadow: 0 10px 30px -12px rgba(15,23,42,.25);
        font-size: .875rem; font-weight: 500;
    }
    .app-spin {
        display: inline-block; width: 1em; height: 1em;
        border: 2px solid currentColor; border-top-color: transparent;
        border-radius: 50%; animation: app-spin .6s linear infinite;
        vertical-align: -0.15em;
    }
    .app-spin-lg { width: 1.35rem; height: 1.35rem; border-width: 2.5px; color: #2563eb; }
    @keyframes app-spin { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) {
        #app-progress-bar.active { transition: none; }
        .app-spin { animation-duration: 1.2s; }
    }
</style>

<script>
(function () {
    var bar = document.getElementById('app-progress-bar');
    var overlay = document.getElementById('app-progress-overlay');
    var failsafe;
    var LOADING_TEXT = @js(__('Processing...'));

    window.AppProgress = {
        show: function (withOverlay) {
            if (bar) {
                bar.classList.remove('done');
                bar.classList.remove('active');
                void bar.offsetWidth;           // restart animation
                bar.classList.add('active');
            }
            if (overlay && withOverlay !== false) overlay.classList.add('active');
        },
        hide: function () {
            if (bar) {
                bar.classList.remove('active');
                bar.classList.add('done');
                setTimeout(function () { bar.classList.remove('done'); }, 500);
            }
            if (overlay) overlay.classList.remove('active');
            clearTimeout(failsafe);
        }
    };

    // Fresh page load / back-forward cache restore → make sure nothing is stuck on.
    window.addEventListener('pageshow', function () { window.AppProgress.hide(); });

    function setButtonLoading(btn) {
        if (!btn || btn.dataset.appLoading) return;
        btn.dataset.appLoading = '1';
        btn.dataset.appOriginal = btn.innerHTML;
        var text = btn.getAttribute('data-loading-text') || LOADING_TEXT;
        btn.innerHTML = '<span class="app-spin"></span><span>' + text + '</span>';
        // disable after submit value is captured so the form still posts the button
        setTimeout(function () { btn.disabled = true; }, 0);
    }

    // Any navigating form submit → show progress.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        if (e.defaultPrevented) return;              // AJAX form handled itself
        if (form.hasAttribute('data-no-progress')) return;

        window.AppProgress.show();
        setButtonLoading(e.submitter || form.querySelector('button[type="submit"], input[type="submit"]'));

        // Failsafe: if navigation never happens (e.g. blocked), auto-clear.
        clearTimeout(failsafe);
        failsafe = setTimeout(function () { window.AppProgress.hide(); }, 30000);
    }, false);

    // Explicit opt-in trigger for JS-driven actions: add data-progress to any element.
    document.addEventListener('click', function (e) {
        var el = e.target.closest ? e.target.closest('[data-progress]') : null;
        if (el) window.AppProgress.show();
    }, false);
})();
</script>
