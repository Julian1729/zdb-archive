<?php

namespace App\Controllers\Admin;

use \Core\View;

/**
 * Admin Controller
 */
class Landing extends \Core\Controller
{

  public function loginAction(){
    View::renderTemplate('Admin/Login.html');
  }

  function before(){
    parent::session_init(true);
    return true;
  }


}


?>
