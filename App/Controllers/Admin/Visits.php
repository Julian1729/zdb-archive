<?php

namespace App\Controllers\Admin;

use \App\Models\Admin;
use \App\Models\Visit;
use \App\Models\Congregations;
use \Core\View;

/**
 * Admin Visit Controller
 */
class Visits extends \Core\Controller
{

  public function viewAction($v = null){
    $visitId = $this->route_params['id'];
    // get visit
    if(!isset($v)): // if instantiated from todayAction, $v may be set
      $v = Visit::getVisit($visitId);
    endif;

    if(!$v){
      // visit not found
      View::renderTemplate('Error.html', array(
        'message' => 'Visit Not Found'
      ));
      die();
    }

    $dayPrefix = array('wed', 'thurs', 'fri');
    // get google maps url and inject into array
    foreach ($dayPrefix as $pre):
      $v[$pre.'_googlemapsUri'] = Visit::getGoogleMapsUri(
        $v[$pre.'_meal_host_street'],
        $v[$pre.'_meal_host_city'],
        $v[$pre.'_meal_host_state'],
        $v[$pre.'_meal_host_country'],
        $v[$pre.'_meal_host_zip']);
    endforeach;

    // add visit url
    $v['visitUrl'] = \App\Config::BASE_URI . '/anon/visit/' . $v['visitId'];

    View::renderTemplate('Admin/Visit.html', array(
      'v' => $v
    ));
  }

  public function todayAction(){
    // get visit id
    $visitId = $this->route_params['id'];
    // get visit
    $v = Visit::getVisit($visitId);
    if(!$v){
      // visit not found
      View::renderTemplate('Error.html', array(
        'message' => 'Visit Not Found'
      ));
      die();
    }
    // get start date and end date, convert to timestamps
    $start = strtotime( $v['startDate'] );
    $end = strtotime( $v['endDate'] );
    $today = strtotime( date('Y-m-d') );
    //$today = strtotime( '2019-05-06' );

    // check if today falls between dates
    $startCheck = ($today >= $start);
    $endCheck = ($today <= $end);

    if(!$startCheck || !$endCheck):
      // not in today, redirect to standard view
      \Core\Utils::redirect('/admin/visits/view/' . $visitId);
      // FIXME: Alternatively we can just instantiate the viewAction function
      // here ... the only thing is, the url would still say .../visits/today
      return;
    endif;

    // determine the day of the week
    $day = date('w', $today);

    //add google maps uri to visit
    $triDay = array('wed', 'thurs', 'fri');
    foreach ($triDay as $pre):
      $v[$pre.'_googlemapsUri'] = Visit::getGoogleMapsUri(
        $v[$pre.'_meal_host_street'],
        $v[$pre.'_meal_host_city'],
        $v[$pre.'_meal_host_state'],
        $v[$pre.'_meal_host_country'],
        $v[$pre.'_meal_host_zip']);
    endforeach;


    View::renderTemplate('Admin/TodaysVisit.html', array(
      'v' => $v,
      // pass in the congregation name
      'name' => Congregations::getCongregationCol($v['congregationId'], 'name'),
      // pass in the day of the week
      'dow' => $day
    ));

  }

  public function before(){
    parent::session_init();
    parent::shouldBeAdmin();
    return true;
  }

}


?>
