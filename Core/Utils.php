<?php

namespace Core;

use \App\Config;


/**
 * Utils
 */
class Utils
{

  /**
   * Send Location header to redirect user..appended with BASE_URI
   * @param string $url URL to redirect to
   * @return void
   */
  public final static function redirect($url = ''){
    // append with BASE_URI
    header('Location:' . Config::BASE_URI . $url );
    die();
  }

  /**
   * Salts a password using md5 and the primary key of that row
   * @param string Raw password passed in from user input
   * @param integer Rows primary key
   * @return string Salted password
   */
  public final static function salt($pass, $rowId){
    if( !isset($pass) || !isset($rowId) ):
      throw new Exception("Password string, and congregation id needed for salt");
    endif;
    //hash the primary key
    $hash1 = md5($rowId);
    //hash the md5 along with the password
    $hash2 = md5( $hash1 . $pass);
    return $hash2;
  }

  /**
   * Return a cutely formatted date (Month/Day)...if year not the same, return year to
   * @param  string $date Unix formatted date (Y-m-d)
   * @return string Formatted Date
   */
  public final static function cuteDate($date){
    // convert $date to date object to compare year, this will be used regardless
    $createdDate = date_create($date);
    // we need to add the year to format if if is different than current year
    $formattedDate = (strcmp(date('Y'), explode('-', $date)[0]) === 0) ? date_format($createdDate, 'n/j') : date_format($createdDate, 'n/j/Y');
    return $formattedDate;
  }


  /**
   * Generates a token to be used as the identifier for a user that has requested a
   * a password reset
   * @param integer Congregation primary key
   * @return string random string to be used as token
   */
  public final static function generateToken($primaryKey){
    return bin2hex( time() . random_bytes(5) . $primaryKey . random_bytes(5) );
  }

  public final static function console($msg) {
    $msg = str_replace('"', '\\"', $msg); // Escaping double quotes
    echo "<script>console.log(\"$msg\")</script>";
  }

}


?>
