<?php
namespace App\Validations;
use Kernel\ValidationRules;

Class CreateContainerValidation extends ValidationRules
{
    public static function rules($request)
    {
        return parent::ajaxExec($request, [
            'name' => [
                'required' => "Name is required",
                'min|3'    => "Name must be at least 3 characters",
                'max|50'   => "Name must not exceed 50 characters"
            ],
            'service' => [
                'required' => "Service is required",
                'min|3'    => "Service must be at least 3 characters",
                'max|50'   => "Service must not exceed 50 characters"
            ],
            'vcpus' => [
                'required' => "vCPUs are required",
                'min|1'    => "vCPUs must be at least 1",
                'max|16'   => "vCPUs must not exceed 16",
                'float'    => "vCPUs must be an floating point number"
            ],
            'memory' => [
                'required'  => "Memory is required",
                'min|128'   => "Memory must be at least 128 MB",
                'max|65536' => "Memory must not exceed 65536 MB",
                'integer'   => "Memory must be an integer"
            ]
        ]);
    }
}