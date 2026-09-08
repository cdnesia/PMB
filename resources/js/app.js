

import Alpine from 'alpinejs';

import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

import select2Init from 'select2/dist/js/select2.js';

// select2 4.0.13 (CommonJS) mengekspor factory `function(root, jQuery)`,
// jadi harus dipanggil eksplisit dengan instance jQuery kita.
const init = (typeof select2Init === 'function')
    ? select2Init
    : (select2Init && select2Init.default);

if (typeof init === 'function') {
    init(window, jQuery);
}

window.Alpine = Alpine;

/**
 * Helper untuk memunculkan notifikasi dari mana saja:
 *   window.notify('success', 'Data tersimpan');
 */
window.notify = function (type, message) {
    window.dispatchEvent(new CustomEvent('notify', { detail: { type, message } }));
};

Alpine.data('toast', (initial = []) => ({
    items: initial.map((i) => ({
        id: Math.random().toString(36).slice(2) + Date.now(),
        type: i.type || 'info',
        message: i.message,
        show: false,
    })),

    init() {
        this.items.forEach((item, index) => {
            setTimeout(() => (item.show = true), 100 * index);
            setTimeout(() => this.dismiss(item.id), 6000 + 200 * index);
        });
    },

    push(detail) {
        const item = {
            id: Math.random().toString(36).slice(2) + Date.now(),
            type: detail?.type || 'info',
            message: detail?.message || '',
            show: false,
        };
        this.items.push(item);
        this.$nextTick(() => (item.show = true));
        setTimeout(() => this.dismiss(item.id), 6000);
    },

    dismiss(id) {
        const item = this.items.find((i) => i.id === id);
        if (!item) return;
        item.show = false;
        setTimeout(() => {
            this.items = this.items.filter((i) => i.id !== id);
        }, 300);
    },

    icon(type) {
        const base = 'h-5 w-5';
        const paths = {
            success:
                '<svg class="' + base + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
            error:
                '<svg class="' + base + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
            warning:
                '<svg class="' + base + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>',
            info:
                '<svg class="' + base + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>',
        };
        return paths[type] || paths.info;
    },

    typeClasses(type) {
        const map = {
            success: {
                icon: 'text-emerald-500',
                bar: 'bg-emerald-500',
            },
            error: {
                icon: 'text-red-500',
                bar: 'bg-red-500',
            },
            warning: {
                icon: 'text-amber-500',
                bar: 'bg-amber-500',
            },
            info: {
                icon: 'text-sky-500',
                bar: 'bg-sky-500',
            },
        };
        return map[type] || map.info;
    },
}));

/**
 * Alpine.data untuk lonceng notifikasi di header admin. Daftar awal & jumlah
 * belum-dibaca dirender dari server (Blade); komponen ini hanya menangani
 * buka/tutup dropdown dan menandai dibaca lewat AJAX tanpa reload halaman.
 */
