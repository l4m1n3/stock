<?php
use App\Models\ActivityLog;

function activity_log($action, $description = null)
{
    ActivityLog::create([
        'user_id'     => auth()->id(),
        'action'      => $action,
        'description' => $description,
    ]);
}