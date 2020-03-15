<?php

namespace Core;

/**
 * Router
 */
class Router
{

  /*
    Associative array of routes (routing table)
    @var array
  */
  protected $routes = array();

  /*
    Parameters from the matched route
    @var array
  */
  protected $params = array();

  /**
  * getRoutes
  *
  * Return all routes from the routing table
  *
  * @param $print When true, print_r() routing table, Default false.
  * @return array, void
  */
  function getRoutes($print = false){
    if($print == true){
      echo '<pre>' .  htmlspecialchars( print_r($this->routes, true) ) . '</pre>';
    }else{
      return $this->routes;
    }
  }

  /**
  * getParams
  *
  * Get the currently matched route's params
  *
  * @return array
  */
  public function getParams($print = false){
    if($print == true){
      echo '<pre>' .  htmlspecialchars( print_r($this->params, true) ) . '</pre>';
    }else{
      return $this->params;
    }
  }

  /**
  * add
  *
  * Add a route to the routing table
  *
  * @param string $route The Route URL ($_SERVER['QUERY_STRING'])
  * @param array $params Parameters (controllers, action, etc)
  * @return void
  */
  public function add( $route, $params = array() ){

    //convert the route to a regular expression
      //escape forward slashes
      $route = preg_replace('/\//', '\\/', $route);
      //convert variables to capture groups
      $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-_]+)', $route);
      //convert variables with custom regular expressions e.g. {id:\d+}
      $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
      //add start and end delimeters, forgive trailing slash and add case insensitive flag
      $route = '/^' . $route . '\/?$/i';

    //add route to object
    $this->routes[$route] = $params;

  }

  /**
  * match
  *
  * Match a URL to a route in the routing table
  * @param string $url The Route
  * @return boolean True on match
  */
  protected function match($url){

    //loop through routing table ($this->routes), check for a regex match to the route
    foreach ($this->routes as $route => $params):
      if( preg_match($route, $url, $matches) ){
        //matches contains the matches (params)
        // ($matches example) Array ( [0] => contolla/actioon [controller] => contolla [1] => contolla [action] => actioon [2] => actioon )
        foreach ($matches as $key => $match) {
          // we only want the custom named capture groups (strings)
          if(is_string($key)){
            //store new params from regex capture groups in the $routes params
            $params[$key] = $match;
          }
        }
        //store routes params in the instace of this object
        $this->params = $params;
        return true;
      }
    endforeach;
    //no match found
    return false;

  }

  /**
  * dispatch
  *
  * Dispatch the router, create the correct controller object, and running the right method
  *
  * @param string $url The route url ($_SERVER['QUERY_STRING'])
  */
  public function dispatch($url){

    /*
      Before checking for a match we must get only the first GET Param,
      if others were added to the URL (?key=value), we must remove them
    */
      if($url != ''){
        $parts = explode('&', $url, 2);
        if( strpos($parts[0], '=') === false ){
          //if no '=' was not found, no extra GET params passed in, url is valid
          $url = $parts[0];
        }else {
          //only get vars were passed in, empty URL to send to Home Page
          $url = '';
        }
      }

    // Handle a match
      if( $this->match($url) ){
        // Filter Controller
          //route params are now inside object instance $this->params
          $controller = $this->params['controller'];
          //convert to StudlyCaps as this is how the actual Controller class should be defined
          $controller = $this->convertToStudlyCaps($controller);
          //Check if a namespace has been specified for the conroller in the route's params
          $controller = $this->getNamespace() . $controller;

        // Make sure that the controller class exists before we instantiate it
        if( !class_exists($controller) ){
          throw new \Exception("Controller class: $controller not found");
        }
          //class exists...create the object
          $controllerObj = new $controller($this->params);

          // Filter Action...default to index
            $action = (isset($this->params['action'])) ? $this->params['action'] : 'index';

            //convert action string to camelCase, as this is how the class method should be defined
            $action = $this->convertToCamelCase($action);
            //the actual method will have 'Action' appended to the end,
            //this is done to force execution of the __call function, this can be bypassed however,
            //if the user inputted URL has 'Action' explicitly appended to
            //...check that the action does not have action appeded to it
            if( preg_match( '/action$/i', $action) ){
              throw new \Exception("Method $action in controller $controller cannot be called directly - remove the Action suffix to call this method");
            }
            //Finally, call Action on controller
            //THE CONTROLLER METHOD WILL NOT EXIST, THIS WILL FORCE THE EXECUTION OF THE
            // __call METHOD THAT SHOULD BE DEFINED IN THE CONTAINING CLASS
            $controllerObj->$action();
      }else {
        //No route matched
        throw new \Exception("No route matched for $url", 404);
      }

  }

  /**
  * convertToStudlyCaps
  *
  *Convert strings with hyphens to studly caps
  * e.g. post-authors > PostAuthors
  *
  * @param string $string The string to convert
  * @return string
  */
  protected function convertToStudlyCaps($string){
    //replace all hypens with space
    //run through ucwords() which makes each word uppercase
    //remove spaces
    return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
  }

  /**
  * convertToCamelCase
  *
  *  Convert to camelCase
  *  e.g. add-new > addNew
  *
  *  @param string $string THe string to convert
  *  @return string
  */
  protected function convertToCamelCase($string){
    return lcfirst($this->convertToStudlyCaps($string));
  }

  /**
  * getNamespace
  *
  * Get the namespace for the conroller class. The namespace defined
  * is in the the route paramters if present
  *
  * @return string The request URL
  */
  public function getNamespace(){
    //base namepace
    $namespace = 'App\Controllers\\';
    //if the namepace is defined
    if(array_key_exists('namespace', $this->params)){
      $namespace .= $this->params['namespace'] . '\\';
    }
    return $namespace;
  }

}


?>
