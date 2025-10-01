<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:super-admin'), // Hanya super-admin
        ];
    }

    public function index()
    {
        $permissions = Permission::orderBy('name')->get();

        return Inertia::render('Permissions/Index', [
            'permissions' => $permissions
        ]);
    }

    public function create()
    {
        return Inertia::render('Permissions/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'unique:permissions',
                'regex:/^(view|create|edit|delete)\s[a-z\s]+$/', // Format: view blogs, create users, dll
            ],
        ], [
            'name.regex' => 'Permission format must be: action + resource (e.g., "view blogs", "create users")',
        ]);

        DB::beginTransaction();
        try {
            $permission = Permission::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            // Clear cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            DB::commit();

            return redirect()->route('permissions.index')
                ->with('success', 'Permission created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create permission: ' . $e->getMessage()]);
        }
    }

    public function destroy(Permission $permission)
    {
        DB::beginTransaction();
        try {
            $permission->delete();
            
            // Clear cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
            DB::commit();

            return redirect()->route('permissions.index')
                ->with('success', 'Permission deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors(['error' => 'Failed to delete permission: ' . $e->getMessage()]);
        }
    }
}