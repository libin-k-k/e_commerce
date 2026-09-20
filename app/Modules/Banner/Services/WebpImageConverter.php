<?php

namespace App\Modules\Banner\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class WebpImageConverter
{
    private const MaxBytes = 200 * 1024;

    /**
     * Convert an uploaded image to WebP under 200KB and store on the public disk.
     */
    public function store(UploadedFile $file, string $directory, int $maxWidth = 1920): string
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('WebP support is required to process banner images.');
        }

        $binary = file_get_contents($file->getRealPath());

        if ($binary === false) {
            throw new RuntimeException('Unable to read the uploaded image.');
        }

        $source = @imagecreatefromstring($binary);

        if ($source === false) {
            throw new RuntimeException('Unsupported image format.');
        }

        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($source);
        }

        imagealphablending($source, true);
        imagesavealpha($source, true);

        $source = $this->scaleDown($source, $maxWidth);
        $webp = $this->encodeUnderLimit($source);
        imagedestroy($source);

        $path = trim($directory, '/').'/'.Str::uuid()->toString().'.webp';
        Storage::disk('public')->put($path, $webp);

        return $path;
    }

    /**
     * @param  \GdImage  $source
     * @return \GdImage
     */
    private function scaleDown($source, int $maxWidth)
    {
        $width = imagesx($source);
        $height = imagesy($source);

        if ($width <= $maxWidth) {
            return $source;
        }

        $targetWidth = $maxWidth;
        $targetHeight = (int) max(1, round($height * ($maxWidth / $width)));
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($source);

        return $resized;
    }

    /**
     * @param  \GdImage  $source
     */
    private function encodeUnderLimit($source): string
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $working = $source;
        $ownsWorking = false;

        for ($attempt = 0; $attempt < 8; $attempt++) {
            for ($quality = 82; $quality >= 35; $quality -= 7) {
                ob_start();
                imagewebp($working, null, $quality);
                $encoded = ob_get_clean();

                if ($encoded !== false && strlen($encoded) <= self::MaxBytes) {
                    if ($ownsWorking) {
                        imagedestroy($working);
                    }

                    return $encoded;
                }
            }

            $nextWidth = (int) max(320, round($width * 0.82));
            $nextHeight = (int) max(180, round($height * 0.82));
            $resized = imagecreatetruecolor($nextWidth, $nextHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $working, 0, 0, 0, 0, $nextWidth, $nextHeight, $width, $height);

            if ($ownsWorking) {
                imagedestroy($working);
            }

            $working = $resized;
            $ownsWorking = true;
            $width = $nextWidth;
            $height = $nextHeight;
        }

        ob_start();
        imagewebp($working, null, 30);
        $encoded = ob_get_clean();

        if ($ownsWorking) {
            imagedestroy($working);
        }

        if ($encoded === false) {
            throw new RuntimeException('Unable to encode WebP image.');
        }

        return $encoded;
    }
}
