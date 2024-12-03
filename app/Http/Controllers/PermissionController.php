<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view permissions', only: ['index']) ,
            new Middleware('permission:edit permissions', only: ['edit']) ,
            new Middleware('permission:create permissions', only: ['create']) ,
            new Middleware('permission:delete permissions', only: ['destroy']) ,
        ];
    }
    // this method will show permission page
    public function index() {
        $permissions = Permission::orderBy('created_at', 'DESC')->paginate(25);
        return view('permissions.list', [
            'permissions' => $permissions
        ]);
    }

    // this method will show create permission page
    public function create() {
        return view('permissions.create');
    }

    // this method will insert a permission in DB
    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:permissions|min:3'
        ]);

        if ($validator->passes()) {
            Permission::create(['name' => $request->name]);
            return redirect()->route('permissions.index')->with('success', 'Permission added successfully');

        } else {
            return redirect()->route('permissions.create')->withInput()->withErrors($validator);
        }
    }

    // this method will show edit permission page
    public function edit($id) {
        $permissions = Permission::findOrFail($id);
        return view('permissions.edit',[
           'permissions' => $permissions
        ]);
    }

    // this method will update a permission
    public function update($id, Request $request) {

        $permissions = Permission::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'name' => 'required|min:3|unique:permissions,name,'.$id.',id'
        ]);

        if ($validator->passes()) {

            $permissions->name = $request->name;
            $permissions->save();
            return redirect()->route('permissions.index')->with('success', 'Permission updared successfully');

        } else {
            return redirect()->route('permissions.edit',$id)->withInput()->withErrors($validator);
        }
    }

    // this method will delete a permission in DB
    public function destroy(Request $request) {
        $id = $request->id;

        $permissions = Permission::find($id);

        if ($permissions == null) {
            session()->flash('error', 'Permission not found');
            return response()->json([
                'status' => false
            ]);
        }
        $permissions->delete();

        session()->flash('success', 'Permission deleted successfully');
        return response()->json([
            'status' => true
        ]);


    }
}
