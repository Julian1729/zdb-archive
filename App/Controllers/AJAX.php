<?php

namespace App\Controllers;

use \Core\Utils;
use App\Config;
use App\Models\Congregations;
use App\Models\Visit;
use App\Models\Admin;

/**
 * AJAX Controller
 * All AJAX calls will be routed through here
 */
class AJAX
{


  function __construct(){
    // OPTIMIZE: Check HTTP Referer to make sure request originated from this server
    // ...

    //call necessary action as soon as instantiated
      // get action from POST, throw error if not present
      if( !isset( $_POST['action'] ) || $_POST['action'] === ''):
        throw new \Exception("\$_POST['action'] not present");
      endif;

    $action = $_POST['action'];

    if( !is_callable($action, true) ):
      throw new \Exception("$action not a valid method");
    endif;

    // start session as this is a brand new request
    session_start();

    $this->$action();
    // kill script
    die();
  }

  private static function jsObj($code = 1, $errors = array(), $extra = array()){
    // to be turned into json obj
    $array = array();
    // enter code
    $array['code'] = $code;
    // if errors enter $errors
    if(!empty($errors)){
      $array['errors'] = $errors;
    }
    // loop throough extras and enter into array
    foreach ($extra as $key => $value) {
      $array[$key] = $value;
    }
    // json encode array
    $json = json_encode($array);
    return $json;
  }


  /**
   * Output the Referer HTTP Header
   * @return void
   */
  function referer(){
    echo $_SERVER['HTTP_REFERER'];
  }

  /**
   * Handle congregation login form submission
   */
  function congregationLogin(){
    $f = new \Formval();
    $f->expecting(array(
      'password-input' => array(
        'required' => true,
      )
    ));

    if( !$f->validate() ):
      echo 0;
      die();
    endif;

      // get congregation id from sesssion
      $congId = $_SESSION['congregationId'];
      // salt the user inputted password
      $saltedPassword = Utils::salt($f->{'password-input'}, $congId);
      $dbPassword = Congregations::getCongregationCol($congId, 'password');
      // check that database password exists
      if( $dbPassword === false ):
        throw new Exception("Database returned empty results");
      endif;
      // check if the passwords match
      if( strcmp($saltedPassword, $dbPassword) === 0 ):
        // succesfull login...set session variable
        $_SESSION['authenticated'] = true;
        echo 1;
        die();
      else:
        // unsuccesful login...return 0 and die
        echo 0;
        die();
      endif;
  }

  /**
   * handle congregation sign up form submission
   */
  function congregationSignUp(){

    $f = new \Formval();
    $f->expecting(array(
      'email' => array(
        'required' => true,
        'regex' =>  '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/'
      ),
      'password' => array(
        'required' => true,
        'min-len' => 6,
        'max-len' => 30
      ),
      'password-confirm' => array(
        'required' => true,
        'match' => 'password'
      ),
      'bod-checkbox' => array(
        'required' => true
      )
    ));
    if( !$f->validate() ):
      $f->return();
    endif;

      $congId = $_SESSION['congregationId'];
    // Salt Password
    $saltedPassword = Utils::salt($f->password, $congId);
    // Sign Up Congregation
    if( Congregations::signUp(
          $_SESSION['congregationId'],
          $f->email,
          $saltedPassword
        )
      ){
        // succesfull sign up
        // unset congregation token
        Congregations::unsetToken($_SESSION['congregationId']);
        // authenticate session
        $_SESSION['authenticated'] = true;
        echo 1;
      }else {
        throw new \Exception("Could not enter DB");
      }

  }

  function forgotPassword(){
    // Generate token for congregation
    $token = Utils::generateToken($_SESSION['congregationId']);
    // Store token in congregation db row
    if(!Congregations::storeToken($_SESSION['congregationId'], $token)){
      echo 1;
      die();
    }
    // Send email to congregation
    echo \Core\Email::sendForgotEmail($_SESSION['email'], $token);
    die();
    // echo success
  }

