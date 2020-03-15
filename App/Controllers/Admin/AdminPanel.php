<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Admin;
use \App\Models\Congregations;

/**
 * Admin Panel Controller
 */
class AdminPanel extends \Core\Controller
{

  function indexAction(){
    View::renderTemplate('Admin/AdminPanel.html', array(
      'congregations' => Congregations::getAll(),
      // pass in co settings
      'co' => Admin::getCo()
    ));
  }

  function congregationSettings(){
    if(!isset($_GET['congregation'])):
      \Core\Utils::redirect('/admin/home');
    endif;
    $c = Congregations::getCongregation($_GET['congregation']);
    if(!$c):
      // congregation not found
      View::renderTemplate('Error.html', array(
        'message' => 'Congregation Not Found'
      ));
      die();
    endif;

    View::renderTemplate('Admin/CongregationSettings.html', array(
      'c' => $c
    ));
  }

  function before(){
    parent::session_init();
    parent::shouldBeAdmin();
    return true;
  }

}


?>
