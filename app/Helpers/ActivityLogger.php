<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(
        string $type,
        string $description,
        ?string $modelType = null,
        int|string|null $modelId = null,
        ?array $properties = null,
    ): ?ActivityLog {
        try {
            return ActivityLog::create([
                'user_id' => auth()->id(),
                'type' => $type,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'description' => $description,
                'properties' => $properties ? json_encode($properties) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            try {
                Log::warning('Gagal mencatat aktivitas: ' . $e->getMessage(), [
                    'type' => $type,
                    'description' => $description,
                ]);
            } catch (\Throwable $ignored) {
                // jangan pernah mengganggu proses utama hanya karena log gagal
            }

            return null;
        }
    }
}