  // Handle visit form
  function visitForm(){
    // print_r($_POST);
    // die();
    // //////

    $f = new \Formval();
    $f->setType('required', array(
      'required' => true
    ));
    $f->setType('g', array(
      'required' => false
    ));

    // create array to send to visit function (column_name => value)
    $set = array();

    switch ($_POST['form']) {
      case 'serviceform':
        $expecting = Visit::SERVICE_FORM;
        //$serviceForm = true;
        break;
      case 'mealform':
        $expecting = Visit::MEAL_FORM;
        break;
      case 'elderform':
        $expecting = Visit::ELDER_FORM;
        break;
      default:
        throw new \Exception("Form string not passed in");
        break;
    }

    $f->expecting($expecting);

    $validate = $f->validate();

    // OPTIMIZE: There is a better way of doing this
    // Check if this is serviceform, if so, add the activities to the set
    // if($serviceForm){
    //   // get value of activities property
    //   //print_r($f->goodInputs);
    //   $activitiesJSON = $f->activities;
    //   // add to set to be injected into db
    //
    // }

    // ===========================================

    // name => column
    $columns = Visit::VISIT_COLUMNS;

    foreach ($f->goodInputs as $name => $value):
      if(array_key_exists($name, $columns)){
        $col = $columns[$name];
        // default an empty string to null
        $value = empty($value) ? null : $value;
        // checkboxes come in a arrays, just turn them into
        // comma seperated string
        $value = is_array($value) ? implode(', ', $value) : $value;
        // enter value into $set array
        $set[$col] = $value;
      }
    endforeach;

    // run statement, send back error code 3 on fail
    if( !Visit::saveVisit($_SESSION['congregationId'], $f->goodInputs['visitId'], $set) ):
      // if validation failed, at least send back form errors
      echo static::jsObj(3, (!$validate ? $f->badInputs : array() ) );
      die();
    endif;

    if( $validate ){
      // success send back success code
      echo static::jsObj(1);
      die();
    }else {
      echo static::jsObj(0, $f->badInputs);
      die();
    }

  }

  function congregationAccountSettings(){
    // validate input
      // make sure form name passed in
      if(!isset($_POST['form'])): throw new \Exception("No form name passed in."); endif;
      //init formval
      $f = new \Formval();
      $setting = '';
      // set expecting based on form
      switch ($_POST['form']) {
        case 'email':
          $setting = 'email';
          $value = 'email';
          $f->expecting(array(
            'email' => array(
              'required' => true,
              'regex' =>  '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/'
            )
          ));
          break;
        case 'password':
          $setting = 'password';
          $value = 'password';
          $f->expecting(array(
            'password' => array(
              'required' => true,
              'min-len' => 6,
              'max-len' => 30
            ),
            'password-confirm' => array(
              'match' => 'password'
            )
        ));
          break;
        case 'name':
          $setting = 'name';
          $value = 'name';
          $f->expecting(array(
            'name' => array(
              'required' => true
            )
          ));
          break;
        default:
          throw new \Exception('No form matched for "' . $_POST['form'] . '"');
          break;
        }

        //validate form
        $validate = $f->validate();
        if(!$validate){
          echo static::jsObj(0, $f->badInputs);
          die();
        }

        //all inputs are validated and passed
        $congregationId = isset($_SESSION['congregationId']) ? $_SESSION['congregationId'] : $f->congregation;
        if( Congregations::updateAccountSetting($congregationId, $setting, $f->{$value}) ):
          // update session variables if they are signed in
          if( isset($_SESSION['authenticated']) ):
            if( $setting == 'name' ){
              $_SESSION['name'] = $f->{$value};
            }elseif ($setting == 'email') {
              $_SESSION['email'] = $f->{$value};
            }
          endif;
          echo static::jsObj(1, null, array(
            'setting' => $setting,
            'value' => $setting !== 'password' ? $f->{$value} : null
          ));
          die();
        else:
          echo static::jsObj(3, (!$validate ? $f->badInputs : array()));
          die();
        endif;
    }

    function congregationResetPassword(){
      $f = new \Formval();
      $f->expecting(array(
        'password' => array(
          'required' => true,
          'min-len' => 6,
          'max-len' => 30
        ),
        'password-confirm' => array(
          'required' => true,
          'match' => 'password'
        )
      ));
      $validate = $f->validate();
      if(!$validate):
        echo static::jsObj(0, $f->badInputs);
        die();
      endif;
      // enter into db
      if( !Congregations::updateAccountSetting(
        $_SESSION['congregationId'],
        'password',
        $f->password
        ) ):
        // system error occured
        echo static::jsObj(3, $f->badInputs);
        die();
      else:
        // successfull reset...authenticate
        $_SESSION['authenticated'] = true;
        // unset token
        Congregations::unsetToken($_SESSION['congregationId']);
        echo static::jsObj(1);
      endif;
    }

    function formFillData(){
      if(!isset($_POST['visitId'])):
        throw new \Exception("No visitId");
      endif;
      // get visit info
      $visitInfo = Visit::getVisit($_POST['visitId']);
      if(!$visitInfo):
        echo jsObj(0);
        die();
      endif;
      // get name to column
      $nameToColumn = Visit::VISIT_COLUMNS;
      // store name to value
      $nameToValue = array();
      // loop through inputs and store non empty values in
      foreach ($visitInfo as $column => $value) {
        if(!is_null($value) && $value != ""):
          // find corresponding column input name
          $name = array_search($column, $nameToColumn);
          // store pair in array
          $nameToValue[$name] = $value;
        endif;
      }
      echo jsObj(1, null, array($nameToColumn));
      die();
    }

