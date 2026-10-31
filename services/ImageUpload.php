<?php
/**
 * Safe product image upload (FR17)
 
 */

declare(strict_types=1);

final class ImageUpload
{
    private const TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /**
     * @return array{0: ?string, 1: ?string} [saved file name or null, error message or null]
     * Returns [null, null] when no file was chosen.
     */
    public static function store(?array $file): array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return [null, 'The image could not be uploaded. Please try again.'];
        }
        if ($file['size'] > MAX_UPLOAD_BYTES) {
            return [null, 'The image must be 2 MB or smaller.'];
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::TYPES[$mime]) || @getimagesize($file['tmp_name']) === false) {
            return [null, 'Please upload a JPG, PNG, WEBP or GIF image.'];
        }

        if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true) && !is_dir(UPLOAD_DIR)) {
            return [null, 'The upload folder is missing.'];
        }

        $name = bin2hex(random_bytes(16)) . '.' . self::TYPES[$mime];
        if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
            return [null, 'The image could not be saved.'];
        }
        return [$name, null];
    }

    public static function delete(?string $name): void
    {
        // basename() stops paths like ../../config/config.php
        if ($name && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp|gif)$/', $name)) {
            $path = UPLOAD_DIR . basename($name);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
