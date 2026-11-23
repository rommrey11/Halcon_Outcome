<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private function authorizeAdmin(Request $request)
    {
        $actorId = $request->header('X-User-Id');
        if (!$actorId) {
            return false;
        }
        $actor = User::with('role')->find($actorId);
        return $actor && $actor->is_active && $actor->role?->name === 'Administrator';
    }

    public function roles()
    {
        return response()->json(Role::all(), 200);
    }

    public function index(Request $request)
    {
        if (!$this->authorizeAdmin($request)) {
            return response()->json(['message' => 'Unauthorized: Administrator access required.'], 403);
        }

        return response()->json(User::with('role')->orderBy('id')->get(), 200);
    }

    public function store(Request $request)
    {
        if (!$this->authorizeAdmin($request)) {
            return response()->json(['message' => 'Unauthorized: Administrator access required.'], 403);
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'role_id'   => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => bcrypt($validated['password']),
            'role_id'   => $validated['role_id'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($user->load('role'), 201);
    }

    public function show(Request $request, $id)
    {
        if (!$this->authorizeAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(User::with('role')->findOrFail($id), 200);
    }

    public function update(Request $request, $id)
    {
        if (!$this->authorizeAdmin($request)) {
            return response()->json(['message' => 'Unauthorized: Administrator access required.'], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            'email'     => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|min:6',
            'role_id'   => 'sometimes|required|exists:roles,id',
            'is_active' => 'sometimes|boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json($user->load('role'), 200);
    }

    public function toggleStatus(Request $request, $id)
    {
        if (!$this->authorizeAdmin($request)) {
            return response()->json(['message' => 'Unauthorized: Administrator access required.'], 403);
        }

        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json($user->load('role'), 200);
    }

    public function destroy(Request $request, $id)
    {
        if (!$this->authorizeAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}