    // ===== ADMIN =====
    function adminLogin(){
      $f = new \Formval();
      $f->expecting(array(
        'password' => array(
          'required' => true
        )
      ));

      $validate = $f->validate();
      if(!$validate):
        echo static::jsObj(0, $f->badInputs);
        die();
      endif;

      // validate password
        // salt password
        $saltedPass = \Core\Utils::salt(1, $f->password);
        // get db password
        $dbPass = Admin::getPassword();
        if( !$dbPass ){
          echo static::jsObj(3);
          die();
        }
        // compare passwords
        if( strcmp($saltedPass, $dbPass) === 0 ):
          // authenticate
          $_SESSION['admin-authenticated'] = true;
          $_SESSION['email'] = Admin::getCol('email');
          echo static::jsObj(1);
        else:
          // i use an array here to trick my formvalErrorHandler into using it
          // to grab the errored inputs
          echo static::jsObj(0, array('password' => false));
        endif;

    }

    function adminAccountSettings(){
      if(!isset($_POST['form'])):
        throw new \Exception("No form passed in");
      endif;
      $f = new \Formval();
      switch ($_POST['form']) {
        case 'password':
          $col = 'password';
          $val = 'password';
          $f->expecting(array(
            'password' => array(
              'required' => true,
              'min-len' => 6
            ),
            'password-confirm' => array(
              'required' => true,
              'match' => 'password'
            )
          ));
          break;
        case 'email':
          $col = 'email';
          $val = 'email';
          $f->expecting(array(
            'email' => array(
              'required' => true,
              'regex' =>  '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/'
            )
          ));
          break;
        default:
          throw new \Exception('No form matched for "' . $_POST['form'] . '"');
          break;
      }
      $v = $f->validate();
      if(!$v):
        echo static::jsObj(0, $f->badInputs);
        die();
      endif;

      // if updating password, we must salt it
      if($_POST['form'] === 'password'):
        $f->password = \Core\Utils::salt(1, $f->password);
      endif;

      if( !Admin::updateAdminColumn($col, $f->{$val}) ):
        echo static::jsObj(3);
      else:
        // update session var
        if($_POST['email'] === 'email'):
          $_SESSION['email'] = $f->{$val};
        endif;
        echo static::jsObj(1);
      endif;

    }

    function addVisit(){
      $f = new \Formval();
      $f->setType('date', array(
        'required' => true,
        'regex' => '/\d{4}-\d{2}-\d{2}/'
      ));
      $f->expecting(array(
        'start-date' => 'date',
        'end-date' => 'date',
        'congregation' => array(
          'required' => true,
        ),
        'comments' => array(
          'required' => false
        )
      ));
      $validate = $f->validate();
      if(!$validate):
        echo static::jsObj(0, $f->badInputs);
        die();
      endif;

      if(!Visit::addVisit(
        $f->congregation,
        $f->{'start-date'},
        $f->{'end-date'},
        $f->comments)
        ):
        echo static::jsObj(3, $f->badInputs);
      else:
        echo static::jsObj(1, null, array(
          'name' => Congregations::getCongregationCol($f->congregation, 'name'),
          'start' => $f->{'start-date'},
          'end' => $f->{'end-date'}
        ));
      endif;
    }

    function deleteVisit(){
      $id = $_POST['visitId'];
      if( !Visit::deleteVisit($id) ):
        echo static::jsObj(0);
      else:
        echo static::jsObj(1);
      endif;
    }

    function deleteCongregation(){
      $id = $_POST['congregationId'];
      if( !Congregations::delete($id) ):
        echo static::jsObj(0);
      else:
        echo static::jsObj(1);
      endif;
    }

    function updateSMTP(){
      $f = new \Formval();
      $f->setType('required', array(
        'required' => true
      ));
      $f->expecting(array(
        'username' => 'required',
        'password' => 'required',
        'from-name' => 'required',
        'port' => array(
          'required' => true,
          'regex' => '/^[0-9]+$/',
        ),
        'host' => 'required'
      ));
      $v = $f->validate();
      if(!$v){
        echo static::jsObj(0, $f->badInputs);
        die();
      }

      // enter into db
      if( !Admin::updateSMTP(
        $f->username,
        $f->password,
        $f->{'from-name'},
        $f->port,
        $f->host
        ) ):
        echo static::jsObj(3, $f->badInputs);
      else:
        echo static::jsObj(1);
      endif;
    }

    function refreshVisitList(){
      // get all visits from database
      $visits = Admin::getVisitList(null);
      if(!$visits):
        echo static::jsObj(3);
        die();
      endif;
      // feed visits to view and capture html
      $htmlList = \Core\View::getRenderedTemplate('Includes/VisitList.twig', array(
        'visits' => $visits
      ));
      if(!$htmlList):
        echo static::jsObj(3);
        die();
      else:
        echo static::jsObj(1, null, array(
          'html' => $htmlList
        ));
        die();
      endif;
    }

}



?>
