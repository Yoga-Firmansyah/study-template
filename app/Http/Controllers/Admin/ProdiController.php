<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Assignment, AuditHistory, Prodi};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Session};
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProdiController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_field', 'direction', 'per_page']);
        $perPage = $request->input('per_page', 10);

        $prodis = Prodi::search($request->search, ['name', 'code']) // Cari di kolom name & code
            ->sort($request->sort_field, $request->direction)    // Urutkan ASC/DESC
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Master/Prodi/Index', [
            'prodis' => $prodis,
            'filters' => $filters,
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
        if ($prodi->users()->exists() || Assignment::where('prodi_id', $prodi->id)->exists()) {
            return back()->with('toastr', [
                'type' => 'gradient-red-to-pink',
                'content' => 'Gagal: Prodi ini masih memiliki user atau penugasan audit.'
            ]);
        }

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
