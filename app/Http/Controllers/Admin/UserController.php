<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna dengan filter peran, status verifikasi, dan pencarian.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $roleFilter = $request->query('role');
        $statusFilter = $request->query('status');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter && in_array($roleFilter, ['admin', 'staff', 'user'], true)) {
            $query->where('role', $roleFilter);
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            if ($statusFilter === 'verified') {
                $query->where('is_verified', true);
            } elseif ($statusFilter === 'unverified') {
                $query->where('is_verified', false);
            }
        }

        $users = $query->orderByRaw('is_verified ASC') // Unverified muncul di atas agar admin cepat memproses
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'unverified' => User::where('is_verified', false)->count(),
            'verified' => User::where('is_verified', true)->count(),
            'staff' => User::where('role', Role::STAFF->value)->count(),
            'user' => User::where('role', Role::USER->value)->count(),
            'admin' => User::where('role', Role::ADMIN->value)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats', 'search', 'roleFilter', 'statusFilter'));
    }

    /**
     * Tampilkan formulir pembuatan akun pengguna baru secara langsung oleh admin.
     */
    public function create(): View
    {
        // Admin hanya dapat mendaftarkan role user dan staff (admin tidak dibuat via form)
        $availableRoles = [
            'user' => 'Pengguna (Mahasiswa / Dosen / Civitas)',
            'staff' => 'Petugas Fasilitas (Staff)',
        ];

        return view('admin.users.create', compact('availableRoles'));
    }

    /**
     * Simpan akun baru yang didaftarkan langsung oleh admin.
     * Akun yang dibuat langsung oleh admin berstatus aktif (is_verified = true).
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_verified'] = true;

        $user = User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$user->role->label()} untuk '{$user->name}' ({$user->email}) berhasil dibuat dan langsung berstatus aktif.");
    }

    /**
     * Tampilkan formulir edit akun pengguna.
     */
    public function edit(User $user): View
    {
        $availableRoles = [
            'user' => 'Pengguna (Mahasiswa / Dosen / Civitas)',
            'staff' => 'Petugas Fasilitas (Staff)',
        ];

        return view('admin.users.edit', compact('user', 'availableRoles'));
    }

    /**
     * Perbarui data akun pengguna.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Data akun '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Verifikasi akun hasil registrasi mandiri (set is_verified = true).
     */
    public function verify($id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_verified' => true]);

        return back()->with('success', "Akun '{$user->name}' ({$user->email}) berhasil diverifikasi! Pengguna sekarang dapat login.");
    }

    /**
     * Tolak atau tangguhkan verifikasi akun (set is_verified = false).
     */
    public function reject($id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_verified' => false]);

        return back()->with('success', "Akun '{$user->name}' ({$user->email}) ditandai belum diverifikasi/ditangguhkan.");
    }
}
