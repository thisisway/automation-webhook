<?php

namespace Kernel;

use Kernel\Redirect;
use Kernel\Session;

class ValidationRules
{

    private static function required($value, $multiple)
    {
        if (!$multiple)
            if ($value == '' || empty($value) || is_null($value) || !$value)
                return true;
        if ($multiple)
            if (empty($value) || is_null($value) || !$value || count($value) == 0)
                return true;
        return false;
    }

    private static function min($min, $value, $required)
    {
        if (strlen($value) < $min && $required) //case required, is obrigatory send data
            return true;

        //case not required, not is obrigatory send data, but, if be sendend, validate length
        if (!$required && strlen($value) > 0 && strlen($value) < $min)
            return true;

        return false;
    }

    private static function max($max, $value, $required)
    {
        // the same logic function min, but reverse 
        if (strlen($value) > $max && $required)
            return true;

        if (!$required && strlen($value) > 0 && strlen($value) > $max)
            return true;

        return false;
    }

    private static function equals($equals, $value)
    {
        if (strlen($value) > 0 && strlen($value) != $equals)
            return true;
        return false;
    }

    private static function removeMask($value)
    {
        return str_replace(['(', ')', ' ', '-', '.', '/'], '', $value);
    }

    private static function email($email)
    {
        if (strlen($email) > 0 && !filter_var($email, FILTER_VALIDATE_EMAIL))
            return true;
        return false;
    }

    private static function unique($model, $collumn, $value)
    {
        $model = "App\\Models\\$model";
        $modelInstance = new $model;
        $rows = $modelInstance->where($collumn, $value)->count();
        if ($rows > 0)
            return true;
        return false;
    }

    private static function confirm($request, $attr)
    {
        return $request->get($attr) != $request->get("confirm_{$attr}");
    }

    private static function past($value)
    {
        if($value){
            return $value < date('Y-m-d');
        }
        return false;
    }

    public static function formExec($request, $rulesArray)
    {
        Session::clearErrors();
        foreach ($rulesArray as $attr => $rules) {
            foreach ($rules as $rule => $message) {
                $required = array_key_exists('required', $rules);
                $multiple = array_key_exists('multiple', $rules);
                $masked   = array_key_exists('masked', $rules);
                $value    = $request->get($attr);
                

                if ($masked)
                    $value = self::removeMask($value);

                //required
                if ($rule == 'required') {
                    if (self::required($value, $multiple) || $value == '')
                        if (!Session::hasError($attr))
                            Session::setError($attr, $message);
                }

                //min
                if (preg_match('/min:/', $rule)) {
                    $min = explode(':', $rule)[1];
                    if (self::min($min, $value, $required))
                        if (!Session::hasError($attr))
                            Session::setError($attr, $message);
                }

                //max
                if (preg_match('/max:/', $rule)) {
                    $max = explode(':', $rule)[1];
                    if (self::max($max, $value, $required))
                        if (!Session::hasError($attr))
                            Session::setError($attr, $message);
                }

                //equals
                if (preg_match('/equals:/', $rule)) {
                    $equals = explode(':', $rule)[1];
                    if (self::equals($equals, $value))
                        if (!Session::hasError($attr))
                            Session::setError($attr, $message);
                }

                //email
                if ($rule == 'email')
                    if (self::email($value))
                        if (!Session::hasError($attr))
                            Session::setError($attr, $message);

                if (preg_match('/unique/', $rule)) {
                    [$rule, $model, $collumn] = explode('|', $rule);
                    if (self::unique($model, $collumn, $value))
                        if (!Session::hasError($attr))
                            Session::setError($attr, $message);
                }

                if (preg_match('/confirm/', $rule)) {
                    if (self::confirm($request, $attr))
                        if (!Session::hasError($attr))
                            Session::setError($attr, $message);
                }

                if (preg_match('/past/', $rule)) {
                    if (self::past($value))
                        if (!Session::hasError($attr))
                            Session::setError($attr, $message);
                }
            }
        }
        if (Session::hasErrors()) {
            Session::setFlash($request->all());
            Redirect::back();
        }
    }


    public static function ajaxExec($request, $rulesArray)
    {
        $errors = [];
        foreach ($rulesArray as $attr => $rules) {
            foreach ($rules as $rule => $message) {
                $required = array_key_exists('required', $rules);
                $multiple = array_key_exists('multiple', $rules);
                $masked   = array_key_exists('masked', $rules);
                $value    = $request->get($attr);

                if ($masked)
                    $value = self::removeMask($value);

                //required
                if ($rule == 'required') {
                    if (self::required($value, $multiple) || $value == '')
                        if (!isset($errors[$attr]))
                            $errors[$attr] = $message;
                }

                //min
                if (preg_match('/min:/', $rule)) {
                    $min = explode(':', $rule)[1];
                    if (self::min($min, $value, $required))
                        if (!isset($errors[$attr]))
                            $errors[$attr] = $message;
                }

                //max
                if (preg_match('/max:/', $rule)) {
                    $max = explode(':', $rule)[1];
                    if (self::max($max, $value, $required))
                        if (!isset($errors[$attr]))
                            $errors[$attr] = $message;
                }

                //equals
                if (preg_match('/equals:/', $rule)) {
                    $equals = explode(':', $rule)[1];
                    if (self::equals($equals, $value))
                        if (!isset($errors[$attr]))
                            $errors[$attr] = $message;
                }

                //email
                if ($rule == 'email')
                    if (self::email($value))
                        if (!isset($errors[$attr]))
                            $errors[$attr] = $message;

                if (preg_match('/unique/', $rule)) {
                    [$rule, $model, $collumn] = explode('|', $rule);
                    if (self::unique($model, $collumn, $value))
                        if (!isset($errors[$attr]))
                            $errors[$attr] = $message;
                }

                if (preg_match('/confirm/', $rule)) {
                    if (self::confirm($request, $attr))
                        if (!isset($errors[$attr]))
                            $errors[$attr] = $message;
                }

                if (preg_match('/past/', $rule)) {
                    if (self::past($value))
                        if (!isset($errors[$attr]))
                            $errors[$attr] = $message;
                }
            }
        }

        if (count($errors) > 0) {
            header('Content-Type: application/json');
            echo json_encode($errors);
            die();
        }
    }
}
