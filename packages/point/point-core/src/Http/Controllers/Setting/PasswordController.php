<?php

namespace Point\Core\Http\Controllers\Setting;

use Illuminate\Http\Request;
use Point\Core\Http\Controllers\Controller;
use Point\Core\Models\Setting;

class PasswordController extends Controller
{
    /**
     * Show form edit password
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        if (Setting::userChangePasswordAllowed() == "false") {
            return view('core::errors.restricted');
        }

        return view('core::app.settings.password');
    }

    /**
     * Change password request
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required',
            'new_password' => 'required',
            'retype_password' => 'required'
        ]);

        $old_password = $request->input('old_password');
        $new_password = $request->input('new_password');
        $retype_password = $request->input('retype_password');

        if (!\Hash::check($old_password, auth()->user()->password)) {
            gritter_error(trans('core::core/setting.wrong_password'), false);
            return redirect('settings/password');
        }

        if ($new_password != $retype_password) {
            gritter_error(trans('core::core/setting.unmatched_password'), false);
            return redirect('settings/password');
        }

        $user = auth()->user();
        $user->password = bcrypt($new_password);
        $user->save();

        gritter_success(trans('core::core/setting.change_password_success'), false);
        return redirect('settings/password');
    }
}
