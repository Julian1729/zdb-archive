<?php

namespace App\Models;

/**
 * Access visits
 */
class Visit extends \Core\Model
{

  const VISIT_COLUMNS = array(
    'm1' => 'wed_meal_host_name',
    'm2' => 'wed_meal_host_phone',
    'm3' => 'wed_meal_host_street',
    'm4' => 'wed_meal_host_city',
    'm6' => 'wed_meal_host_country',
    'm5' => 'wed_meal_host_state',
    'm7' => 'wed_meal_host_zip',
    'm8' => 'wed_meal_host_directions',
    'm9' => 'thurs_meal_host_name',
    'm10' => 'thurs_meal_host_phone',
    'm11' => 'thurs_meal_host_street',
    'm12' => 'thurs_meal_host_city',
    'm14' => 'thurs_meal_host_country',
    'm13' => 'thurs_meal_host_state',
    'm15' => 'thurs_meal_host_zip',
    'm16' => 'thurs_meal_host_directions',
    'm17' => 'fri_meal_host_name',
    'm18' => 'fri_meal_host_phone',
    'm19' => 'fri_meal_host_street',
    'm20' => 'fri_meal_host_city',
    'm22' => 'fri_meal_host_country',
    'm21' => 'fri_meal_host_state',
    'm23' => 'fri_meal_host_zip',
    'm24' => 'fri_meal_host_directions',
    'e1' => 'chairman_lifeministry',
    'e2' => 'chairman_publictalk',
    'e_add_1' => 'wtconductor',
    'e3' => 'meetingtime_lifeministry',
    'e4' => 'meetingday_publictalk',
    'e5' => 'meetingtime_publictalk',
    'e6' => 'wed_fieldservice_location',
    'e7' => 'wed_fieldservice_time',
    'e8' => 'thurs_fieldservice_location',
    'e9' => 'thurs_fieldservice_time',
    'e10' => 'fri_fieldservice_location',
    'e11' => 'fri_fieldservice_time',
    'e12' => 'sat_fieldservice_location',
    'e13' => 'sat_fieldservice_time',
    'e14' => 'sun_fieldservice_location',
    'e15' => 'sun_fieldservice_time',
    'e16' => 'pioneermeeting_day',
    'e17' => 'pioneermeeting_time',
    'e19' => 'eldersmeetingpoints',
    'e18' => 'eldermsmeeting_time',
    'e20' => 'publisherjudicial',
    'e21' => 'publishernoprayer',
    'activities' => 'activities_json'
  );

  const SERVICE_FORM = array(
    's1' => 'g',
    's2' => 'g',
    's3' => 'g',
    's4' => 'g',
    's5' => 'g',
    's10' => 'g',
    's12' => 'g',
    's13' => 'g',
    's14' => 'g',
    's15' => 'g',
    's16' => 'g',
    's21' => 'g',
    's23' => 'g',
    's28' => 'g',
    'activities' => 'g',
    'visitId' => 'required'
  );

  const MEAL_FORM = array(
    'm1' => 'required',
    'm2' => 'required',
    'm3' => 'required',
    'm4' => 'required',
    'm5' => 'g',
    'm6' => 'g',
    'm7' => 'g',
    'm8' => 'required',
    'm9' => 'required',
    'm10' => 'required',
    'm11' => 'required',
    'm12' => 'required',
    'm13' => 'g',
    'm14' => 'g',
    'm15' => 'g',
    'm16' => 'required',
    'm17' => 'required',
    'm18' => 'required',
    'm19' => 'required',
    'm20' => 'required',
    'm21' => 'g',
    'm22' => 'g',
    'm23' => 'g',
    'm24' => 'required',
    'visitId' => 'required'
  );

  const ELDER_FORM = array(
    'e1' => 'required',
    'e2' => 'required',
    'e_add_1' => 'required',
    'e3' => 'required',
    'e4' => 'required',
    'e5' => 'required',
    'e6' => 'required',
    'e7' => 'required',
    'e8' => 'required',
    'e9' => 'required',
    'e10' => 'required',
    'e11' => 'required',
    'e12' => 'required',
    'e13' => 'required',
    'e14' => 'required',
    'e15' => 'required',
    'e16' => 'required',
    'e17' => 'required',
    'e18' => 'required',
    'e19' => 'g',
    'e20' => 'g',
    'e21' => 'g',
    'visitId' => 'required'
  );

