<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditHistory;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class AuditHistoryController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil filter untuk dikembalikan ke UI agar state input terjaga
        $filters = $request->only(['search', 'user_id', 'action', 'sort_field', 'direction', 'per_page']);
        $perPage = $request->input('per_page', 10);

        $histories = AuditHistory::with(['user:id,name,role', 'historable'])
            // 1. Menggunakan scope search dari Filterable Trait
            // Kita mencari pada kolom 'action' dan 'reason'
            ->search($request->search, ['action', 'reason'])

            // 2. Filter spesifik tetap menggunakan when()
            ->when($request->user_id, fn($q, $id) => $q->where('user_id', $id))
            ->when($request->action, fn($q, $act) => $q->where('action', $act))

            // 3. Menggunakan scope sort dari Filterable Trait
            // Ini menggantikan logika manual orderBy
            ->sort($request->sort_field, $request->direction)

            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('History/Index', [
            'histories' => $histories,
            'users' => User::all(['id', 'name']),
            'filters' => $filters
        ]);
    }
}
