<?php
namespace App\Middleware;

interface Middleware
{
    public function rules($args);
    public function failure();

}