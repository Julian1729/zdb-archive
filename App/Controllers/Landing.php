<?php

namespace App\Controllers;

use \Core\View;
use App\Models\Congregations;
/**
 * Home Controller
 */
class Landing extends \Core\Controller
{

  private static function logout_handler(){
    session_start();
    if( isset($_GET['logout']) ):
      session_destroy();
    endif;
  }

  public function indexAction(){

    View::renderTemplate('Landing.html', array(
      'congregations' => Congregations::getAll(),
    ));

  }

  public function before(){

    // session_start();
    //
    // require_once 'assets/includes.php';//this also starts session
    //
    // //check if the user has requested to be logged out
    // if( array_key_exists('logout', $_GET) ){
    //   //logout user by unsetting all sesssions
    //   session_destroy();
    // }
    static::logout_handler();
    parent::session_init(true);
    return true;
  }

  public function after(){}

}

?>
