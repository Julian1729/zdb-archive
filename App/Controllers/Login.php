<?php

namespace App\Controllers;

use \Core\View;
use App\Models\Congregations;
use \Core\Utils;

/**
 * Login Controller
 */

class Login extends \Core\Controller
{

  function indexAction(){

    View::renderTemplate('Login.html', array(
      'name' => $_SESSION['name']
    ));

  }

  public function before(){
    //congregation get param must be set
    if(!isset( $_GET['congregation'] ) ):
      Utils::redirect();
      return false;
    endif;

    $congId = $_GET['congregation'];

    // start sesssion
    session_start();

    $congregation = Congregations::getCongregation( $congId );

    if(!$congregation){
      // no congregation found...send back home
      Utils::redirect();
      return false;
    }

    // set known session variables
    $_SESSION['congregationId'] = $congregation['congregationId'];
    $_SESSION['name'] = $congregation['name'];
    $_SESSION['email'] = $congregation['email'];

    if( !isset( $congregation['password'] ) ):
      // congregation not signed up
      // generate token
      $token = Utils::generateToken($_SESSION['congregationId']);
      // store token
      Congregations::storeToken($_SESSION['congregationId'], $token);
      // redirect to sign up
      Utils::redirect('/sign-up?token=' . $token);
    endif;

    //set more session variables
    $_SESSION['email'] = $congregation['email'];

    return true;
  }

}


?>
