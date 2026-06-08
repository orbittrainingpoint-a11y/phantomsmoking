<?php
// Run once: generates icon-192.png and icon-512.png
// Delete this file after running!

$logo = __DIR__ . '/assets/images/logo.webp';
if (!file_exists($logo)) { die('logo.webp not found'); }

$src = imagecreatefromwebp($logo);
if (!$src) { die('Could not load logo.webp'); }

$sw = imagesx($src);
$sh = imagesy($src);

// Make logo white: convert to grayscale then negate (dark pixels become light)
imagefilter($src, IMG_FILTER_GRAYSCALE);
imagefilter($src, IMG_FILTER_NEGATE);
// Boost brightness so it becomes fully white
imagefilter($src, IMG_FILTER_BRIGHTNESS, 100);

foreach ([192, 512] as $size) {
    $dst = imagecreatetruecolor($size, $size);

    // Dark brand background #1A1A2E
    $bg = imagecolorallocate($dst, 26, 26, 46);
    imagefill($dst, 0, 0, $bg);

    // Center logo with padding (75% of icon size)
    $pad   = (int)($size * 0.125);
    $inner = $size - ($pad * 2);

    $ratio = min($inner / $sw, $inner / $sh);
    $dw    = (int)($sw * $ratio);
    $dh    = (int)($sh * $ratio);
    $dx    = (int)(($size - $dw) / 2);
    $dy    = (int)(($size - $dh) / 2);

    imagecopyresampled($dst, $src, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh);

    $out = __DIR__ . "/assets/images/icon-{$size}.png";
    imagepng($dst, $out, 9);
    imagedestroy($dst);
    echo "Created: icon-{$size}.png<br>";
}

imagedestroy($src);
echo '<br><strong>Done! Delete this file now.</strong>';
