<?php

namespace Point\Core\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Http\Controllers\Controller;
use Point\Core\Models\Master\History;
use Point\Core\Models\Master\Permission;
use Point\Core\Models\Master\PermissionRole;
use Point\Core\Models\Master\Role;
use Point\Core\Models\Master\RoleUser;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;

class RoleController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->may('read.role')) {
            return view('core::errors.restricted');
        }

        $view = view('core::app.master.role.index');
        $view->roles = Role::search(\Input::get('search'))->paginate(100);
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles'
        ]);

        DB::beginTransaction();

        $role = new Role;
        $role->name = \Input::get('name');
        $role->slug = $role->name;
        $role->created_by = auth()->user()->id;
        $role->updated_by = auth()->user()->id;

        if (!$role->save()) {
            gritter_error(trans('core::core/master.role.create.failed', ['name' => $role->name]), false);
            return redirect()->back();
        }

        DB::commit();

        gritter_success(trans('core::core/master.role.create.success', ['name' => $role->name]), false);

        timeline_publish('create.role', trans('core::core/master.role.create.timeline', ['name' => $role->name]));

        return redirect('master/role/' . $role->id . '/permission/1');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->may('read.role')) {
            return view('core::errors.restricted');
        }

        $view = view('core::app.master.role.show');
        $view->role = Role::find($id);
        $view->users = RoleUser::where('role_id', '=', $id)->get();
        $view->histories = History::show('roles', $id);
        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->may('update.role')) {
            return view('core::errors.restricted');
        }

        $view = view('core::app.master.role.edit');
        $view->role = Role::find($id);
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->may('update.role')) {
            return view('core::errors.restricted');
        }

        $this->validate($request, [
            'name' => 'required|unique:roles,name,' . $id
        ]);

        DB::beginTransaction();

        $role = Role::find($id);
        $role->name = \Input::get('name');
        $role->slug = str_slug(\Input::get('name'), '-');
        $role->updated_by = auth()->user()->id;

        if (!$role->save()) {
            gritter_error('update failed', false);
            return redirect()->back();
        }

        gritter_success('Role "' . $role->name . '" Berhasil Diupdate', false);

        DB::commit();

        return redirect('master/role/' . $id);
    }

    public function permission($id, $group_id)
    {
        $group = Permission::find($group_id);
        $view = view('core::app.master.role.permission');
        $view->role = Role::find($id);
        $view->list_permission_group = Permission::groupBy('group')->get();
        $view->list_permission_type = Permission::where('group', '=', $group->group)->groupBy('type')->get();
        return $view;
    }

    public function permissionAll()
    {
        $role = Role::find(\Input::get('role_id'));
        $list_permission = explode(',', \Input::get('permission_id'));
        $attach = \Input::get('attach');
        DB::beginTransaction();
        
        foreach ($list_permission as $id) {
            $permission = Permission::find($id);
            if ($attach) {
                $role->attachPermission($permission);
            } else {
                $role->detachPermission($permission);
            }
        }

        DB::commit();
        
        $response = array(
            'attach' => $attach,
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update permission success'
        );
        return response()->json($response);
    }

    public function userAccess($id)
    {
        $view = view('core::app.master.role.user-access');
        $view->role = Role::find($id);
        $view->users = User::all();
        return $view;
    }

    public function toggleUserAccess()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $role = Role::find(\Input::get('role_id'));
        $user = User::find(\Input::get('user_id'));

        if (RoleUser::check($user->id, $role->id)) {
            $user->detachRole($role);
            timeline_publish('create.role', 'remove role "' . $role->name . '" from "'. $user->name);
        } else {
            $user->attachRole($role);
            timeline_publish('create.role', 'add role "' . $role->name . '" from "'. $user->name);
        }
        
        
        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update permission success'
        );
        return response()->json($response);
    }

    public function togglePermission()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $role = Role::find(\Input::get('role_id'));
        $permission = Permission::find(\Input::get('permission_id'));

        if (PermissionRole::check($role->id, $permission->id)) {
            $role->detachPermission($permission);
        } else {
            $role->attachPermission($permission);
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update permission success'
        );
        return response()->json($response);
    }

    public function delete()
    {
        $redirect = false;

        if (\Input::get('redirect')) {
            $redirect = \Input::get('redirect');
        }

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        if (!$this->validatePassword(auth()->user()->name, \Input::get('password'))) {
            return response()->json($this->wrongPasswordMessage());
        }

        try {
            DB::beginTransaction();
            $role = Role::find(\Input::get('id'));
            if ($role->id > 1) {
                $role->delete();
                timeline_publish('delete.role', 'delete role ' . $role->name . ' success');
                $response = array(
                    'status' => 'success',
                    'title' => 'Success',
                    'msg' => 'Delete Success',
                    'redirect' => $redirect
                );
            } else {
                $response = array(
                    'status' => 'success',
                    'title' => 'Success',
                    'msg' => 'Cannot delete administrator role',
                    'redirect' => $redirect
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        if ($redirect) {
            gritter_success('Delete Role "' . $role->name . '" Success');
        }

        return $response;
    }
}
