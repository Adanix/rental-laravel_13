<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageResizeService
{
    /**
     * Store uploaded image, resizing to max 1200px width using GD if available.
     */
    public function storeItemImage(UploadedFile $file, string $folder = 'items'): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid() . '.' . ($extension ?: 'jpg');
        $targetDir = storage_path('app/public/' . $folder);

        if (! file_exists($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $targetPath = $targetDir . '/' . $filename;
        $relativePath = $folder . '/' . $filename;

        // If GD extension is available, attempt resize
        if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
            try {
                $imageContent = file_get_contents($file->getRealPath());
                $src = @imagecreatefromstring($imageContent);

                if ($src !== false) {
                    $origWidth = imagesx($src);
                    $origHeight = imagesy($src);
                    $maxWidth = 1200;

                    if ($origWidth > $maxWidth) {
                        $newWidth = $maxWidth;
                        $newHeight = (int) round(($origHeight / $origWidth) * $maxWidth);

                        $dst = imagecreatetruecolor($newWidth, $newHeight);
                        imagealphablending($dst, false);
                        imagesavealpha($dst, true);
                        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

                        if (function_exists('imagewebp')) {
                            $webpFilename = Str::uuid() . '.webp';
                            $webpTargetPath = $targetDir . '/' . $webpFilename;
                            imagewebp($dst, $webpTargetPath, 85);
                            imagedestroy($dst);
                            imagedestroy($src);
                            return $folder . '/' . $webpFilename;
                        }

                        imagejpeg($dst, $targetPath, 85);
                        imagedestroy($dst);
                        imagedestroy($src);
                        return $relativePath;
                    }

                    imagedestroy($src);
                }
            } catch (\Throwable $e) {
                // Fall back to standard storage
            }
        }

        // Fallback: standard Laravel file storage
        $stored = $file->storeAs('public/' . $folder, $filename);
        return str_replace('public/', '', $stored);
    }
}
