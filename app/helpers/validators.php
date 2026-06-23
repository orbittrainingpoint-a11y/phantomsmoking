<?php
if (!function_exists('validate_email')) {
    function validate_email(string $email): bool
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

if (!function_exists('validate_phone_uae')) {
    function validate_phone_uae(string $phone): bool
    {
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone) ?? '';
        return (bool)preg_match('/^(\+971|00971|0)?[0-9]{9}$/', $phone);
    }
}

if (!function_exists('validate_password_strength')) {
    function validate_password_strength(string $password): array
    {
        $errors = [];
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
        if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password must contain an uppercase letter';
        if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must contain a number';
        return $errors;
    }
}

if (!function_exists('validate_age')) {
    function validate_age(string $dob, int $minAge = 18): bool
    {
        $birthDate = new DateTime($dob);
        $today = new DateTime();
        return $today->diff($birthDate)->y >= $minAge;
    }
}

if (!function_exists('sanitize_string')) {
    function sanitize_string(string $input): string
    {
        return trim(strip_tags($input));
    }
}

if (!function_exists('sanitize_string_deep')) {
    function sanitize_string_deep(array $data): array
    {
        return array_map(fn($v) => is_string($v) ? sanitize_string($v) : $v, $data);
    }
}

if (!function_exists('validate_image_upload')) {
    function validate_image_upload(array $file): array
    {
        $errors = [];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExts  = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error';
            return $errors;
        }
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size must not exceed 5MB';
        }
        // Validate extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            $errors[] = 'Only JPG, PNG and WebP images are allowed';
        }
        // Validate real MIME via magic bytes (not user-supplied)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            $errors[] = 'Could not determine file type';
            return $errors;
        }
        $mime = finfo_file($finfo, $file['tmp_name']) ?: '';
        finfo_close($finfo);
        if (!in_array($mime, $allowedMimes)) {
            $errors[] = 'Only JPG, PNG and WebP images are allowed';
        }
        // Ensure it is a valid image (prevents polyglot files)
        if (empty($errors) && !getimagesize($file['tmp_name'])) {
            $errors[] = 'Uploaded file is not a valid image';
        }
        return $errors;
    }
}
