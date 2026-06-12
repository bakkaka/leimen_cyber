<?php

namespace App\Service;

use Cloudinary\Cloudinary;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct(string $cloudName, string $apiKey, string $apiSecret)
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key'    => $apiKey,
                'api_secret' => $apiSecret,
            ],
        ]);
    }

    public function upload(string $filePath, array $options = []): ?array
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload($filePath, $options);
            return [
                'publicId' => $result['public_id'],
                'url'      => $result['secure_url'],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function delete(string $publicId): void
    {
        try {
            $this->cloudinary->uploadApi()->destroy($publicId);
        } catch (\Exception $e) {
            // Silence ou log
        }
    }
}