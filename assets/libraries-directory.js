(function () {
    'use strict';
    const form = document.getElementById('libraries-filters');
    if (!form) return;
    const type = form.elements.type_id, subtype = form.elements.subtype_id;
    function cascade() {
        Array.from(subtype.options).forEach(function (option) {
            const incompatible = !!(option.value && type.value && option.dataset.type !== type.value);
            option.hidden = incompatible;
            option.disabled = incompatible;
            if (incompatible && option.selected) subtype.value = '';
        });
    }
    cascade();
    document.querySelectorAll('.library-filter-tabs').forEach(function (tabs) {
        const active = tabs.querySelector('.active');
        if (active) tabs.scrollLeft += active.getBoundingClientRect().left - tabs.getBoundingClientRect().left - 8;
    });
    type.addEventListener('change', function () { cascade(); form.requestSubmit(); });
    subtype.addEventListener('change', function () {
        if (subtype.value) type.value = subtype.selectedOptions[0].dataset.type;
        form.requestSubmit();
    });
    const dialog = document.getElementById('library-profile-dialog');
    const profiles = JSON.parse(document.getElementById('libraries-profile-data').textContent);
    const fields = document.getElementById('library-profile-fields');
    const labels = {
        iplm_population_status: 'Seleksi populasi IPLM', code: 'Kode / NPSN', npp: 'NPP', type_name: 'Jenis perpustakaan', subtype_name: 'Subjenis',
        institution_status: 'Status institusi', address: 'Alamat', district: 'Kecamatan', village: 'Desa / kelurahan',
        manager_name: 'Nama PIC', phone: 'Telepon', email: 'Email', website: 'Website',
        opening_hours: 'Jam layanan', coordinates: 'Koordinat GPS', radius: 'Radius layanan',
        facilities: 'Fasilitas', description: 'Deskripsi'
    };
    document.querySelectorAll('[data-library-profile]').forEach(function (button) {
        button.addEventListener('click', function () {
            const profile = profiles[button.dataset.libraryProfile];
            if (!profile) return;
            document.getElementById('library-profile-title').textContent = profile.name;
            document.getElementById('library-profile-institution').textContent = profile.institution_name || 'Institusi belum diisi';
            document.getElementById('library-profile-badges').textContent =
                ({active: 'Aktif', pending: 'Pending', inactive: 'Nonaktif'}[profile.status] || profile.status) +
                ' · ' + (Number(profile.is_verified) === 1 ? 'Terverifikasi' : 'Perlu verifikasi');
            const data = Object.assign({}, profile, {
                iplm_population_status: {pending:'Belum dipilih',included:'Dipilih sesuai kewenangan',excluded:'Dikecualikan'}[profile.iplm_population_status],
                district: profile.district_name || profile.district,
                village: profile.village_name || profile.village,
                institution_status: {negeri: 'Negeri', swasta: 'Swasta', belum_diketahui: 'Belum diketahui'}[profile.institution_status],
                coordinates: Number(profile.latitude) || Number(profile.longitude) ? profile.latitude + ', ' + profile.longitude : 'Belum tersedia',
                radius: profile.service_radius_meters == null ? '' : profile.service_radius_meters + ' meter'
            });
            fields.replaceChildren();
            Object.keys(labels).forEach(function (key) {
                const group = document.createElement('div'), term = document.createElement('dt'), value = document.createElement('dd');
                term.textContent = labels[key]; value.textContent = data[key] || '—';
                group.append(term, value); fields.append(group);
            });
            const survey = document.getElementById('library-profile-survey');
            const schema = JSON.parse(document.getElementById('libraries-survey-fields').textContent);
            survey.replaceChildren();
            Object.keys(schema).forEach(function(key) {
                const spec = schema[key], group = document.createElement('div'), term = document.createElement('dt'), value = document.createElement('dd');
                const raw = (profile.survey.values || {})[key];
                term.textContent = spec[0];
                value.textContent = raw == null || raw === '' ? 'Belum diisi' : (spec[1] === 'boolean' ? ({yes:'Ya',no:'Tidak',unknown:'Belum diketahui'}[raw] || raw) : ((spec[2] || {})[raw] || raw));
                group.append(term,value);survey.append(group);
            });
            const iplm = document.getElementById('library-profile-iplm');
            iplm.replaceChildren();
            (profile.iplm || []).forEach(function(record) {
                const details = document.createElement('details'), summary = document.createElement('summary'), dl = document.createElement('dl');
                details.className = 'library-profile-section';dl.className = 'library-profile-fields';
                summary.textContent = record.period + ' · ' + ({draft:'Draf',revision:'Perlu revisi',submitted:'Terkirim',verified:'Terverifikasi'}[record.status] || record.status);
                Object.keys(record.values || {}).forEach(function(key) {
                    const group = document.createElement('div'), term = document.createElement('dt'), value = document.createElement('dd');
                    const schema = Array.isArray(record.schema) ? record.schema : Object.values(record.schema || {});
                    const field = schema.find(f => f.code === key);
                    term.textContent = field ? field.label : key;
                    value.textContent = record.values[key] === '' ? 'Belum diisi' : String(record.values[key]);
                    if ((record.evidence || {})[key]) value.textContent += '\nBukti: ' + record.evidence[key];
                    group.append(term,value);dl.append(group);
                });
                details.append(summary,dl);iplm.append(details);
            });
            dialog.showModal();
        });
    });
    document.querySelectorAll('[data-profile-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', function (event) {
        const bounds = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) dialog.close();
    });
    // A body-level tooltip stays visible outside the horizontally scrolling table.
    const tooltip = document.createElement('div');
    tooltip.className = 'library-action-tooltip'; tooltip.id = 'library-action-tooltip'; tooltip.role = 'tooltip'; tooltip.hidden = true;
    document.body.append(tooltip);
    const hide = () => { tooltip.hidden = true; };
    document.querySelectorAll('.library-directory-actions [title]').forEach(function (button) {
        const label = button.title; button.removeAttribute('title');
        button.setAttribute('aria-describedby', tooltip.id);
        function show() {
            tooltip.textContent = label; tooltip.hidden = false;
            const rect = button.getBoundingClientRect();
            tooltip.style.left = Math.max(8, Math.min(innerWidth - tooltip.offsetWidth - 8, rect.left + rect.width / 2 - tooltip.offsetWidth / 2)) + 'px';
            tooltip.style.top = Math.max(8, rect.top - tooltip.offsetHeight - 8) + 'px';
        }
        button.addEventListener('mouseenter', show); button.addEventListener('focus', show);
        ['mouseleave', 'blur', 'click'].forEach(event => button.addEventListener(event, hide));
    });
    window.addEventListener('scroll', hide, true); window.addEventListener('resize', hide);
})();
