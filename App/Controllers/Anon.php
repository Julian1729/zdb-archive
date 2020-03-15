<?php

namespace App\Controllers;

use \App\Models\Visit;
use \App\Models\Congregations;
use \Core\View;

/**
 * Anon(ymous) Controller
 */
class Anon extends \Core\Controller
{

  function visitAction(){
    $token = $this->route_params['token'];
    $v = Visit::getVisitByToken($token);
    if(!$v){
      View::renderTemplate('Error.html', array(
        'title' => 'Invalid URL',
        'message' => 'Visit Not Found'
      ));
      die();
    }
    // set session variables for congregation
    $c = Congregations::getCongregation($v['congregationId']);
    $_SESSION['email'] = $c['email'];
    $_SESSION['name'] = $c['name'];
    $_SESSION['congregationId'] = $v['congregationId'];

    // reverse key and value pair in "name to column" to "column to name"
    $nameToColumn = \App\Models\Visit::VISIT_COLUMNS;
    $columnToName = array();
    foreach ($nameToColumn as $name => $column) {
      $columnToName[$column] = $name;
    }

    View::renderTemplate('AnonVisit.html', array(
      'c' => $c,
      'v' => $v,
      'column_map' => $columnToName
    ));
  }

  function before(){
    parent::session_init();
    return true;
  }

}


?>
