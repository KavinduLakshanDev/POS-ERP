<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:web,company'),
        ];
    }

    public function index(Request $request)
    {
        $query = User::with(['company', 'section', 'role']);
        $user = $request->user();

        // If not super admin, filter by the user's company
        if ($user && $user->user_type !== 'super_admin') {
            $query->where('company_code', $user->company_code);
        }

        // Filter by section
        if ($request->has('section_code')) {
            $query->where('section_code', $request->section_code);
        }

        // Filter by role
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'user_type' => 'required|in:super_admin,company_admin,company_user',
            'company_code' => 'nullable|string|exists:companies,company_code',
            'section_code' => 'nullable|string|exists:sections,section_code',
            'delivery_section_code' => 'nullable|string|exists:sections,section_code',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['uuid'] = Str::uuid();

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'user_type' => 'required|in:super_admin,company_admin,company_user',
            'company_code' => 'nullable|string|exists:companies,company_code',
            'section_code' => 'nullable|string|exists:sections,section_code',
            'delivery_section_code' => 'nullable|string|exists:sections,section_code',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
