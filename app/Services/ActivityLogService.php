<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public static function log(
        string $action,
        string $description,
        string $subjectType = null,
        string $subjectId = null,
        array  $properties = [],
        string $ipAddress = null,
        ?int   $userId = null,
    ): void {
        $userId ??= Auth::id();

        ActivityLog::create([
            'user_id'      => $userId,
            'action'       => $action,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'description'  => $description,
            'properties'   => $properties ?: null,
            'ip_address'   => $ipAddress ?? (function_exists('request') && request() ? request()->ip() : null),
        ]);
    }
}
