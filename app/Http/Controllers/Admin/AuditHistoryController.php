<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditHistory;
use Inertia\Inertia;
use Illuminate\Http\Request;

class AuditHistoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'user_id', 'action', 'sort_field', 'direction']);
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $histories = AuditHistory::with(['user:id,name,role', 'historable'])
            // Filter berdasarkan user pelaksana
            ->when($request->input('user_id'), fn($q, $id) => $q->where('user_id', $id))
            // Filter berdasarkan tipe aksi (update_prodi, finalize, dll)
            ->when($request->input('action'), fn($q, $act) => $q->where('action', $act))
            ->orderBy($sortField, $sortDirection)
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('History/Index', [
            'histories' => $histories,
            'users' => User::all(['id', 'name']), // Untuk dropdown filter user
            'filters' => $filters
        ]);
    }
}
