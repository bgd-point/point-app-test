<?php

namespace Point\Core\Traits;

trait ValidationTrait
{
    public function validatePassword($name, $password)
    {
        if (auth()->validate(['name' => $name, 'password' => $password])) {
            return true;
        }
        return false;
    }

    public function validateCSRF()
    {
        if (\Session::token() === \Request::header('X-CSRF-Token')) {
            return true;
        }
        return false;
    }

    public function validateRequiredInput($array)
    {
        foreach ($array as $value) {
            if ($value == '') {
                return false;
            }
        }
        return true;
    }

    public function restrictionAccessMessage()
    {
        return array(
            'status' => 'error',
            'title' => 'Restricted Access',
            'msg' => 'Unauthorized access'
        );
    }

    public function wrongPasswordMessage()
    {
        return array(
            'status' => 'error',
            'title' => 'Restricted Access',
            'msg' => 'Your password is incorrect'
        );
    }

    public function requiredInputMessage()
    {
        return array(
            'status' => 'error',
            'title' => 'Error',
            'msg' => 'Please fill all required form'
        );
    }

    public function errorDeleteMessage()
    {
        return array(
            'status' => 'error',
            'title' => 'Failed',
            'msg' => 'Cannot delete this file'
        );
    }

    public function errorMessage()
    {
        return array(
            'status' => 'error',
            'title' => 'Error',
            'msg' => 'Undefined error'
        );
    }
}
