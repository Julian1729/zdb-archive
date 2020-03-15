<?php

namespace App\Models;

use PDO;

/**
 * Congregations Model
 */
class Congregations extends \Core\Model
{

  /**
   * Query DB for all congregations
   * @return array Assoc array of all congregations
   */
  public static function getAll(){

    $query = 'SELECT `congregationId`, `name` FROM `congregations`';
    return static::query($query);

  }

  /**
   * Get all rows for a specific congregation
   * @param  integer $id Congregation primary key (congregationId)
   * @return array Assoc array of congregation info
   */
  public static function getCongregation($id){
    $query = "SELECT * FROM `congregations` WHERE `congregationId` = $id";
    // send back the first result [0]
    $results = static::query($query);
    if( !$results ):
      return false;
    else:
      return $results[0];
    endif;
  }

  /**
   * Get one value from a congregation
   * @param  int $id Congregation Primary Key
   * @param  string $column Database row column
   * @return void
   */
  public static function getCongregationCol($id, $column){
    $query =
      "SELECT $column
      FROM `congregations`
      WHERE `congregationId` = ?
      LIMIT 1";
    $results = static::preparedQuery($query, array(
      $id
    ));
    if( !$results ):
      return false;
    else:
      return $results[0][$column];
    endif;
    //return $results;
  }

  /**
   * Sign up a congregation
   * @param  integer $id Congregation Primary Key
   * @param  string $email User inputted email
   * @param  string $password Salted password
   * @return void
   */
  public static function signUp($id, $email, $password){

    $query =
    "UPDATE `congregations`
    SET `email` = ?, `password` = ?
    WHERE `congregationId` = ?
    LIMIT 1";
    return static::preparedStatement($query, array(
      $email,
      $password,
      $id
    ));

  }

  /**
   * Get all visits from a specific congregation
   * @param  integer $id Congregation PK
   * @param  string $from Where to start query, default to current date
   * @return array Array of
   */
  public static function getVisits($id, $from = null){

    $from = ($from == null) ? date('Y-m-d') : $from;

    //echo "date: " . $from;
    $query = "SELECT * FROM `visits` WHERE `endDate` >= ? AND `congregationId` = ? ORDER BY `startDate` ASC";

    return static::preparedQuery( $query, array(
      $from,
      $id
    ));
  }

  /**
   * Return all columns for a single visit
   * @param  integer $congregationId Congregation PK
   * @param  integer $visitId Visit PK
   * @return array Assoc Array
   */
  public static function getVisit($visitId, $congregationId = null){
    $query = "SELECT * FROM `visits` WHERE  `visitId` = ? AND `congregationId` = ?";
    if( is_null($congregationId) ):
      $congregationId = $_SESSION['congregationId'];
    endif;
    return static::preparedQuery( $query, array(
      $visitId,
      $congregationId
    ))[0];
  }

    /**
     * Get only needed to columns from visit to generate a list
     * @param  integer $id Congregation PK
     * @param  string $from Date to use to find visits, default to current date
     * @return array Assoc Array of visits
     */
    public static function getVisitList($id, $from = null){

      $from = ($from == null) ? date('Y-m-d') : $from;

      $query = "SELECT `visitId`, `startDate`, `endDate` FROM `visits` WHERE `endDate` >= ? AND `congregationId` = ? ORDER BY `startDate` ASC";
      $results = static::preparedQuery( $query, array(
        $from,
        $id
      ));

      if($results):
        // format start date and end date into cute date
        for($i=0;$i<count($results);$i++){
          $start = $results[$i]['startDate'];
          $end = $results[$i]['endDate'];
          $results[$i]['startDate'] = \Core\Utils::cuteDate($start);
          $results[$i]['endDate'] = \Core\Utils::cuteDate($end);
        }
      endif;

      return $results;

    }

    /**
     * Store token in congregation row
     * @param  integer $id Congregation PK
     * @param  string $token Generated token
     * @return bool False on faiure
     */
    public static function storeToken($id, $token){
      $query = "UPDATE `congregations` SET `token` = ? WHERE `congregationId` = ?";
      return static::preparedStatement($query, array(
        $token,
        $id
      ));
    }

    /**
     * Get the congregation's name and id that has a specific token
     * @param  string $token Congregation's token
     * @return mixed False on token not found
     */
    public static function findToken($token){
      $query = "SELECT `congregationId`, `name` FROM `congregations` WHERE `token` = ?";
      $r = static::preparedQuery($query, array($token));
      return $r ? $r[0] : false;
    }

    /**
     * Retrieve a congregation's token from database
     * @param  integer $id [description]
     * @return mixed False on no match | string token on success
     */
    public static function retrieveToken($id){
      $query = "SELECT `token` FROM `congregations` WHERE `congregationId` = ?";
      $results = static::preparedQuery($query, array(
        $id
      ));
      return $results ? $results[0]['token'] : false;
    }

    /**
     * Delete a congregations token
     * @param integer $id Congregation's PK
     */
    public static function unsetToken($id){
      $query = "UPDATE `congregations` SET `token` = ? WHERE `congregationId` = ?";
      return static::preparedStatement($query, array(
        '',
        $id
      ));
    }

    /**
     * Delete Congregation and all visits for congregation
     * @param  string|int $id Congregation PK
     * @return boolean True on success
     */
    public static function delete($id){
      $query = "DELETE FROM `congregations` WHERE `congregationId` = ? LIMIT 1";
      $r1 = static::preparedStatement($query, array(
        $id
      ));

      $query = "DELETE FROM `visits` WHERE `congregationId` = ?";
      $r2 = static::preparedStatement($query, array(
        $id
      ));
      return $r1;
    }

    public static function updateAccountSetting($congregationId = null, $setting, $value = ''){
      $query = 'UPDATE `congregations` SET ';
      // change column based on setting change
      switch ($setting) {
        case 'email':
          $query .= '`email`';
          break;
        case 'password':
          $query .= '`password`';
          // we must salt the password ($value)
          $value = \Core\Utils::salt($value, $congregationId);
          break;
        case 'name':
          $query .= '`name`';
          break;
        default:
          throw new \Exception('No setting matched for: ' . '"' . $setting . '"');
          break;
        }
        // close query
        $query .= ' = ? WHERE `congregationId` = ? LIMIT 1';
        if(empty($congregationId)){
          throw new \Exception('No congregation id passed in');
        }
        return static::preparedStatement($query, array(
          $value,
          $congregationId
        ));

    }


  }



?>
