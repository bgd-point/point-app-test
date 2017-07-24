<?php

namespace Point\Framework\Http\Controllers\Setting;

use Illuminate\Http\Request;
use Point\Core\Models\Setting;
use Point\Framework\Http\Controllers\Controller;

class PasswordController extends Controller
{
    /**
     * Show form edit password
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        if (Setting::userChangePasswordAllowed() == "false") {
            return view('framework::errors.restricted');
        }

        return view('framework::app.settings.password');
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
            gritter_error(trans('framework::framework/setting.wrong_password'));
            return redirect('settings/password');
        }

        if ($new_password != $retype_password) {
            gritter_error(trans('framework::framework/setting.unmatched_password'));
            return redirect('settings/password');
        }

        $user = auth()->user();
        $user->password = bcrypt($new_password);
        $user->save();

        gritter_success(trans('framework::framework/setting.change_password_success'));
        return redirect('settings/password');
    }
}
