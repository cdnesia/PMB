/**
 * Halaman pengerjaan ujian CBT: countdown timer, autosave jawaban, navigasi soal,
 * dan pencatatan pelanggaran integritas (pindah tab, keluar fullscreen, copy/paste, klik kanan).
 * Vanilla JS (bukan Alpine) karena timer & anti-cheat butuh kontrol penuh & harus tetap jalan
 * meski elemen di-render ulang.
 */
(function () {
    const configEl = document.getElementById('cbt-config');
    if (!configEl) return;

    const config = JSON.parse(configEl.textContent);
    const deadline = new Date(config.deadlineAt).getTime();

    const panels = Array.from(document.querySelectorAll('.cbt-soal-panel'));
    const navItems = Array.from(document.querySelectorAll('.cbt-nav-item'));
    let currentIndex = 0;
    let submitted = false;

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
            },
            body: JSON.stringify(body),
        });
    }

    function submitForm() {
        if (submitted) return;
        submitted = true;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = config.submitUrl;
        form.innerHTML = '<input type="hidden" name="_token" value="' + config.csrfToken + '">';
        document.body.appendChild(form);
        form.submit();
    }

    // ----- Navigasi antar soal -----
    function showPanel(index) {
        if (index < 0 || index >= panels.length) return;
        currentIndex = index;
        panels.forEach((p, i) => (p.style.display = i === index ? '' : 'none'));
        navItems.forEach((btn, i) => btn.classList.toggle('ring-2', i === index));
        navItems.forEach((btn, i) => btn.classList.toggle('ring-indigo-600', i === index));
    }

    navItems.forEach((btn) => {
        btn.addEventListener('click', () => showPanel(parseInt(btn.dataset.cbtNav, 10)));
    });

    panels.forEach((panel) => {
        const prev = panel.querySelector('[data-cbt-prev]');
        const next = panel.querySelector('[data-cbt-next]');
        if (prev) prev.addEventListener('click', () => showPanel(currentIndex - 1));
        if (next) next.addEventListener('click', () => showPanel(currentIndex + 1));
    });

    // ----- Autosave jawaban -----
    const saveTimers = {};

    function updateNavState(soalId) {
        const panel = document.querySelector('.cbt-soal-panel:has([data-soal-id="' + soalId + '"])');
        if (!panel) return;
        const index = parseInt(panel.dataset.cbtPanel, 10);
        const btn = navItems[index];
        if (!btn) return;

        const checked = panel.querySelector('.cbt-jawaban-input:checked');
        const ragu = panel.querySelector('.cbt-ragu-input:checked');

        btn.classList.remove('bg-indigo-600', 'text-white', 'bg-amber-400', 'border-gray-200');
        if (checked) {
            btn.classList.add('bg-indigo-600', 'text-white');
        } else if (ragu) {
            btn.classList.add('bg-amber-400', 'text-white');
        } else {
            btn.classList.add('border-gray-200');
        }
    }

    function saveJawaban(soalId) {
        clearTimeout(saveTimers[soalId]);
        saveTimers[soalId] = setTimeout(() => {
            const panel = document.querySelector('.cbt-soal-panel:has([data-soal-id="' + soalId + '"])');
            const checked = panel.querySelector('.cbt-jawaban-input:checked');
            const ragu = panel.querySelector('.cbt-ragu-input:checked');

            post(config.jawabUrl, {
                cbt_soal_id: soalId,
                jawaban: checked ? checked.value : null,
                ragu_ragu: !!ragu,
            });

            updateNavState(soalId);
        }, 300);
    }

    document.querySelectorAll('.cbt-jawaban-input').forEach((input) => {
        input.addEventListener('change', () => saveJawaban(input.dataset.soalId));
    });
    document.querySelectorAll('.cbt-ragu-input').forEach((input) => {
        input.addEventListener('change', () => saveJawaban(input.dataset.soalId));
    });
    document.querySelectorAll('.cbt-jawaban-input:checked, .cbt-ragu-input:checked').forEach((input) => {
        updateNavState(input.dataset.soalId);
    });

    // ----- Countdown & auto-submit -----
    const countdownEl = document.getElementById('cbt-countdown');

    function tickCountdown() {
        const remaining = deadline - Date.now();

        if (remaining <= 0) {
            countdownEl.textContent = '00:00:00';
            clearInterval(timer);
            submitForm();
            return;
        }

        const totalSeconds = Math.floor(remaining / 1000);
        const h = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
        const m = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
        const s = String(totalSeconds % 60).padStart(2, '0');
        countdownEl.textContent = `${h}:${m}:${s}`;
    }

    tickCountdown();
    const timer = setInterval(tickCountdown, 1000);

    // ----- Pencatatan pelanggaran (throttled agar tidak membanjiri server) -----
    const lastReport = {};

    function reportPelanggaran(jenis, keterangan) {
        const now = Date.now();
        if (lastReport[jenis] && now - lastReport[jenis] < 4000) return;
        lastReport[jenis] = now;

        post(config.pelanggaranUrl, { jenis, keterangan: keterangan || null });
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden && !submitted) {
            reportPelanggaran('pindah_tab');
        }
    });

    window.addEventListener('blur', () => {
        if (!submitted) reportPelanggaran('pindah_tab', 'window blur');
    });

    document.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        reportPelanggaran('klik_kanan');
    });

    document.addEventListener('copy', () => reportPelanggaran('copy_paste', 'copy'));
    document.addEventListener('paste', () => reportPelanggaran('copy_paste', 'paste'));

    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement && !submitted) {
            reportPelanggaran('keluar_fullscreen');
        }
    });

    const fullscreenBtn = document.getElementById('cbt-fullscreen');
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', () => {
            document.documentElement.requestFullscreen?.().catch(() => {});
        });
    }

    window.addEventListener('beforeunload', (e) => {
        if (submitted) return;
        e.preventDefault();
        e.returnValue = '';
    });
})();
