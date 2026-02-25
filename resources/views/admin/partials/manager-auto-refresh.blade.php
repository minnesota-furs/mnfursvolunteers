{{-- Auto-refresh progress bar (visual) + 60-second reload script --}}
<div class="auto-refresh-bar mb-6">
    <div class="auto-refresh-fill" id="refresh-bar"></div>
</div>

<script>
    (function () {
        const bar = document.getElementById('refresh-bar');

        function startBar() {
            bar.classList.remove('animating');
            void bar.offsetWidth; // force reflow to restart transition
            bar.classList.add('animating');
        }

        startBar();
        setTimeout(() => window.location.reload(), 60_000);
    })();
</script>
