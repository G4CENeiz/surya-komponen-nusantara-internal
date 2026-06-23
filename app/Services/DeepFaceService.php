<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DeepFaceService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.deepface.url', 'http://deepface:5000');
    }

    /**
     * Verify if two face images belong to the same person.
     */
    public function verify(string $imagePath1, string $imagePath2): array
    {
        $response = Http::attach(
            'img1',
            Storage::disk('public')->get($imagePath1),
            basename($imagePath1)
        )->attach(
            'img2',
            Storage::disk('public')->get($imagePath2),
            basename($imagePath2)
        )->post("{$this->baseUrl}/verify");

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => Verifikasi wajah gagal: '.$response->body(),
            ];
        }

        $data = $response->json();

        return [
            'success' => true,
            'verified' => $data['verified'] ?? false,
            'distance' => $data['distance'] ?? null,
            'threshold' => $data['threshold'] ?? null,
        ];
    }

    /**
     * Get face embedding (representation) for an image.
     */
    public function represent(string $imagePath, string $modelName = 'Facenet'): array
    {
        $response = Http::attach(
            'img',
            Storage::disk('public')->get($imagePath),
            basename($imagePath)
        )->post("{$this->baseUrl}/represent", [
            'model_name' => $modelName,
        ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => Representasi wajah gagal: '.$response->body(),
            ];
        }

        $data = $response->json();

        return [
            'success' => true,
            'embedding' => $data['embedding'] ?? null,
            'face_confidence' => $data['face_confidence'] ?? null,
        ];
    }

    /**
     * Analyze facial attributes (age, gender, emotion, race).
     */
    public function analyze(string $imagePath, array $actions = ['age', 'gender']): array
    {
        $response = Http::attach(
            'img',
            Storage::disk('public')->get($imagePath),
            basename($imagePath)
        )->post("{$this->baseUrl}/analyze", [
            'actions' => $actions,
        ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => Analisis wajah gagal: '.$response->body(),
            ];
        }

        $data = $response->json();

        return [
            'success' => true,
            'results' => $data ?? [],
        ];
    }

    /**
     * Extract faces from an image.
     */
    public function extractFaces(string $imagePath, bool $antiSpoofing = true): array
    {
        $response = Http::attach(
            'img',
            Storage::disk('public')->get($imagePath),
            basename($imagePath)
        )->post("{$this->baseUrl}/extract_faces", [
            'anti_spoofing' => $antiSpoofing,
        ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => Ekstraksi wajah gagal: '.$response->body(),
            ];
        }

        $data = $response->json();

        return [
            'success' => true,
            'faces' => $data ?? [],
        ];
    }

    /**
     * Verify face against a user's registered face photo.
     * Used during clock-in/out to confirm identity.
     */
    public function verifyUser(string $uploadedPhotoPath, string $registeredPhotoPath): array
    {
        $result = $this->verify($uploadedPhotoPath, $registeredPhotoPath);

        if (! $result['success']) {
            return $result;
        }

        // Threshold for FaceNet model (lower = stricter)
        $threshold = $result['threshold'] ?? 0.4;
        $distance = $result['distance'] ?? 1;

        return [
            'success' => true,
            'verified' => $result['verified'],
            'distance' => $distance,
            'threshold' => $threshold,
            'confidence' => max(0, 1 - $distance),
            'message' => $result['verified']
                ? 'Wajah terverifikasi successfully.'
                : 'Face does not match registered photo.',
        ];
    }
}
