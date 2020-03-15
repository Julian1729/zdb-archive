<?php

namespace Core;

use PDO;
use \App\Config;
/**
 * Base Model
 */
abstract class Model
{

  protected static $db = null;

  /**
  * connect
  *
  * Establish PDO Database Connection
  *
  * @return mixed
  */
  protected static function connect(){
    //static $db = null;
    if(static::$db === null){
      //first time connecting to DB
      try {
        $db = new PDO(
          "mysql:host=" . Config::DB_HOST .
          ";dbname=" . Config::DB_NAME .
          ";charset=utf8",
          Config::DB_USER,
          Config::DB_PASS);

          //throw an exception when an error occurs
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          return $db;
      } catch (PDOException $e) {
        throw new \Exception($e);
      }

    }
  }

  /**
   * Query the database and return results as assoc array
   * @param  string $query Query string
   * @return array DB Results
   */
  public static function query($query){
    try {
      $db = static::connect();
      $stmt = $db->query($query);
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      throw new \Exception($e);
    }
    if($stmt->rowCount() > 0):
      return $results;
    else:
      return false;
    endif;
  }

  /**
   * Execute a prepared statement on the Database
   * @param  string $query MySQL Query
   * @return void
   */
  public static function preparedQuery($query, $params){
    try {
      $db = static::connect();
      $stmt = $db->prepare($query);
      $stmt->execute($params);
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      throw new \Exception($e);
    }
    if($stmt->rowCount() > 0):
      return $results;
    else:
      return false;
    endif;
  }


  /**
   * Execute a prepared statement
   * @param  string $query  MySQL Statement
   * @param  array $params Prepared Statement parameters
   * @param boolean $returnId When true
   * @return int|boolean Id of the last inserted row when $returnId = true
   * or true on success
   */
  public static function preparedStatement($statement, $params, $returnId = false){
    $db = static::connect();
    $stmt = $db->prepare($statement);
    $stmt->execute($params);

    if($stmt->rowCount() > 0):
      // succesfull
      return $returnId ? $db->lastInsertId() : true;
    else:
      // unsuccesful
      return false;
    endif;
  }


  /**
   * Get the id of the last inserted row
   * @return int PK of last inserted row
   */
  public static function lastId(){
    $db = static::connect();
    return $db->lastInsertId();
  }



}


?>
