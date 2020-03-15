<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Admin;
use \App\Models\Visit;

/**
 * Home Controller (Admin)
 */
class Home extends \Core\Controller
{

  function indexAction(){
    $currentVisit = Visit::currentVisit();
    if($currentVisit):
      // add congregation name to array
      $currentVisit['name'] = \App\Models\Congregations::getCongregationCol(
        $currentVisit['congregationId'],
        'name'
      );
      // cute date the start date and end date
      $start = $currentVisit['startDate'];
      $end = $currentVisit['endDate'];
      // reformat and inject back into array
      $currentVisit['startDate'] = \Core\Utils::cuteDate($start);
      $currentVisit['endDate'] = \Core\Utils::cuteDate($end);
    endif;
    //get visits for congregation
    View::renderTemplate('Admin/Home.html', array(
      'visit' => Admin::getVisitList(null),
      'congregations' => \App\Models\Congregations::getAll(),
      'currentVisit' => $currentVisit
    ));
  }


  function before(){
    parent::session_init();
    parent::shouldBeAdmin();
    //must check for required session variables
    return true;
  }

}


?>
