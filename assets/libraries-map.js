(function () {
    'use strict';

    var rowSelect = document.getElementById('libraries-per-page');
    if (rowSelect) {
        rowSelect.addEventListener('change', function () { rowSelect.form.requestSubmit(); });
    }

    var source = document.getElementById('libraries-map-data');
    var container = document.getElementById(source && source.dataset.target || 'libraries-map');
    if (!container || !source) return;

    // All SVG paths and colors are application-owned, never profile HTML.
    var paths = {
        umum: ['M3 10h18L12 3 3 10Z', 'M5 10v9m5-9v9m4-9v9m5-9v9M3 21h18'],
        sekolah: ['m2 8 10-5 10 5-10 5L2 8Z', 'M6 10v6c3 3 9 3 12 0v-6M22 8v7'],
        swasta: ['M5 21V3h14v18M3 21h18', 'M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1M10 21v-3h4v3'],
        mitra: ['M12 5c-3-2-6-2-9-1v15c3-1 6-1 9 1 3-2 6-2 9-1V4c-3-1-6-1-9 1Z', 'M12 5v15M6 8h3M15 8h3'],
        pin: ['M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z', 'M15 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0'],
        user: ['M20 21v-2a7 7 0 0 0-14 0v2', 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0'],
        clock: ['M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0', 'M12 6v6l4 2'],
        arrow: ['m21 3-7 18-4-7-7-4 18-7Z', 'm10 14 5-5'],
        edit: ['m16 3 5 5-12 12-6 1 1-6L16 3Z', 'm14 5 5 5'],
        radius: ['M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0', 'M12 2v4m0 12v4M2 12h4m12 0h4']
    };
    var types = {
        umum: {color: '#2563eb', label: 'Perpustakaan Umum'},
        khusus: {color: '#9333ea', label: 'Perpustakaan Khusus'},
        perguruan_tinggi: {color: '#b45309', label: 'Perpustakaan Perguruan Tinggi'},
        sekolah: {color: '#15803d', label: 'Perpustakaan Sekolah'},
        swasta: {color: '#7c3aed', label: 'Perpustakaan Swasta'},
        mitra: {color: '#0e7490', label: 'Mitra Pojok Baca'},
        reading_point: {color: '#c2410c', label: 'Pojok Baca Digital'}
    };
    paths.khusus = paths.swasta;
    paths.perguruan_tinggi = paths.sekolah;
    var points = JSON.parse(source.textContent);
    points.forEach(function(point){
        if (typeof point.type_code !== 'string' || !/^[a-z0-9_]+$/.test(point.type_code)) return;
        if (!Object.prototype.hasOwnProperty.call(types, point.type_code)) types[point.type_code] = {color: '#475569', label: point.type || 'Perpustakaan'};
        if (typeof point.color === 'string' && /^#[0-9a-f]{6}$/i.test(point.color)) types[point.type_code].color = point.color;
    });
    function element(tag, className, text) {
        var node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = String(text);
        return node;
    }
    function icon(name) {
        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '1.8');
        svg.setAttribute('stroke-linecap', 'round');
        svg.setAttribute('stroke-linejoin', 'round');
        svg.setAttribute('aria-hidden', 'true');
        (paths[name] || paths.mitra).forEach(function (d) {
            var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', d);
            svg.appendChild(path);
        });
        return svg;
    }
    function styleFor(code) {
        return Object.prototype.hasOwnProperty.call(types, code) ? types[code] : {color: '#475569', label: 'Perpustakaan'};
    }
    function symbol(code, className) {
        var node = element('span', className);
        node.style.setProperty('--library-color', styleFor(code).color);
        node.appendChild(icon(Object.prototype.hasOwnProperty.call(types, code) ? code : 'mitra'));
        return node;
    }
    document.querySelectorAll('[data-library-type]').forEach(function (node) {
        node.replaceChildren(symbol(node.dataset.libraryType, 'library-type-symbol'));
    });

    if (!window.L || !L.markerClusterGroup) {
        container.textContent = 'Peta belum dapat dimuat. Muat ulang halaman atau periksa koneksi internet Anda.';
        return;
    }

    var canEdit = source.dataset.canEdit === '1';
    var publicView = source.dataset.public === '1';
    var statuses = {active: 'Aktif', pending: 'Pending', inactive: 'Nonaktif'};

    function infoBlock(label, value, iconName) {
        var block = element('div', 'library-info-block');
        var heading = element('dt', '');
        heading.appendChild(icon(iconName));
        heading.appendChild(element('span', '', label));
        block.appendChild(heading);
        block.appendChild(element('dd', '', value || 'Belum diisi'));
        return block;
    }
    function actionLink(label, iconName, href, className) {
        var link = element('a', className);
        link.href = href;
        link.appendChild(icon(iconName));
        link.appendChild(element('span', '', label));
        return link;
    }
    function popup(point) {
        var panel = element('section', 'library-map-popup');
        panel.style.setProperty('--library-color', styleFor(point.type_code).color);
        var header = element('header', 'library-popup-header');
        var category = element('div', 'library-popup-category');
        category.appendChild(symbol(point.type_code, 'library-popup-emblem'));
        var categoryText = element('div', '');
        categoryText.appendChild(element('div', 'library-map-popup-type', point.type));
        if (point.subtype) categoryText.appendChild(element('div', 'library-map-popup-type', point.subtype));
        categoryText.appendChild(element('div', 'library-popup-code', point.location_kind === 'reading_point' ? (point.subtitle || 'Pojok Baca Digital') : 'Kode / NPSN · ' + (point.code || '—')));
        category.appendChild(categoryText);
        header.appendChild(category);
        header.appendChild(element('h3', '', point.name));
        var badges = element('div', 'library-map-popup-badges');
        badges.appendChild(element('span', 'library-status library-status-' + (Object.prototype.hasOwnProperty.call(statuses, point.status) ? point.status : 'pending'), statuses[point.status] || 'Tidak diketahui'));
        if (typeof point.verified === 'boolean') badges.appendChild(element('span', 'library-verification', point.verified ? '✓ Terverifikasi' : 'Belum diverifikasi'));
        header.appendChild(badges);
        panel.appendChild(header);

        var body = element('div', 'library-popup-body');
        body.tabIndex = 0;
        body.setAttribute('aria-label', 'Informasi perpustakaan, gulir untuk detail lainnya');
        var address = element('div', 'library-popup-address');
        address.appendChild(icon('pin'));
        var addressText = element('div', '');
        addressText.appendChild(element('strong', '', point.address || 'Alamat belum diisi'));
        addressText.appendChild(element('div', '', [point.village, point.district].filter(Boolean).join(', ') || 'Wilayah belum diisi'));
        address.appendChild(addressText);
        body.appendChild(address);

        var details = element('dl', 'library-map-popup-details');
        if (!publicView) details.appendChild(infoBlock('Penanggung jawab', point.manager, 'user'));
        details.appendChild(infoBlock('Radius layanan', point.radius + ' meter', 'radius'));
        var hours = infoBlock('Jam layanan', '', 'clock');
        hours.classList.add('library-info-wide');
        var schedule = hours.querySelector('dd');
        schedule.textContent = '';
        (point.opening_hours ? point.opening_hours.split(';') : ['Belum diisi']).filter(function (part) { return part.trim(); }).forEach(function (part) {
            schedule.appendChild(element('div', 'library-schedule-row', part.trim()));
        });
        if (!publicView || point.opening_hours) details.appendChild(hours);
        var facilities = infoBlock('Fasilitas', point.facilities, 'mitra');
        facilities.classList.add('library-info-wide');
        if (!publicView || point.facilities) details.appendChild(facilities);
        body.appendChild(details);
        body.appendChild(element('div', 'library-popup-gps', 'GPS ' + Number(point.lat).toFixed(7) + ', ' + Number(point.lng).toFixed(7)));
        panel.appendChild(body);

        var actions = element('footer', 'library-map-popup-actions');
        var directions = actionLink('Petunjuk arah', 'arrow', 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(point.lat + ',' + point.lng), 'library-map-action library-map-action-primary');
        directions.target = '_blank';
        directions.rel = 'noopener noreferrer';
        actions.appendChild(directions);
        if (source.dataset.selectLibrary === '1') {
            var select = element('button', 'library-map-action library-map-action-secondary');
            select.type = 'button';
            select.textContent = 'Pilih perpustakaan ini';
            select.addEventListener('click', function () { document.dispatchEvent(new CustomEvent('library:selected', {detail: point})); });
            actions.appendChild(select);
        }
        if (canEdit && !publicView) actions.appendChild(actionLink('Edit data', 'edit', point.url, 'library-map-action library-map-action-secondary'));
        panel.appendChild(actions);
        return panel;
    }

    var map = L.map(container, {zoomControl: false, scrollWheelZoom: !publicView}).setView([-6.7071, 111.3502], 11);
    L.control.zoom({position: 'bottomright'}).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var clusters = L.markerClusterGroup({
        maxClusterRadius: function (zoom) { return zoom < 12 ? 65 : 45; },
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        spiderfyOnMaxZoom: true,
        spiderfyDistanceMultiplier: 1.6,
        spiderLegPolylineOptions: {weight: 1.5, color: '#64748b', opacity: 0.6},
        iconCreateFunction: function (cluster) {
            var children = cluster.getAllChildMarkers();
            var counts = {};
            children.forEach(function (marker) {
                var color = styleFor(marker.options.libraryType).color;
                counts[color] = (counts[color] || 0) + 1;
            });
            var total = children.length;
            var offset = 0;
            var stops = Object.keys(counts).sort().map(function (color) {
                var start = offset;
                offset += counts[color] / total * 100;
                return color + ' ' + start + '% ' + offset + '%';
            });
            var ring = element('div', 'library-cluster-ring');
            ring.style.background = 'conic-gradient(' + stops.join(', ') + ')';
            var center = element('span', 'library-cluster-center');
            center.appendChild(element('strong', '', total));
            center.appendChild(element('small', '', 'lokasi'));
            ring.appendChild(center);
            ring.setAttribute('aria-label', total + ' perpustakaan berdekatan, perbesar peta');
            ring.title = total + ' perpustakaan · klik untuk memperbesar';
            return L.divIcon({html: ring, className: 'library-cluster', iconSize: [56, 56], iconAnchor: [28, 28]});
        }
    });
    var activeRadius = null;
    function clearRadius() {
        if (activeRadius) { map.removeLayer(activeRadius); activeRadius = null; }
    }
    map.on('popupclose', clearRadius);
    var markers = points.map(function (point) {
        var pin = symbol(point.type_code, 'library-pin');
        var marker = L.marker([point.lat, point.lng], {
            icon: L.divIcon({html: pin, className: 'library-marker', iconSize: [38, 46], iconAnchor: [19, 45], popupAnchor: [0, -39], tooltipAnchor: [0, -32]}),
            title: point.name,
            alt: 'Lihat ' + point.name,
            keyboard: true,
            libraryType: point.type_code
        });
        marker.bindTooltip(element('span', '', point.name), {direction: 'top'});
        marker.bindPopup(function () { return popup(point); }, {
            className: 'library-detail-overlay',
            maxWidth: Math.min(370, container.clientWidth - 36),
            minWidth: Math.min(320, container.clientWidth - 36),
            autoPanPadding: [12, 12]
        });
        marker.on('popupopen', function () {
            clearRadius();
            marker.closeTooltip();
            activeRadius = L.circle([point.lat, point.lng], {
                radius: point.radius || 50,
                color: styleFor(point.type_code).color,
                weight: 1.5,
                fillOpacity: 0.08,
                interactive: false
            }).addTo(map);
        });
        return marker;
    });
    var focusedMarker = null;
    document.querySelectorAll('[data-library-focus]').forEach(function(button) {
        button.addEventListener('click', function() {
            var index = points.findIndex(function(p) { return String(p.id) === button.dataset.libraryFocus; });
            if (index < 0) return;
            var marker = markers[index];
            if (focusedMarker) { focusedMarker.setZIndexOffset(0); if (focusedMarker.getElement()) focusedMarker.getElement().classList.remove('library-marker-selected'); }
            focusedMarker = marker;
            container.scrollIntoView({behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block:'center'});
            map.invalidateSize();
            clusters.zoomToShowLayer(marker, function() {
                if (focusedMarker !== marker) return;
                marker.openPopup();marker.setZIndexOffset(1000);
                var icon = marker.getElement();
                if (icon) { icon.classList.add('library-marker-selected'); icon.focus({preventScroll:true}); }
            });
        });
    });
    clusters.addLayers(markers);
    map.addLayer(clusters);
    if (markers.length) map.fitBounds(clusters.getBounds(), {padding: [35, 35], maxZoom: 14});

    var showAll = document.getElementById('network-show-all');
    if (showAll) showAll.addEventListener('click', function () {
        map.closePopup();
        if (markers.length) map.fitBounds(clusters.getBounds(), {padding: [35, 35], maxZoom: 14});
        else map.setView([-6.7071, 111.3502], 11);
    });

    var locate = document.getElementById('network-locate');
    var locationState = document.getElementById('network-location-state');
    var nearestState = document.getElementById('network-nearest');
    var userMarker = null;
    var accuracyCircle = null;
    function showPosition(coords) {
        var lat = Number(coords.latitude), lng = Number(coords.longitude), accuracy = Number(coords.accuracy);
        if (!Number.isFinite(lat) || !Number.isFinite(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180) return;
        if (!Number.isFinite(accuracy) || accuracy < 0) accuracy = 0;
        var here = L.latLng(lat, lng);
        if (userMarker) map.removeLayer(userMarker);
        if (accuracyCircle) map.removeLayer(accuracyCircle);
        userMarker = L.marker(here, {
            icon: L.divIcon({html: element('span', 'network-user-key'), className: 'network-user-icon', iconSize: [23, 23], iconAnchor: [12, 12]}),
            title: 'Lokasi Anda', alt: 'Lokasi Anda', zIndexOffset: 2000
        }).addTo(map).bindTooltip(element('span', '', 'Lokasi Anda · akurasi ±' + Math.round(accuracy) + ' m'));
        accuracyCircle = L.circle(here, {radius: accuracy, color: '#0284c7', weight: 1, fillOpacity: 0.08, interactive: false}).addTo(map);
        var nearest = null;
        points.forEach(function (point) {
            var distance = here.distanceTo([point.lat, point.lng]);
            if (!nearest || distance < nearest.distance) nearest = {point: point, distance: distance};
        });
        if (locationState) locationState.textContent = 'Posisi Anda ditampilkan · akurasi GPS ±' + Math.round(accuracy) + ' meter. Ini pratinjau lokasi, bukan bukti check-in.';
        if (nearestState) {
            nearestState.hidden = !nearest;
            if (nearest) nearestState.textContent = 'Lokasi terdekat: ' + nearest.point.name + ' · sekitar ' + (nearest.distance < 1000 ? Math.round(nearest.distance) + ' m' : (nearest.distance / 1000).toFixed(1) + ' km') + ' (jarak garis lurus). Validasi zona baca tetap dilakukan server saat check-in.';
        }
        map.closePopup();
        if (nearest && nearest.distance < 50000) map.fitBounds(L.latLngBounds(here, [nearest.point.lat, nearest.point.lng]), {padding: [55, 55], maxZoom: 16});
        else map.setView(here, 15);
    }
    if (locate) {
        // GPS is requested only by an explicit button press, never on page load.
        locate.addEventListener('click', function () {
            if (!window.isSecureContext || !navigator.geolocation) {
                locationState.textContent = 'GPS memerlukan HTTPS dan browser yang mendukung lokasi. Peta lokasi baca tetap dapat digunakan.';
                return;
            }
            locate.disabled = true;
            locationState.textContent = 'Membaca GPS… Izinkan akses lokasi di browser Anda.';
            navigator.geolocation.getCurrentPosition(function (position) {
                locate.disabled = false;
                showPosition(position.coords);
            }, function (error) {
                locate.disabled = false;
                if (userMarker) { map.removeLayer(userMarker); userMarker = null; }
                if (accuracyCircle) { map.removeLayer(accuracyCircle); accuracyCircle = null; }
                nearestState.hidden = true;
                locationState.textContent = (error.code === 1 ? 'Izin lokasi ditolak. Izinkan lokasi melalui pengaturan browser lalu coba lagi.' : error.code === 3 ? 'Pembacaan GPS terlalu lama. Coba lagi di tempat dengan sinyal lebih baik.' : 'Lokasi belum tersedia. Aktifkan GPS lalu coba lagi.') + ' Peta lokasi baca tetap dapat digunakan.';
            }, {enableHighAccuracy: true, timeout: 12000, maximumAge: 0});
        });
        window.addEventListener('pustaka:location', function (event) { if (event.detail) showPosition(event.detail); });
    }
})();
