<?php

namespace App\Http\Controllers;

use App\Services\AssignmentService;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    protected $assignmentService;

    public function __construct(AssignmentService $service)
    {
        $this->assignmentService = $service;
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'period_id' => 'required|exists:periods,id',
            'master_standard_id' => 'required|exists:master_standards,id',
            'auditor_ids' => 'required|array',
            'auditor_ids.*' => 'exists:users,id',
        ]);

        // Jalankan service
        $this->assignmentService->createAssignment($validated);

        return redirect()->back()->with('success', 'Penugasan dan Snapshot berhasil dibuat.');
    }
}
