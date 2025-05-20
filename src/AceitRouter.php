<?php
namespace AceitDesign\Router;
class AceitRouter {
    private $prefixes = [];
    private $suffixes = [];

    private $fallback;
    private $routes = [];

    private $default;

    private $isCaseSensitive;
    
    /**
     * Summary of __construct
     * @param bool $isCaseSensitive - Determines whether matching is case sensitive or not
     */
    public function __construct($isCaseSensitive = false) {
        $this->isCaseSensitive = $isCaseSensitive;
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
     * @param array $prefixes - Array of callables to be added.
     * @throws \InvalidArgumentException - If no valid callback is provided.
     * @return void
     */
    public function addPrefixes($prefixes){
        
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

        /**
     * Summary of addPrefixes
     * Accepts a single callable or array of callables and adds them to the prefixes. (Callables called before the main matching function).
     * @param array $prefixes - Array of callables to be added.
     * @throws \InvalidArgumentException - If no valid callback is provided.
     * @return void
     */
    public function addSuffixes($suffixes){
        
        foreach($suffixes as $suffix){
            if(!is_callable($suffix)){
                throw new \InvalidArgumentException(
                    "Suffix must be callable. Received: " . 
                    (gettype($suffix))
                );
            }
            array_push($this->suffixes, $suffix);
        }
        
    }
   
    /**
     * Summary of addRoute
     * Adds a route to the existing routes. Also, overwrites a route with the same name.
     * @param array $path - The path the route is supposed to go. The last (or only) element should be the route name. 
     * @param mixed $handler - The callback function that will correspond to the route.
     * @param array $methods    Allowed HTTP methods (default: ['GET'])
     * @param array $middleware - The callbacks that will be chained along with the handler.
     * @throws \InvalidArgumentException - If no valid callback is provided or an invalid path is given (In case of nested paths).
     */
    public function addRoute(array $path, $handler,array $methods = ['GET'], array $middleware = []){
        if(!is_callable($handler)){
            throw new \InvalidArgumentException(
                "Route callback must be callable. Received: " . 
                (gettype($handler))
            );
        }

        $current = &$this->routes;
        for($i = 0; $i < count($path); $i++){
            if(strpos($path[$i], '{') !== false){
                $paramName = trim($path[$i], '{}');
                $current['params'] = $paramName;
               
                continue;
            }
            if(!array_key_exists($path[$i], $current)){
                $current[$path[$i]] = [];
            }
            $current = &$current[$path[$i]];
        }

        $current['handler'] = $handler;
        $current['methods'] = $methods; 
        $current['middleware'] = [];
        foreach($middleware as $callback){
            if(!is_callable($callback)){
                throw new \InvalidArgumentException(
                    "Route callback must be callable. Received: " . 
                    (gettype($handler))
                );
            }
            array_push($current['middleware'], $callback);
        }        
        return;
        
    }

    /**
    * GET route shortcut
    */
    public function get(array $path, $handler, array $middleware = []) {
        return $this->addRoute($path, $handler, ['GET'], $middleware);
    }

    /**
     * POST route shortcut 
     */
    public function post(array $path, $handler, array $middleware = []) {
        return $this->addRoute($path, $handler, ['POST'], $middleware);
    }

    /**
     * PUT route shortcut
     */
    public function put(array $path, $handler, array $middleware = []) {
        return $this->addRoute($path, $handler, ['PUT'], $middleware);
    }

    /**
     * DELETE route shortcut
     */
    public function delete(array $path, $handler, array $middleware = []) {
        return $this->addRoute($path, $handler, ['DELETE'], $middleware);
    }

    /**
     * PATCH route shortcut
     */
    public function patch(array $path, $handler, array $middleware = []) {
        return $this->addRoute($path, $handler, ['PATCH'], $middleware);
    }


    /**
     * Summary of handleRequest
     * Handles all URI requests.
     * Calls the prefix middleware.
     * Segments the request into parts separated by '/'
     * Tries to match each segment with the routes equivalent by checking to see if the segment exists as a key in the routes named array.
     * Calls route middleware
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
       
        if(!$this->isCaseSensitive){
            $_SERVER['REQUEST_URI'] = strtolower(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL));
        }
        
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
        $params = [];
        $lastValidPath = null;
        for($i=0; $i < count($requestSegments); $i++){
            
            if (array_key_exists($requestSegments[$i], $routesSearch)) {
                $routesSearch = &$routesSearch[$requestSegments[$i]];
            } else {
                echo 'Fallback called';
                call_user_func($this->fallback);
                return;
            }
            if(array_key_exists('handler', $routesSearch)){
                $lastValidPath = $routesSearch;
            }
            if(array_key_exists('params', $routesSearch)){
                $i++;
                $params[$routesSearch['params']]=$requestSegments[$i];
            }
        }

        foreach ($this->suffixes as $suffix) {
            call_user_func($suffix);
        }
        

        if (!is_callable($lastValidPath['handler'])) {
            error_log("[AceitRouter] ".date('Y-m-d H:i:s')." Provided callback is not callable");
            if(is_callable($this->fallback)){
                call_user_func($this->fallback);
            }
            else{
                echo "<h1>There is an issue with the page you are trying to access.</h1>";
            }
            return;
        }
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if (!in_array($requestMethod, $lastValidPath['methods'])) {
            header('HTTP/1.1 405 Method Not Allowed');
            die("Method $requestMethod not allowed");
        }
        foreach ($lastValidPath['middleware'] as $middleware) {
            call_user_func($middleware);
        }
        call_user_func($lastValidPath['handler'], $params);
        return;
        
    }
}
