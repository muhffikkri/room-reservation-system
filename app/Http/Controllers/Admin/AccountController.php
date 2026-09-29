<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAccountRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    private const ACCOUNT_TYPES = [
        'pengguna' => ['view' => 'admin.pengguna', 'variable' => 'users', 'index_route' => 'admin.pengguna.index'],
        'petugas' => ['view' => 'admin.petugas', 'variable' => 'officers', 'index_route' => 'admin.petugas.index'],
        'admin' => ['view' => 'admin.admin', 'variable' => 'admins', 'index_route' => 'admin.admin.index'],
    ];

    public function index(Request $request): View
    {
        $type = $this->accountType($request);
        $accounts = User::query()
            ->where('role', $type['role'])
            ->orderBy('name')
            ->get();

        return view($type['view'].'.index', [$type['variable'] => $accounts]);
    }

    public function create(Request $request): View
    {
        return view($this->accountType($request)['view'].'.create');
    }

    public function store(AdminAccountRequest $request): RedirectResponse
    {
        $type = $this->accountType($request);
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'identity' => $validated['identity'],
            'phone' => $validated['phone'],
            'role' => $type['role'],
            'account_status' => 'aktif',
        ]);

        return redirect()
            ->route($type['index_route'])
            ->with('success', "Akun {$type['role']} {$validated['email']} berhasil dibuat dan langsung aktif.");
    }

    /**
     * @return array{role: string, view: string, variable: string, index_route: string}
     */
    private function accountType(Request $request): array
    {
        $role = $request->route('accountType');

        abort_unless(is_string($role) && isset(self::ACCOUNT_TYPES[$role]), 404);

        return ['role' => $role, ...self::ACCOUNT_TYPES[$role]];
    }
}
