<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Congregations;

/**
 * Home Controller (Congregation)
 */
class Home extends \Core\Controller
{

  function indexAction(){
    //get visits for congregation
    View::renderTemplate('Home.html', array(
      'visits' => Congregations::getVisitList( $_SESSION['congregationId'] ),
    ));
  }

  function before(){
    parent::session_init();
    parent::shouldBeAuthenticated();
    return true;
  }

  function visitAction(){
    $visitId = $this->route_params['id'];
    // reverse key and value pair in "name to column" to "column to name"
    $nameToColumn = \App\Models\Visit::VISIT_COLUMNS;
    $columnToName = array();
    foreach ($nameToColumn as $name => $column) {
      $columnToName[$column] = $name;
    }
    View::renderTemplate('Visit.html', array(
      'v' => Congregations::getVisit($visitId),
      'column_map' => $columnToName
    ));
  }

  function accountAction(){
    View::renderTemplate('Account.html');
  }

}


?>
