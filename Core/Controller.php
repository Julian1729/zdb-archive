<?php

namespace Core;

/**
 * Base Controller
 */
abstract class Controller
{

  /**
  * Parameters from the matched route
  *
  * @var array
  */
  protected $route_params = [];

  /**
  * Class Constructor
  *
  * @param array $params Parameters from the route
  * @return void
  */
  function __construct($params){
    $this->route_params = $params;
  }

  /**
  * __call
  *
  * This function will be forced to be called on every Controller
  * class because the method that is called from dispatch will never
  * exist
  *
  * @param string $name The name of the method trying to be accessed
  * @param array $args The arguments that were passed to the method
  */
  function __call($name, $args){
    //Build actual method name
    $methodName = $name . 'Action';
    // Make sure that the method exists before we call it
      if( !method_exists($this, $methodName) ){
        throw new \Exception("Method $methodName not found in controller " . get_class($this));
      }
    // Call before and after functions
      if( $this->before() ){
        //only execute rest of code if before doesn't return false
        //call intended method
        call_user_func_array( array($this, $methodName), $args );
        //call after function
        $this->after();
      }
  }

  /**
  * before
  *
  * Executed before a method call.
  * Must return true to allow continue to action call.
  */
  protected function before(){return true;}

  /*
  * after
  *
  * Code to be called after a method call
  */
  protected function after(){}

  protected function session_init($redirect = false){
    // ensure session has started
    if(session_status() == PHP_SESSION_NONE):
      session_start();
    endif;
    // check that the user has not already been authenticated
    if (static::isLoggedIn()){
      // user alrady logged in...redirect to /home
      if($redirect){
        Utils::redirect( '/home');
      }
    }elseif (static::isAdmin()){
      if($redirect){
        Utils::redirect( '/admin/home' );
      }
    }
  }

  /**
   * Redirects to home page if congregation is already authenticated
   * @return void
   */
  protected static function shouldBeAuthenticated(){
    if(!static::isLoggedIn()):
      Utils::redirect();
      die();
    endif;
  }

  /**
   * Redirects to landing page if admin is not authenticated
   * @return void
   */
  protected static function shouldBeAdmin(){
    if(!static::isAdmin()){
      Utils::redirect();
      die();
    }
  }

  /**
   * Redirects to landing page if congregation is not authenticated
   * @return void
   */
  protected static function shouldNotBeAuthenticated(){
    if(static::isLoggedIn()):
      Utils::redirect('/home');
    endif;
  }

  /**
   * Find out whether a congregation is logged in
   * @return boolean True on congregation authenticated
   */
  protected static function isLoggedIn(){
    if (
      (isset( $_SESSION['authenticated'] ) && $_SESSION['authenticated'] === true)
      &&
      isset( $_SESSION['congregationId'] )
    ){
      return true;
    }else {
      return false;
    }
  }

  /**
   * Find out whether the user admin is logged in
   * @return boolean True if admin is is signed in
   */
  protected static function isAdmin(){
    if( isset($_SESSION['admin-authenticated']) && $_SESSION['admin-authenticated'] === true ):
      return true;
    else:
      return false;
    endif;
  }

}


?>
