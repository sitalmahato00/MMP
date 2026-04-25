<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\SiteSetting;

class PwaIconController extends Controller
{
    public function icon($size)
    {
        $allowedSizes = [72, 96, 128, 144, 152, 192, 384, 512];
        $size = (int) $size;
        
        if (!in_array($size, $allowedSizes)) {
            abort(404);
        }

        $fallbackPath = public_path('favicon.ico');
        
        // Get the site logo
        $siteLogoPath = Cache::remember('brand:site_logo', 600, function () {
            if (!Schema::hasTable('site_settings')) {
                return null;
            }
            return SiteSetting::query()->where('key', 'site_logo')->value('value');
        });

        // Determine source image
        $sourcePath = null;
        if ($siteLogoPath && Storage::disk('public')->exists($siteLogoPath)) {
            $sourcePath = Storage::disk('public')->path($siteLogoPath);
        } else {
            $sourcePath = $fallbackPath;
        }

        // Check if we have GD
        if (!extension_loaded('gd')) {
            // Fallback: serve original file
            return response()->file($sourcePath);
        }

        try {
            // Get image info
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return response()->file($sourcePath);
            }

            // Create source image based on type
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    $sourceImage = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return response()->file($sourcePath);
            }

            if (!$sourceImage) {
                return response()->file($sourcePath);
            }

            // Create new image with target size
            $newImage = imagecreatetruecolor($size, $size);
            
            // Enable alpha blending for transparency
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            
            // Fill with transparent background
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefill($newImage, 0, 0, $transparent);
            
            // Get source dimensions
            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);
            
            // Copy and resize
            imagecopyresampled(
                $newImage, $sourceImage,
                0, 0, 0, 0,
                $size, $size,
                $sourceWidth, $sourceHeight
            );

            // Output as PNG
            ob_start();
            imagepng($newImage, null, 9);
            $imageData = ob_get_clean();

            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($newImage);

            return response($imageData)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'public, max-age=31536000');

        } catch (\Exception $e) {
            // Fallback on error
            return response()->file($sourcePath);
        }
    }
}
