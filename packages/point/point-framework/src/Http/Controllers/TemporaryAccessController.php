<?php

namespace Point\Framework\Http\Controllers;

use Point\Core\Models\Master\Permission;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;

class TemporaryAccessController extends Controller
{
    use ValidationTrait;

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($title, $permission_type)
    {
        access_is_allowed('update.role');

        $view = view('framework::app.access');
        $view->permission_type = Permission::where('type', '=', $permission_type)->first();
        $view->users = User::search(\Input::get('search'))->paginate(100);
        $view->title = $title;
        return $view;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleAccess()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $user = User::find(\Input::get('user_id'));
        $permission = Permission::find(\Input::get('permission_id'));

        if ($user->may($permission->slug)) {
            $user->detachPermission($permission);
        } else {
            $user->attachPermission($permission);
        }

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update permission success'
        );
        return response()->json($response);
    }
}
