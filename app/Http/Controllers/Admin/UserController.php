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
                $query->verified();
            } elseif ($statusFilter === 'unverified') {
                $query->pending();
            } elseif ($statusFilter === 'rejected') {
                $query->rejected();
            }
        }

        $users = $query->orderByRaw('is_verified ASC, rejected_at IS NULL DESC') // Menunggu verifikasi muncul di atas
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'unverified' => User::pending()->count(),
            'verified' => User::verified()->count(),
            'rejected' => User::rejected()->count(),
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

        // Sinkronkan penanda penolakan dengan status verifikasi yang dipilih admin.
        if (! empty($validated['is_verified'])) {
            $validated['rejected_at'] = null;
        } elseif ($user->isPending()) {
            $validated['rejected_at'] = null;
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Data akun '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Verifikasi akun hasil registrasi mandiri (set is_verified = true).
     * Menghapus jejak penolakan jika sebelumnya pernah ditolak.
     */
    public function verify($id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_verified' => true,
            'rejected_at' => null,
        ]);

        return back()->with('success', "Akun '{$user->name}' ({$user->email}) berhasil diverifikasi! Pengguna sekarang dapat login.");
    }

    /**
     * Tolak verifikasi akun (set is_verified = false + catat waktu penolakan).
     * Akun tidak dihapus permanen dan dapat dipulihkan kembali oleh admin.
     */
    public function reject($id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menolak akun Anda sendiri.');
        }

        $user->update([
            'is_verified' => false,
            'rejected_at' => now(),
        ]);

        return back()->with('success', "Akun '{$user->name}' ({$user->email}) telah ditolak. Akun tetap tersimpan dan dapat dipulihkan kembali.");
    }

    /**
     * Pulihkan akun yang ditolak menjadi Menunggu Verifikasi (bukan langsung aktif).
     */
    public function restore($id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! $user->isRejected()) {
            return back()->with('error', "Akun '{$user->name}' tidak dalam status ditolak.");
        }

        $user->update([
            'is_verified' => false,
            'rejected_at' => null,
        ]);

        return back()->with('success', "Akun '{$user->name}' ({$user->email}) berhasil dipulihkan dan kembali menunggu verifikasi.");
    }
}
