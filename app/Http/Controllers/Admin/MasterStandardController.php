<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AuditHistory, MasterStandard};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Session};
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MasterStandardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_field', 'direction', 'per_page']);
        $perPage = $request->input('per_page', 10);

        $standards = MasterStandard::withCount('indicators')
            ->search($request->search, ['name', 'code'])
            ->sort($request->sort_field ?? 'code', $request->direction ?? 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Master/Standards/Index', [
            'standards' => $standards,
            'filters' => $filters
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:6|unique:master_standards,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $standard = MasterStandard::create($validated);
            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => MasterStandard::class,
                'historable_id' => $standard->id,
                'stage' => 'master_setup',
                'action' => 'create_standard',
                'new_values' => $standard->toArray(),
            ]);
        });

        return back()->with('toastr', ['type' => 'gradient-primary', 'content' => 'Standar Mutu berhasil dibuat.']);
    }

    public function update(Request $request, MasterStandard $standard)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:6', Rule::unique('master_standards')->ignore($standard->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $oldValues = $standard->toArray();

        DB::transaction(function () use ($standard, $validated, $oldValues) {
            $standard->update($validated);
            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => MasterStandard::class,
                'historable_id' => $standard->id,
                'stage' => 'master_setup',
                'action' => 'update_standard',
                'old_values' => $oldValues,
                'new_values' => $standard->getChanges(),
            ]);
        });

        return back()->with('toastr', ['type' => 'gradient-info', 'content' => 'Standar diperbarui.']);
    }

    public function destroy(MasterStandard $standard)
    {
        DB::transaction(function () use ($standard) {
            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => MasterStandard::class,
                'historable_id' => $standard->id,
                'stage' => 'master_setup',
                'action' => 'delete_standard',
                'old_values' => $standard->toArray(),
            ]);
            $standard->delete();
        });

        return back()->with('toastr', ['type' => 'gradient-red-to-pink', 'content' => 'Standar dihapus.']);
    }
}
