<?php

namespace Point\Framework\Http\Controllers\Setting;

use Point\Core\Models\Setting;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;

class ConfigController extends Controller
{
    use ValidationTrait;

    public function index()
    {
        $view = view('framework::app.settings.config');
        $view->setting_date_input = Setting::where('name', '=', 'date-input')->first();
        $view->setting_date_show = Setting::where('name', '=', 'date-show')->first();
        $view->setting_mouse_select_allowed = Setting::where('name', '=', 'mouse-select-allowed')->first();
        $view->setting_right_click_allowed = Setting::where('name', '=', 'right-click-allowed')->first();
        $view->setting_user_change_password_allowed = Setting::where('name', '=', 'user-change-password-allowed')->first();
        $view->setting_lock_periode = Setting::where('name', '=', 'lock-periode')->first();
        return $view;
    }

    public function dateInput()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $setting = Setting::where('name', '=', 'date-input')->first();
        $setting->value = \Input::get('value');
        $setting->save();

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update data success'
        );
        return response()->json($response);
    }

    public function dateShow()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $setting = Setting::where('name', '=', 'date-show')->first();
        $setting->value = \Input::get('value');
        $setting->save();

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update data success'
        );
        return response()->json($response);
    }

    public function mouseSelectAllowed()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $setting = Setting::where('name', '=', 'mouse-select-allowed')->first();
        $setting->value = \Input::get('value');
        $setting->save();

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update data success'
        );
        return response()->json($response);
    }

    public function RightClickAllowed()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $setting = Setting::where('name', '=', 'right-click-allowed')->first();
        $setting->value = \Input::get('value');
        $setting->save();

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update data success'
        );
        return response()->json($response);
    }

    public function UserChangePasswordAllowed()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $setting = Setting::where('name', '=', 'user-change-password-allowed')->first();
        $setting->value = \Input::get('value');
        $setting->save();

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update data success'
        );
        return response()->json($response);
    }

    public function lockPeriode()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $setting = Setting::where('name', '=', 'lock-periode')->first();
        $setting->value = date_format_db(\Input::get('value'), false);
        $setting->save();

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update data success'
        );

        return response()->json($response);
    }
}
