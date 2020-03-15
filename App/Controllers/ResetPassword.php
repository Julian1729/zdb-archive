<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Congregations;

/**
 * Reset Password Controller
 */
class ResetPassword extends \Core\Controller
{

  public function indexAction(){
    View::renderTemplate('ResetPassword.html', array());
  }

  function before(){
    session_start();
    //check for GET param
    if(!isset($_GET['token'])):
      // token isn't set, show error page
      View::renderTemplate('Error.html', array(
        'title' => 'Are you lost?',
        'message' => 'You have no token'
      ));
    endif;

    // get congregation with token
    $congInfo = Congregations::findToken($_GET['token']);
    // check if token was found
    if(!$congInfo):
      // token not found, show error page
      View::renderTemplate('Error.html', array(
        'title' => 'Invalid Token',
        'message' => 'Try requesting a password reset email again.'
      ));
    endif;
    // set session vars
    session_unset();
    $_SESSION['congregationId'] = $congInfo['congregationId'];
    $_SESSION['name'] = $congInfo['name'];
    return true;
  }

}


?>
