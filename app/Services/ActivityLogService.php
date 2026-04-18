<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public static function log(
        string $action,
        string $description,
        string $subjectType = null,
        string $subjectId = null,
        array  $properties = [],
        string $ipAddress = null
    ): void {
        if (! Auth::check()) {
            return;
        }

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'description'  => $description,
            'properties'   => $properties ?: null,
            'ip_address'   => $ipAddress ?? request()->ip(),
        ]);
    }
}
