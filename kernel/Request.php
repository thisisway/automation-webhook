<?php

namespace Kernel;

#[\AllowDynamicProperties]
class Request
{

    public function __construct()
    {
        // Processa GET params
        foreach ($_GET as $key => $value) {
            $value = filter_input(INPUT_GET, $key);
            if (empty($value) || !$value)
                $value = filter_input(INPUT_GET, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $this->{filter_var($key, FILTER_DEFAULT)} = $this->sanitizeValue($value);
        }

        // Processa POST params
        foreach ($_POST as $key => $value) {
            $value = filter_input(INPUT_POST, $key);
            if (empty($value) || !$value)
                $value = filter_input(INPUT_POST, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $this->{filter_var($key, FILTER_DEFAULT)} = $this->sanitizeValue($value);
        }

        // Processa JSON requests
        if (Server::jsonRequest()) {
            $jsonRequest = json_decode(file_get_contents('php://input'), true) ?? [];
            foreach ($jsonRequest as $key => $value) {
                $this->{filter_var($key, FILTER_DEFAULT)} = $this->sanitizeValue($value);
            }
        }

        if (count($_FILES) > 0) {
            $this->set('files', json_decode(json_encode($_FILES)));
        }

        $this->set('server', Server::requestInfo());

        return $this;
    }

    /**
     * Sanitiza valores de entrada
     */
    private function sanitizeValue($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitizeValue'], $value);
        }
        return is_string($value) ? htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8') : $value;
    }

    public function hasFile($key)
    {
        return isset($_FILES[$key]) && !empty($_FILES[$key]['tmp_name']);
    }

    /**
     * Retorna um arquivo
     */
    public function file($key)
    {
        if (isset($this->files) && isset($this->files->$key)) {
            return $this->files->$key;
        }
        return null;
    }

    public function __get($key)
    {
        if (isset($this->{$key}))
            return $this->{$key};
        return false;
    }

    public function get($key)
    {
        if (isset($this->{$key}))
            return $this->{$key};
        return false;
    }

    public function __set($key, $value)
    {
        $this->{$key} = $value;
        return $this->{$key};
    }

    public function set($key, $value)
    {
        $this->{$key} = $value;
        return $this->{$key};
    }

    public function all()
    {
        return \get_object_vars($this);
    }

    public function getIpAddress()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ipList[0]);
        }

        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                return $_SERVER[$header];
            }
        }

        return '0.0.0.0';
    }
}
