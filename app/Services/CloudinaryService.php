<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    /**
     * Upload image to Cloudinary with optimization
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string $type (car, service, profile, testimonial)
     * @return array
     * @throws Exception
     */
    public function uploadImage(UploadedFile $file, string $folder, string $type = 'car'): array
    {
        try {
            // Validate file size
            if ($file->getSize() > config('cloudinary.max_file_size.image')) {
                throw new Exception('Image size exceeds maximum allowed size of 5MB');
            }

            // Validate file format
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, config('cloudinary.allowed_formats.image'))) {
                throw new Exception('Invalid image format. Allowed: ' . implode(', ', config('cloudinary.allowed_formats.image')));
            }

            // Upload with optimization
            $uploadedFile = Cloudinary::upload($file->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'auto',
                'format' => 'webp',
                'quality' => 'auto',
                'fetch_format' => 'auto',
                'flags' => 'progressive',
                'crop' => 'fill',
                'gravity' => 'auto',
                'responsive_width' => true,
                'public_id' => uniqid('khanza_' . $type . '_'),
            ])->getSecurePath();

            Log::info('Image uploaded successfully', [
                'folder' => $folder,
                'type' => $type,
                'url' => $uploadedFile,
            ]);

            return [
                'success' => true,
                'url' => $uploadedFile,
                'public_id' => $uploadedFile,
            ];
        } catch (Exception $e) {
            Log::error('Cloudinary upload failed', [
                'error' => $e->getMessage(),
                'folder' => $folder,
                'type' => $type,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'url' => null,
            ];
        }
    }

    /**
     * Upload multiple images
     *
     * @param array $files
     * @param string $folder
     * @param string $type
     * @return array
     */
    public function uploadMultipleImages(array $files, string $folder, string $type = 'car'): array
    {
        $uploadedUrls = [];
        $failedUploads = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $result = $this->uploadImage($file, $folder, $type);
                if ($result['success']) {
                    $uploadedUrls[] = $result['url'];
                } else {
                    $failedUploads[] = $result['error'];
                }
            }
        }

        return [
            'uploaded' => $uploadedUrls,
            'failed' => $failedUploads,
            'total_uploaded' => count($uploadedUrls),
            'total_failed' => count($failedUploads),
        ];
    }

    /**
     * Delete image from Cloudinary
     *
     * @param string $publicId
     * @return bool
     */
    public function deleteImage(string $publicId): bool
    {
        try {
            Cloudinary::destroy($publicId);
            Log::info('Image deleted from Cloudinary', ['public_id' => $publicId]);
            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete image from Cloudinary', [
                'error' => $e->getMessage(),
                'public_id' => $publicId,
            ]);
            return false;
        }
    }

    /**
     * Generate optimized image URL with transformations
     *
     * @param string $publicId
     * @param array $transformations
     * @return string
     */
    public function getOptimizedUrl(string $publicId, array $transformations = []): string
    {
        $defaultTransformations = [
            'fetch_format' => 'auto',
            'quality' => 'auto',
            'dpr' => 'auto',
        ];

        $allTransformations = array_merge($defaultTransformations, $transformations);

        return cloudinary_url($publicId, $allTransformations);
    }

    /**
     * Get responsive image URL for different screen sizes
     *
     * @param string $publicId
     * @param int $width
     * @return string
     */
    public function getResponsiveUrl(string $publicId, int $width = 400): string
    {
        return cloudinary_url($publicId, [
            'fetch_format' => 'auto',
            'quality' => 'auto',
            'width' => $width,
            'crop' => 'fill',
            'gravity' => 'auto',
        ]);
    }

    /**
     * Validate Cloudinary credentials and connectivity
     *
     * @return bool
     */
    public function validateConnection(): bool
    {
        try {
            $api = \Cloudinary\Api::instance();
            $api->ping();
            return true;
        } catch (Exception $e) {
            Log::error('Cloudinary connection failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