Alpine.data('notifBell', (options = {}) => ({
    open: false,
    items: options.items || [],
    unreadCount: options.unreadCount || 0,
    markAllUrl: options.markAllUrl,

    async markRead(item) {
        if (!item.unread) {
            window.location.href = item.url;
            return;
        }

        try {
            await fetch(item.markUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        } catch (e) {
            // Tetap arahkan pengguna meski penandaan gagal — tidak boleh memblokir navigasi.
        }

        item.unread = false;
        this.unreadCount = Math.max(0, this.unreadCount - 1);
        window.location.href = item.url;
    },

    async markAllRead() {
        if (this.unreadCount === 0) return;

        try {
            await fetch(this.markAllUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            this.items.forEach((item) => (item.unread = false));
            this.unreadCount = 0;
        } catch (e) {
            window.notify('error', 'Gagal menandai semua notifikasi, coba lagi.');
        }
    },
}));

/**
 * Directive Alpine `x-select2` — inisialisasi Select2 pada <select>,
 * tetap kompatibel dengan `x-model`, `@change`, dan `x-for` (opsi dinamis).
 */
document.addEventListener('alpine:init', () => {
    Alpine.directive('select2', (el, { expression }, { evaluate, cleanup }) => {
        const $el = jQuery(el);
        let building = false;

        const options = () => {
            const cfg = expression ? evaluate(expression) : {};
            return {
                width: '100%',
                minimumResultsForSearch: 8,
                placeholder: $el.find('option[value=""]').first().text().trim() || undefined,
                ...(cfg || {}),
            };
        };

        const mount = () => {
            if (building) return;
            building = true;
            const val = $el.val();
            if ($el.data('select2')) {
                $el.select2('destroy');
            }
            $el.select2(options());
            if (val) {
                $el.val(val);
            }
            building = false;
        };

        // Forward seleksi Select2 -> event `change` native agar x-model/@change tetap jalan.
        $el.on('select2:select select2:unselect', () => {
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // Rebuild saat opsi berubah (mis. x-for Alpine).
        const observer = new MutationObserver(() => {
            if (building) return;
            clearTimeout(el.__select2Timer);
            el.__select2Timer = setTimeout(mount, 0);
        });
        observer.observe(el, { childList: true, subtree: true });
        cleanup(() => {
            clearTimeout(el.__select2Timer);
            observer.disconnect();
            if ($el.data('select2')) $el.select2('destroy');
        });

        // Inisialisasi setelah Alpine merender opsi (x-for).
        setTimeout(mount, 0);
    });
});

/**
 * Ambil ulang isi tabel CRUD (elemen dengan id="crud-table") dari halaman
 * saat ini via AJAX, lalu tukar ke DOM tanpa reload penuh. Dipakai setelah
 * tambah/edit/hapus data lewat modal supaya tabel & paginasi selalu akurat
 * tanpa menduplikasi logika render tabel di JavaScript.
 */
window.refreshCrudTable = async function () {
    const current = document.getElementById('crud-table');
    if (! current) return;

    try {
        const res = await fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const html = await res.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const fresh = doc.getElementById('crud-table');

        if (fresh) {
            current.replaceWith(fresh);
            window.Alpine.initTree(fresh);
        } else {
            window.location.reload();
        }
    } catch (e) {
        window.location.reload();
    }
};

/**
 * Serialisasi nilai (skalar, boolean, array, atau objek/array-of-objects
 * bersarang) ke FormData dengan penamaan gaya Laravel: `key[i][sub]` untuk
 * array/objek bersarang, supaya request()->validate(['x.*.y' => ...]) di
 * backend tetap jalan seperti form HTML biasa.
 */
function appendToFormData(body, key, value) {
    if (value === null || value === undefined) return;
    if (typeof value === 'boolean') {
        body.append(key, value ? '1' : '0');
        return;
    }
    if (Array.isArray(value)) {
        value.forEach((v, i) => appendToFormData(body, `${key}[${i}]`, v));
        return;
    }
    if (typeof value === 'object') {
        Object.entries(value).forEach(([k, v]) => appendToFormData(body, `${key}[${k}]`, v));
        return;
    }
    body.append(key, value);
}

/**
 * Alpine.data generik untuk modal tambah/edit lewat AJAX. Setiap halaman
 * CRUD memakai `x-data="crudModal({ storeUrl, defaults })"` lalu memanggil
 * openCreate()/openEdit(data) dari tombol Tambah/Edit di tabel. Opsional
 * `transform(mode, form)` dipakai saat bentuk payload create/update berbeda
 * dari bentuk state form (mis. create menerima banyak baris sekaligus).
 * Key tambahan apa pun di `options` (mis. method khusus halaman seperti
 * addRow/removeRow) ikut disebar ke state komponen agar bisa dipanggil dari
 * template modal halaman tersebut.
 */
Alpine.data('crudModal', (options = {}) => ({
    ...options,
    show: false,
    mode: 'create',
    storeUrl: options.storeUrl,
    defaults: options.defaults || {},
    transformFn: options.transform || null,
    form: {},
    errors: {},
    submitting: false,

    openCreate() {
        this.mode = 'create';
        this.form = JSON.parse(JSON.stringify(this.defaults));
        this.errors = {};
        this.show = true;
    },

    openEdit(data) {
        this.mode = 'edit';
        this.form = { ...JSON.parse(JSON.stringify(this.defaults)), ...data };
        this.errors = {};
        this.show = true;
    },

    error(field) {
        return this.errors?.[field]?.[0] || null;
    },

    async submit() {
        this.submitting = true;
        this.errors = {};

        const url = this.mode === 'edit' ? this.form._updateUrl : this.storeUrl;
        const body = new FormData();
        const payload = typeof this.transformFn === 'function' ? this.transformFn(this.mode, this.form) : this.form;

        Object.entries(payload).forEach(([key, value]) => {
            if (key === '_updateUrl') return;
            appendToFormData(body, key, value);
        });

        if (this.mode === 'edit') {
            body.append('_method', 'PUT');
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                body,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await res.json().catch(() => ({}));

            if (res.ok) {
                this.show = false;
                window.notify('success', data.message || 'Data berhasil disimpan.');
                await window.refreshCrudTable();
            } else if (res.status === 422) {
                this.errors = data.errors || {};
                Object.values(this.errors).flat().forEach((msg) => window.notify('error', msg));
            } else {
                window.notify('error', data.message || 'Terjadi kesalahan, silakan coba lagi.');
            }
        } catch (e) {
            window.notify('error', 'Terjadi kesalahan jaringan, silakan coba lagi.');
        } finally {
            this.submitting = false;
        }
    },
}));

Alpine.start();
