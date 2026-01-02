<?php

namespace App\Http\Controllers\Settings;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Session};
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_field', 'direction']);

        return Inertia::render('Settings/User', [
            'users' => User::with('prodi')
                ->search($request->input('search'), ['name', 'email'])
                ->sort($request->sort_field, $request->direction)
                ->paginate(10)
                ->withQueryString(),
            'prodis' => Prodi::all(['id', 'name']),
            'roles' => ['admin', 'auditor', 'auditee'],
            'filters' => $filters
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => [
                'required',
                Rule::enum(UserRole::class),
            ],
            'prodi_id' => 'nullable|exists:prodis,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        Session::flash('toastr', ['type' => 'gradient-primary', 'content' => 'User baru berhasil dibuat']);
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => [
                'required',
                Rule::enum(UserRole::class),
            ],
            'prodi_id' => 'nullable|exists:prodis,id',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        Session::flash('toastr', ['type' => 'gradient-info', 'content' => 'Data user diperbarui']);
        return redirect()->back();
    }

    public function destroy($id)
    {
        if ($id == 1 || $id == auth()->id()) { // Proteksi Admin & Diri Sendiri
            Session::flash('toastr', ['type' => 'solid-yellow', 'content' => 'Tidak dapat menghapus user ini']);
            return redirect()->back();
        }

        $user = User::findOrFail($id);
        $user->delete(); // Soft Delete

        Session::flash('toastr', ['type' => 'gradient-red-to-pink', 'content' => 'User telah dipindahkan ke sampah']);
        return redirect()->back();
    }
}