  public static function saveVisit($congregationId, $visitId, $values){
    // values : column => value
    $query = "UPDATE `visits` SET ";
    // build query by concatenating column = ?
    $counter = 1;
    $last = count($values);
    foreach ($values as $col => $val):
      $query .= $col . " = ?";
      if($counter !== $last){
        // if not last interation, add comma
        $query .= ", ";
        $counter++;
      }
    endforeach;
    // end query
    $query .= " WHERE `congregationId` = ? AND `visitId` = ? LIMIT 1";
    //add congregation id to values

    // build params array
    $params = array_values($values);
    // add congregation id to end of array
    $params[] = $congregationId;
    // add visit id to end of array
    $params[] = $visitId;
    // echo $query;
    // die();
    return static::preparedStatement($query, $params);
  }

  public static function getVisit($visitId){
    $query = "SELECT * FROM `visits` WHERE `visitId` = ?";
    $r = static::preparedQuery($query, array($visitId));
    if(!$r){
      return false;
    }
    $res = $r[0];
    // decode afternoon activities from json
    $res['activities_json'] = json_decode( $res['activities_json'], true );

    return $res;
  }

  /**
   * Query for a visit using token
   * @param  $string $token Visit token
   * @return array Visit Columns
   */
  public static function getVisitByToken($token){
    $query = "SELECT * FROM `visits` WHERE `token` = ?";
    $r = static::preparedQuery($query, array(
      $token
    ));
    return $r ? $r[0] : $r;
  }

  public static function getGoogleMapsUri( $street = '', $city = '', $state = '', $country = '', $zip ='' ){
    $googleMapsBase = "https://www.google.com/maps/dir//";
    $enc = urlencode(
      $street . ' ' .
      $city . ' ' .
      $state . ' ' .
      $country . ' ' .
      $zip
    );
    $url = $googleMapsBase . $enc;
    return $url;
  }

  public static function addVisit($congregationId, $startDate, $endDate, $comments = ''){
    $query = "INSERT INTO `visits` (congregationId, startDate, endDate, comments) VALUES (?, ?, ?, ?)";

    $visitId = static::preparedStatement($query, array(
      $congregationId,
      $startDate,
      $endDate,
      $comments
    ), true);
    if(!$visitId):
      throw new \Exception("Could not insert visit.");
    endif;
    // generate token
    return static::setToken($visitId);

  }

  public static function currentVisit(){
    $today = date('Y-m-d');
    $query = "SELECT * FROM `visits` WHERE `startDate` <= ? AND `endDate` >= ? LIMIT 1";
    $r = static::preparedQuery($query, array(
      $today,
      $today
    ));
    return $r ? $r[0] : false;
  }

  public static function deleteVisit($id){
    $query = "DELETE FROM `visits` WHERE `visitId` = ? LIMIT 1";
    return static::preparedStatement($query, array(
      $id
    ));
  }

  /**
   * Generate and set a token for specified visit
   * @param string|int $visitId Visit PK (visitId)
   * @return boolean False on failure
   */
  public static function setToken($visitId){
    $query = "UPDATE `visits` SET `token` = ? WHERE `visitId` = ?";
    // generate token
    $token = \Core\Utils::generateToken($visitId);
    return static::preparedStatement($query, array(
      $token,
      $visitId
    ));
  }

  /**
   * Set token to empty string for a visit
   * @param string|int $visitId Visit PK
   */
  public static function unsetToken($visitId){
    $query = "UPDATE `visits` SET `token` = '' WHERE `visitId` = ?";
    return static::preparedStatement($query, array(
      $visitId
    ));
  }

  /**
   * Return token for  avisit
   * @param  string|int $visitId Visit PK
   * @return string|boolean Visit token or false on failure
   */
  public static function retrieveToken($visitId){
    $query = "SELECT `token` FROM `visits` WHERE `visitId` = ?";
    $r = static::preparedQuery($query, array(
      $visitId
    ));
    return $r ? $r[0]['token'] : $r;
  }



}


?>
