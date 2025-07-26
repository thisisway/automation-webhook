<?php
namespace Kernel;
use Kernel\Request;

trait Routes
{

    private $routes = [
        'GET'  => [],
        'POST' => [],
        'PUT'  => [],
        'PATCH'=> [],
        'DELETE' => []
    ];
    private $groupPrefix = null;
    private $middlewares = false;

    public function verifyRoute($class, $action) 
    {
        if(class_exists($class))
        {
            if(method_exists(new $class, $action))
                return true;
        }
        return false;
    }

    public function getRoute($requestMethod, $path)
    {
        
        $pathMatched = $this->getMatchRoute($requestMethod, $path);
        if($pathMatched)
        {
            $route       = $this->routes[$requestMethod][$pathMatched];
            $route->args = $this->setArgs($requestMethod, $path, $pathMatched, $route);
            
            if($route->middlewares)
                $this->execMiddlewares($route);
            return $route;
        }
           
        return false;
    }


    public function setRoute($requestMethod, $path, $controllerAndMethod, $permission = false)
    {
        if($this->groupPrefix)
            $path = $this->groupPrefix . $path;

        $middlewares                         = $this->middlewares;
        [$controller, $controllerMethod]     = explode('@',$controllerAndMethod);
        $this->routes[$requestMethod][$path] = (object)compact('controller', 'controllerMethod', 'middlewares', 'permission');
    }

    public function group($prefix, $callback)
    {
        $this->groupPrefix = $prefix;
        $callback();
        $this->groupPrefix = null;
    }

    private function getMatchRoute($method, $path)
    {
        $routes = array_keys($this->routes[$method]);
        foreach($routes as $route)
        {
            $spacesPath  = count( explode('/',$path) );
            $spacesRoute = count( explode('/',$route) );
            $regexRoute  = $this->routeToRegex($route);
            if( preg_match($regexRoute, $path ) && $spacesPath == $spacesRoute)
		        return $route;
        }
        return false;
    }

    private function routeToRegex($route)
    {
        $pattern = "/";
        $pattern .= str_replace('/','\/',$route);
        $pattern = preg_replace('/:\w*/','\w*',$pattern);
        $pattern .= "$/";
        return $pattern;
    }

    private function setArgs($requestMethod, $path, $requestRoute, $route)
    {

        $args = [];

        $reflection = new \ReflectionMethod('App\\Controller\\'.$route->controller, $route->controllerMethod);
        foreach ($reflection->getParameters() as $params)
        {   
            if($params->hastype() && $params->getType()->getName() == 'Kernel\Request')
                array_push($args, new Request);
        }
        
        $positionsPath  = explode('/', $path);
        $positionsRoute = explode('/', $requestRoute);
        
        foreach($positionsRoute as $key => $position)
        {
            if(preg_match('/:\w*/',$position))
            {
                $positionName        = str_replace(':','',$position);
                $args[$positionName] = $positionsPath[$key];
            }
        }            

        return $args;
    }

    public function setMiddlewares($middlewares, $setRoutes)
    {
        $this->middlewares = $middlewares;
        $setRoutes();
        $this->middlewares = false;
    }

    public function execMiddlewares($route){

        $middlewares = $route->middlewares;
        foreach($middlewares as $middle) {
            $middleware = "App\\Middleware\\".$middle;
            if(!call_user_func_array([new $middleware, 'rules'], [$route])){
                call_user_func([new $middleware, 'failure']);
            }
        }
    }
}