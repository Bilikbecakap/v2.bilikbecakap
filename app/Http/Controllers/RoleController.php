<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view roles', only: ['index', 'show']),
            new Middleware('permission:create roles', only: ['create', 'store']),
            new Middleware('permission:edit roles', only: ['edit', 'update']),
            new Middleware('permission:delete roles', only: ['destroy']),
        ];
    }

    public function index()
    {
        $roles = Role::with('permissions')->get();

        return Inertia::render('Roles/Index', [
            'roles' => $roles
        ]);
    }

    public function create()
    {
        $permissions = Permission::all();

        return Inertia::render('Roles/Create', [
            'permissions' => $permissions
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'guard_name' => 'nullable|string|in:web,api',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => $validated['guard_name'] ?? 'web',
            ]);

            if (isset($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create role: ' . $e->getMessage()]);
        }
    }

    public function show(Role $role)
    {
        return Inertia::render('Roles/Show', [
            'role' => $role->load('permissions')
        ]);
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();

        return Inertia::render('Roles/Edit', [
            'role' => $role->load('permissions'),
            'permissions' => $permissions
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $role->update([
                'name' => $validated['name'],
            ]);

            // Sync permissions by name instead of ID
            if (isset($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Role updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update role: ' . $e->getMessage()]);
        }
    }

    public function destroy(Role $role)
    {
        // Proteksi role sistem
        if (in_array(strtolower($role->name), ['super-admin', 'admin'])) {
            return back()->withErrors(['error' => 'Cannot delete system role']);
        }

        DB::beginTransaction();
        try {
            $role->delete();
            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Role deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors(['error' => 'Failed to delete role: ' . $e->getMessage()]);
        }
    }
}