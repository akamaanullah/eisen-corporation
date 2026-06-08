<?php
namespace App\Helpers;

class UploadValidator
{
    public static function validateImageUpload(array $file, int $maxBytes = 5242880): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('Image upload failed.');
        }

        if (($file['size'] ?? 0) > $maxBytes) {
            throw new \InvalidArgumentException('Image exceeds maximum allowed size.');
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \InvalidArgumentException('Invalid image extension.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes, true)) {
            throw new \InvalidArgumentException('Invalid image MIME type.');
        }
    }
}
