<?php

namespace App\Http\Controllers;

use App\Models\AuditHistory;
use Inertia\Inertia;

class AuditHistoryController extends Controller
{
    public function index()
    {
        return Inertia::render('History/Index', [
            'histories' => AuditHistory::with(['user', 'historable'])
                ->latest()
                ->paginate(20)
        ]);
    }
}
