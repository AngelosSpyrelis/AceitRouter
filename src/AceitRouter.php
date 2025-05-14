<?php

class AceitRouter {
    private $prefixes = [];
    private $suffixes = [];
    private $fallback;
    private $routes = [];

    private $default;

    private $pageErrorDefault;

    
    public function __construct() {
        
    }

    /**
     * Summary of setFallback
     * Sets a fallback callable that will be used as an ultimate resort.
     * If it is called because of an error, the error message will also be passed onto it as an argument. 
     * @param mixed $fallback - The fallback callable
     * @throws \InvalidArgumentException - If no valid callback is provided.
     */
    public function setFallback($fallback){
        if(!is_callable($fallback)){
            throw new \InvalidArgumentException(
                "Fallback must be callable. Received: " . 
                (is_object($fallback) ? get_class($fallback) : gettype($fallback))
            );
        }
        $this->fallback = $fallback;
    }

    /**
     * Summary of setDefault
     * Sets the default callable, in case someone gives an empty request. This would be the equivalent of a '' or '/' match in other Routers.
     * @param mixed $default - The default page callback (Usually home)
     * @throws \InvalidArgumentException - If no valid callback is provided.
     * @return void
     */
    public function setDefault($default){
        if(!is_callable($default)){
            throw new \InvalidArgumentException(
                "Default must be callable. Received: " . 
                (is_object($default) ? get_class($default) : gettype($default))
            );
        }
        $this->default = $default;
    }

    /**
     * Summary of setPageErrorDefault
     * Sets the page error default callback. It is called when there is a page match but the provided callback is not callable.
     * @param mixed $default - The default page callback (some kind of error page, usually.).
     * @throws \InvalidArgumentException - If no valid callback is provided.
     * @return void
     */
    public function setPageErrorDefault($default){
        if(!is_callable($default)){
            throw new \InvalidArgumentException(
                "pageError must be callable. Received: " . 
                (is_object($default) ? get_class($default) : gettype($default))
            );
        }
        $this->pageErrorDefault = $default;
    }

    /**
     * Summary of setRoutes
     * Sets the routes that the router will use to match requests. Expects an array (nested, optionally) of callables
     * @param array $routes - An array of callables (Can be nested)
     * @throws \InvalidArgumentException - If no valid route array is provided.
     * @return void
     */
    public function setRoutes($routes) {
        if(!is_array($routes)){
            error_log("[AceitRouter] ".date('Y-m-d H:i:s')." Routes provided are not in array format.");
            throw new \InvalidArgumentException(
                "Routes provided are not in array format. Format provided: " . 
                (gettype($routes))
            );
        }
        $this->routes = $routes;
    }

    /**
     * Summary of addPrefixes
     * Accepts a single callable or array of callables and adds them to the prefixes. (Callables called before the main matching function).
     * @param mixed $prefixes - Array or single callable to be added.
     * @throws \InvalidArgumentException - If no valid callback is provided.
     * @return void
     */
    public function addPrefixes($prefixes){
        if(is_array($prefixes)){
            foreach($prefixes as $prefix){
                if(!is_callable($prefix)){
                    throw new \InvalidArgumentException(
                        "Prefix must be callable. Received: " . 
                        (gettype($prefix))
                    );
                }
                array_push($this->prefixes, $prefix);
            }
        }
        else{
            if(!is_callable($prefixes)){
                throw new \InvalidArgumentException(
                    "Prefix must be callable. Received: " . 
                    (gettype($prefixes))
                );
            }
            array_push($this->prefixes, $prefixes);
        }
        
    }

        /**
     * Summary of addPrefixes
     * Accepts a single callable or array of callables and adds them to the prefixes. (Callables called before the main matching function).
     * @param mixed $prefixes - Array or single callable to be added.
     * @throws \InvalidArgumentException - If no valid callback is provided.
     * @return void
     */
    public function addSuffixes($suffixes){
        if(is_array($suffixes)){
            foreach($suffixes as $suffix){
                if(!is_callable($suffix)){
                    throw new \InvalidArgumentException(
                        "Suffix must be callable. Received: " . 
                        (gettype($suffix))
                    );
                }
                array_push($this->suffixes, $suffixes);
            }
        }
        else{
            if(!is_callable($suffixes)){
                throw new \InvalidArgumentException(
                    "Suffix must be callable. Received: " . 
                    (gettype($suffixes))
                );
            }
            array_push($this->suffixes, $suffixes);
        }
        
    }
   

    /**
     * Summary of addRoute
     * Adds a route to the existing routes. Also, overwrites a route with the same name.
     * @param mixed $route - The name of the route e.g. 'home'
     * @param mixed $callback - The callback function that will correspond to the route.
     * @param mixed $path - The path the route is supposed to go. If left empty, it will set the route to the root directory.
     * @throws \InvalidArgumentException - If no valid callback is provided or an invalid path is given (In case of nested paths).
     * @return void
     */
    public function addRoute($route, $callback, $path = []){
        if(!is_callable($callback)){
            throw new \InvalidArgumentException(
                "Route callback must be callable. Received: " . 
                (gettype($callback))
            );
        }
        $current = &$this->routes;
        foreach($path as $segment){
            if(!array_key_exists($segment, $current)){
                throw new \InvalidArgumentException(
                    "Route path does not exist. Route path provided: " . 
                    (print_r($path))
                );
            }
            $current = &$current[$segment];
        }

        $current[$route] = $callback;
        return;
    }


    /**
     * Summary of handleRequest
     * Handles all URI requests.
     * Calls the prefix middleware.
     * Segments the request into parts separated by '/'
     * Tries to match each segment with the routes equivalent by checking to see if the segment exists as a key in the routes named array.
     * Calls the suffix middleware.
     * Calls the various default or fallback callable in case of errors: 
     * 404: fallback
     * empty request: default
     * page error: errorPageDefault
     * If none are set, it just echoes a generic message in English
     * @throws \LogicException - If no routes array has been established.
     * @return void
     */
    public function handleRequest() {
        if (empty($this->routes)) {
            throw new \LogicException("No routes defined. Use setRoutes() or addRoute() first.");
        }
       
        $_SERVER['REQUEST_URI'] = strtolower(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL));
        echo $_SERVER['REQUEST_URI'];
        foreach ($this->prefixes as $prefix) {
            call_user_func($prefix);
        }

        $requestSegments = array_filter(explode('/', $_SERVER['REQUEST_URI']));
        
        if (empty($requestSegments)) {
            if(is_callable($this->default)){
                call_user_func($this->default);
                return;
            }
            else{
                echo "<h1>No page path was provided.</h1>";
            }
            return;
        }

        $routesSearch = &$this->routes;
        foreach ($requestSegments as $segment) {
            if (is_array($routesSearch) && array_key_exists($segment, $routesSearch)) {
                $routesSearch = &$routesSearch[$segment];
            } else {
                echo 'Fallback called';
                call_user_func($this->fallback);
                return;
            }
        }

        foreach ($this->suffixes as $suffix) {
            call_user_func($suffix);
        }

        if (!is_callable($routesSearch)) {
            error_log("[AceitRouter] ".date('Y-m-d H:i:s')." Provided callback is not callable");
            if(is_callable($this->default)){
                call_user_func($this->default);
            }
            else{
                echo "<h1>There is an issue with the page you are trying to access.</h1>";
            }
            return;
        }
        call_user_func($routesSearch);
        return;
        
    }
}
