<?php

namespace App\Controllers;

use \Core\View;
use \Core\Utils;
use \App\Models\Congregations;

/**
 * SignUp Controller
 */
class SignUp extends \Core\Controller
{

  function indexAction(){
    View::renderTemplate('SignUp.html', array(
      'name' => $_SESSION['name']
    ));
  }

  function before(){
    parent::session_init(true);
    // check that token exists
    $dbToken = Congregations::retrieveToken($_SESSION['congregationId']);
    if( !$dbToken ):
      // INVALID TOKEN
      echo 'Invalid Token';
      return false;
    endif;

    // now check that the token passed in GET matches the stored db token
    if( ( isset($_GET['token']) ) && strcmp($_GET['token'], $dbToken) === 0 ):
      return true;
    else:
      // invalid token
      View::renderTemplate('Error.html', array(
        'message' => 'Your token is invalid. Try again.'
      ));
      return false;
    endif;

    return true;
  }

}


?>
