<?php

namespace App\Http\Controllers;
use App\Models\ActivityLog;

use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(100);

        return view('admin.logs.index', compact('logs'));
    }
}
