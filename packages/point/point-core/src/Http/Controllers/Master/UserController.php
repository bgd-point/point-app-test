<?php

namespace Point\Core\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UsageLimitHelper;
use Point\Core\Http\Controllers\Controller;
use Point\Core\Models\Master\History;
use Point\Core\Models\Master\Role;
use Point\Core\Models\Master\RoleUser;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;

class UserController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->may('read.user')) {
            return view('core::errors.restricted');
        }

        $view = view('core::app.master.user.index');
        $search = \Input::get('search');
        $view->users = User::search($search)->where('id', '>', 1)->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->may('create.user')) {
            return view('core::errors.restricted');
        }

        $view = view('core::app.master.user.create');
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
        if (!auth()->user()->may('create.user')) {
            return view('core::errors.restricted');
        }

        $this->validate($request, [
            'email' => 'required|unique:users',
            'name' => 'required|unique:users',
            'password' => 'required',
            'password' => 'required|confirmed'
        ]);

        DB::beginTransaction();

        $client_max_user = env('CLIENT_MAX_USER') ?: config('point.client.max_user');

        if (UsageLimitHelper::userLimit()['current_active_user'] >= $client_max_user) {
            gritter_error(trans('core::core/master.user.limit'));
            return redirect()->back();
        }

        $user = new User;
        $user->email = $request->input('email');
        $user->name = $request->input('name');
        $user->password = bcrypt($request->input('password'));
        $user->created_by = auth()->user()->id;
        $user->updated_by = auth()->user()->id;

        if (!$user->save()) {
            gritter_error(trans('core::core/master.user.create.failed', ['name' => $user->name]));
            return redirect()->back();
        }

        DB::commit();

        if (!file_exists('uploads/avatar/' . config('point.client.slug'))) {
            \File::makeDirectory('uploads/avatar/' . config('point.client.slug'), 0775, true);
        }

        if (\Input::hasFile('photo')) {
            $image = \Image::make(\Input::file('photo'));
            $image->fit(300, 300)->save('uploads/avatar/' . config('point.client.slug') . '/' . $user->id . '.jpg');
        } else {
            \Image::make(asset('core/assets/img/avatar/avatar.jpg'))->fit(300, 300)->save('uploads/avatar/' . $user->id . '.jpg');
        }

        gritter_success(trans('core::core/master.user.create.success', ['name' => $user->name]));
        timeline_publish('create.user', trans('core::core/master.user.create.timeline', ['name' => $user->name]));
        return redirect('master/user/' . $user->id . '/role');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->may('read.user') || $id == 1) {
            return view('core::errors.restricted');
        }

        $view = view('core::app.master.user.show');
        $view->user = User::find($id);
        $view->histories = History::show('users', $id);
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
        if (!auth()->user()->may('update.user') || $id == 1) {
            return view('core::errors.restricted');
        }

        $view = view('core::app.master.user.edit');
        $view->user = User::find($id);
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
        if (!auth()->user()->may('update.user')) {
            return view('core::errors.restricted');
        }

        $this->validate($request, [
            'name' => 'required|unique:users,name,' . $id,
            'email' => 'required|unique:users,email,' . $id
        ]);

        DB::beginTransaction();

        $user = User::find($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->updated_by = auth()->user()->id;

        if (!$user->save()) {
            gritter_error(trans('core::core/master.user.create.failed', ['name' => $user->name]));
            return redirect()->back();
        }

        DB::commit();

        if (\Input::hasFile('photo')) {
            $image = \Image::make(\Input::file('photo'));
            $image->fit(300, 300)->save('uploads/avatar/' . config('point.client.slug') . '/' . $user->id . '.jpg');
        }

        gritter_success(trans('core::core/master.user.update.success', ['name' => $user->name]));
        timeline_publish('update.user', trans('core::core/master.user.update.timeline', ['name' => $user->name]));
        return redirect('master/user/' . $id);
    }

    public function role($id)
    {
        if (!auth()->user()->may('create.user')) {
            return view('core::errors.restricted');
        }

        $view = view('core::app.master.user.role');
        $view->user = User::find($id);
        $view->roles = Role::search(\Input::get('search'))->get();
        return $view;
    }

    public function toggleRole()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $user = User::find(\Input::get('user_id'));
        $role = Role::find(\Input::get('role_id'));

        if (RoleUser::check($user->id, $role->id)) {
            $user->detachRole($role);
        } else {
            $user->attachRole($role);
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update role success'
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
            $user = User::find(\Input::get('id'));
            $user->delete();

            timeline_publish('delete.user', trans('core::core/master.user.delete.timeline', ['name' => $user->name]));

            DB::commit();
        } catch (\Exception $e) {
            return response()->json($this->errorDeleteMessage());
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Delete Success',
            'redirect' => $redirect
        );

        if ($redirect) {
            gritter_success(trans('core::core/master.user.delete.success', ['name' => $user->name]));
        }
        return $response;
    }
}
