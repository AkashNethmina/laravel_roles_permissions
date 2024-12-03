<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view roles', only: ['index']) ,
            new Middleware('permission:edit roles', only: ['edit']) ,
            new Middleware('permission:create roles', only: ['create']) ,
            new Middleware('permission:delete roles', only: ['destroy']) ,
        ];
    }

    // this method will show roles page
    public function index() {
        $roles = Role::orderBy('name', 'ASC')->paginate(25);
        return view('roles.list', [
            'roles' => $roles
        ]);
    }

    // this method will  create roles page
    public function create() {
        $permissions = Permission::orderBy('name', 'ASC')->get();

        return view('roles.create', [
            'permissions' => $permissions
        ]);
    }

    // this method will insert a role in DB
    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:roles|min:3'
        ]);

        if ($validator->passes()) {
            
            $role = Role::create(['name' => $request->name]);

            if(!empty($request->permission)){
                foreach ($request->permission as $name) {
                   $role->givePermissionTo($name);
                }
            }

            return redirect()->route('roles.index')->with('success', 'Role added successfully');

        } else {
            return redirect()->route('roles.create')->withInput()->withErrors($validator);
        }
    }

    // this method will show edit roles page
    public function edit($id) {
        $role = Role::findOrFail($id);
        $haspermissions = $role->permissions()->pluck('name');
        $permissions = Permission::orderBy('name', 'ASC')->get();

        return view('roles.edit', [
            'permissions' => $permissions,
            'haspermissions' => $haspermissions,
            'role' => $role
        ]);
    }

    // this method will update a permission
    public function update($id, Request $request) {
        $role = Role::findOrFail($id); 

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:roles,name,'.$id.',id'
        ]);

        if ($validator->passes()) {
            
            
            $role->name = $request->name;
            $role->save();

            if(!empty($request->permission)){
                $role->syncPermissions($request->permission);
            } else {
                $role->syncPermissions([]);
            }

            return redirect()->route('roles.index')->with('success', 'Role updated successfully');

        } else {
            return redirect()->route('roles.edit', $id)->withInput()->withErrors($validator);
        }
    }

    // this method will delete a permission in DB
    public function destroy(Request $request) {
        $id = $request->id;
        $role = Role::find($id);

        if ($role == null) {
            session()->flash('error', 'Role not found');
            return response()->json([
                'status' => false
            ]);
        }

        $role->delete();

        session()->flash('success', 'Role deleted successfully');
        return response()->json([
            'status' => true
        ]);
    }
}
