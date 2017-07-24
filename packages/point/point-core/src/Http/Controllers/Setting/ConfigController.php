<?php

namespace Point\Core\Http\Controllers\Setting;

use Point\Core\Http\Controllers\Controller;
use Point\Core\Models\Setting;
use Point\Core\Traits\ValidationTrait;

class ConfigController extends Controller
{
    use ValidationTrait;

    public function index()
    {
        $view = view('core::app.settings.config');
        $view->setting_date_input = Setting::where('name', '=', 'date-input')->first();
        $view->setting_date_show = Setting::where('name', '=', 'date-show')->first();
        $view->setting_mouse_select_allowed = Setting::where('name', '=', 'mouse-select-allowed')->first();
        $view->setting_right_click_allowed = Setting::where('name', '=', 'right-click-allowed')->first();
        $view->setting_user_change_password_allowed = Setting::where('name', '=', 'user-change-password-allowed')->first();
        $view->setting_user_guide_helper = Setting::where('name', '=', 'user-guide-helper')->first();
        $view->setting_pos_font_size = Setting::where('name', '=', 'pos-font-size')->first();
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

        $date_moment = 'DD-MM-YY';
        if (\Input::get('value') == 'd-m-y') {
            $date_moment = 'DD-MM-YY';
        } elseif (\Input::get('value') == 'd-m-Y') {
            $date_moment = 'DD-MM-YYYY';
        } elseif (\Input::get('value') == 'd/m/y') {
            $date_moment = 'DD/MM/YY';
        } elseif (\Input::get('value') == 'd/m/Y') {
            $date_moment = 'DD/MM/YYYY';
        }

        $setting = Setting::where('name', '=', 'date-moment')->first();
        $setting->value = $date_moment;
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

    public function UserGuideHelper()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $setting = Setting::where('name', '=', 'user-guide-helper')->first();
        $setting->value = \Input::get('value');
        $setting->save();

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update data success'
        );
        return response()->json($response);
    }

    public function PosFontSize()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $setting = Setting::where('name', '=', 'pos-font-size')->first();
        $setting->value = \Input::get('value');
        $setting->save();

        $response = array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'Update data success'
        );
        return response()->json($response);
    }
}
