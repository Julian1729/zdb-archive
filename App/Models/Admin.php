<?php

namespace App\Models;

/**
 * Admin Controller
 */
class Admin extends \Core\Model
{

  public static function getCol($col){
    $query = "SELECT `$col` FROM `circuit_overseers` WHERE `overseerId` = 1";
    $results = static::preparedQuery($query, array());
    return $results ? $results[0][$col] : false;
  }

  public static function getPassword()
  {
    $query = "SELECT password FROM `circuit_overseers` WHERE `overseerId` = 1";
    $results = static::preparedQuery($query, array());
    return $results ? $results[0]['password'] : false;
  }

  public static function getVisitList($to){
    $query = "SELECT `visitId`, `congregationId`, `startDate`, `endDate` FROM `visits`";
    if($to):
      $query .= " WHERE `endDate` > ?";
    endif;

    $results = static::preparedQuery($query, ($to?array($to):array()) );

    // format start date and end date into cute date, and enter congregation name into each row
    if($results):
      for($i=0;$i<count($results);$i++){
        // format date
        $start = $results[$i]['startDate'];
        $end = $results[$i]['endDate'];
        $results[$i]['startDate'] = \Core\Utils::cuteDate($start);
        $results[$i]['endDate'] = \Core\Utils::cuteDate($end);
        // get congregation name
        $id = $results[$i]['congregationId'];
        $name = Congregations::getCongregationCol($id, 'name');
        $results[$i]['name'] = $name ? $name : 'Unknown';
      }
    endif;

    return $results;

  }

  public static function updateAdminColumn($col, $value){
    // FIXME: We should not be inserting the column directly into the query
    // start query
    $query = "UPDATE `circuit_overseers` SET $col = ?";
    return static::preparedStatement($query, array(
      $value
    ));
  }

  public static function getCo(){
    $query = "SELECT * FROM `circuit_overseers` WHERE `overseerId` = 1";
    $r = static::preparedQuery($query, array());
    return $r ? $r[0] : false;
  }

  public static function updateSMTP($user, $pass, $name, $port, $host){
    $query = "UPDATE `circuit_overseers` SET `email_username` = ?, `email_password` = ?, `email_from_name` = ?, `email_port` = ?, `email_host` = ? WHERE `overseerId` = 1";
    return static::preparedStatement($query, array(
      $user,
      $pass,
      $name,
      $port,
      $host
    ));
  }



}


?>
