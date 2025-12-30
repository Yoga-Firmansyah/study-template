<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AuditHistory, Prodi};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Session};
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProdiController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_field', 'direction']);
        $sortField = $request->input('sort_field', 'name'); // Default urut nama
        $sortDirection = $request->input('direction', 'asc');

        $prodis = Prodi::when($request->input('search'), function ($q, $search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        })
            ->orderBy($sortField, $sortDirection)
            ->paginate(10) // Disarankan menggunakan paginate agar konsisten dengan tabel lain
            ->withQueryString();

        return Inertia::render('Master/Prodi/Index', [
            'prodis' => $prodis,
            'filters' => $filters
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:prodis,code',
        ]);

        DB::transaction(function () use ($validated) {
            $prodi = Prodi::create($validated);
            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => Prodi::class,
                'historable_id' => $prodi->id,
                'stage' => 'master_setup',
                'action' => 'create_prodi',
                'new_values' => $prodi->toArray(),
            ]);
        });

        return back()->with('toastr', ['type' => 'gradient-primary', 'content' => 'Prodi berhasil ditambahkan.']);
    }

    public function update(Request $request, Prodi $prodi)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:10', Rule::unique('prodis')->ignore($prodi->id)],
        ]);

        $oldValues = $prodi->toArray();

        DB::transaction(function () use ($prodi, $validated, $oldValues) {
            $prodi->update($validated);
            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => Prodi::class,
                'historable_id' => $prodi->id,
                'stage' => 'master_setup',
                'action' => 'update_prodi',
                'old_values' => $oldValues,
                'new_values' => $prodi->getChanges(),
            ]);
        });

        return back()->with('toastr', ['type' => 'gradient-info', 'content' => 'Data prodi diperbarui.']);
    }

    public function destroy(Prodi $prodi)
    {
        DB::transaction(function () use ($prodi) {
            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => Prodi::class,
                'historable_id' => $prodi->id,
                'stage' => 'master_setup',
                'action' => 'delete_prodi',
                'old_values' => $prodi->toArray(),
            ]);
            $prodi->delete();
        });

        return back()->with('toastr', ['type' => 'gradient-red-to-pink', 'content' => 'Prodi dihapus.']);
    }
}
