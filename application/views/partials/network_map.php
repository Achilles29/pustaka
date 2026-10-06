<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$network_points = $map_payload ?? [];
$map_id = $map_id ?? 'network-map';
$locatable = !empty($locatable);
$map_types = [];
foreach ($network_points as $point) $map_types[$point['type_code']] = $point['type'];
$network_json = json_encode($network_points, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
?>
<section class="network-map-card" aria-label="Peta perpustakaan dan lokasi baca">
    <div class="network-map-heading">
        <div><span class="network-map-kicker">Jejaring literasi Rembang</span><h2><?= html_escape($map_title ?? 'Temukan lokasi baca'); ?></h2><p><?= count($network_points); ?> lokasi aktif · klik kelompok untuk memperbesar</p></div>
        <div class="network-map-tools">
            <?php if ($locatable): ?><button class="btn btn-primary btn-sm" type="button" id="network-locate"><i class="ti ti-current-location" aria-hidden="true"></i> Lokasi saya</button><?php endif; ?>
            <button class="btn btn-outline-primary btn-sm" type="button" id="network-show-all">Semua lokasi</button>
        </div>
    </div>
    <div id="<?= html_escape($map_id); ?>" class="network-map-canvas" aria-label="Peta interaktif lokasi layanan"></div>
    <?php if (!$network_points): ?><p class="network-map-note">Belum ada lokasi aktif dengan koordinat valid.</p><?php endif; ?>
    <div class="network-map-legend">
        <?php foreach ($map_types as $code => $label): ?><span><span class="library-type-legend" data-library-type="<?= html_escape($code); ?>" aria-hidden="true"></span><?= html_escape($label); ?></span><?php endforeach; ?>
        <?php if ($locatable): ?><span><span class="network-user-key" aria-hidden="true"></span>Lokasi Anda</span><?php endif; ?>
    </div>
    <?php if ($locatable): ?>
        <p class="network-map-note" id="network-location-state" role="status" aria-live="polite">Tekan “Lokasi saya” dan izinkan GPS untuk melihat posisi Anda. Peta tidak melakukan check-in otomatis.</p>
        <p class="network-map-nearest" id="network-nearest" hidden></p>
    <?php endif; ?>
</section>
<script type="application/json" id="libraries-map-data" data-target="<?= html_escape($map_id); ?>" data-public="1" data-can-edit="0" data-select-library="<?= !empty($select_library)?'1':'0'; ?>"><?= $network_json ?: '[]'; ?></script>
