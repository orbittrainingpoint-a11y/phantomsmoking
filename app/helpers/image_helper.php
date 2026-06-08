<?php
if (!function_exists('resize_image')) {
    function resize_image(string $source, string $dest, int $maxW, int $maxH): bool
    {
        $info = getimagesize($source);
        if (!$info) return false;

        [$origW, $origH, $type] = $info;
        $ratio = min($maxW / $origW, $maxH / $origH, 1);
        $newW = (int)($origW * $ratio);
        $newH = (int)($origH * $ratio);

        $src = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($source),
            IMAGETYPE_PNG  => imagecreatefrompng($source),
            IMAGETYPE_WEBP => imagecreatefromwebp($source),
            default        => false,
        };
        if (!$src) return false;

        $dst = imagecreatetruecolor($newW, $newH);
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $result = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($dst, $dest, 85),
            IMAGETYPE_PNG  => imagepng($dst, $dest, 8),
            IMAGETYPE_WEBP => imagewebp($dst, $dest, 85),
            default        => false,
        };
        imagedestroy($src);
        imagedestroy($dst);
        return (bool)$result;
    }
}

if (!function_exists('generate_thumbnail')) {
    function generate_thumbnail(string $source, string $dest, int $size = 300): bool
    {
        return resize_image($source, $dest, $size, $size);
    }
}

if (!function_exists('save_product_image')) {
    function save_product_image(array $file, int $productId): ?string
    {
        $errors = validate_image_upload($file);
        if (!empty($errors)) return null;

        // ROOT_PATH is defined in index.php
        $uploadBase = defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public_html';
        $dir = $uploadBase . '/uploads/products/' . $productId . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $productId . '_' . time() . '_' . substr(bin2hex(random_bytes(2)), 0, 4) . '.' . $ext;
        $dest = $dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
        resize_image($dest, $dest, 800, 800);

        $thumbDir = $dir . 'thumbs/';
        if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);
        generate_thumbnail($dest, $thumbDir . $filename, 300);

        return '/uploads/products/' . $productId . '/' . $filename;
    }
}

if (!function_exists('save_category_image')) {
    function save_category_image(array $file): ?string
    {
        $errors = validate_image_upload($file);
        if (!empty($errors)) return null;

        $uploadBase = defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public_html';
        $dir = $uploadBase . '/uploads/categories/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'cat_' . time() . '_' . substr(bin2hex(random_bytes(2)), 0, 4) . '.' . $ext;
        $dest = $dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
        resize_image($dest, $dest, 800, 600);

        return '/uploads/categories/' . $filename;
    }
}

if (!function_exists('delete_image')) {
    function delete_image(string $path): void
    {
        $uploadBase = defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public_html';
        $full = $uploadBase . $path;
        if (file_exists($full)) unlink($full);
        $thumb = dirname($full) . '/thumbs/' . basename($full);
        if (file_exists($thumb)) unlink($thumb);
    }
}
