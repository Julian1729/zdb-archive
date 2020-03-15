<?php

namespace App;

/**
 * PHP Config Settings
 */
class Config
{

  /**
  * Database Credentials
  */

  const DB_HOST = ' dlzandersnet.ipagemysql.com';

  const DB_USER = 'dlzanders01';

  const DB_PASS = 'j1102hJBJ';

  const DB_NAME = 'darrenzanders';

  /**
  * Error Switch (false for production)
  */
  const SHOW_ERRORS = true;

  /**
  * Constant URLs
  */

  const BASE_URI = 'http://http://dlzanders.net';

  const ASSETS_URI = Config::BASE_URI . '/assets';

  const ADMIN_URI = Config::BASE_URI . '/admin';

  const ADMIN_HOME = Config::ADMIN_URI . '/home';

  const ADMIN_PANEL = Config::ADMIN_URI . '/admin-panel';

  const AJAX_URI = Config::BASE_URI .'/ajax';

  const HOME_URI = Config::BASE_URI . '/home';

  const FORGOT_PASSWORD_URI = Config::BASE_URI . '/reset-password/?token=';

  const VISIT_ANON_URI = Config::BASE_URI . '/anon/visit';

  const URI = array(
    'BASE_URI' => Config::BASE_URI,
    'ASSETS_URI' => Config::ASSETS_URI,
    'ADMIN_URI' => Config::ADMIN_URI,
    'ADMIN_HOME' => Config::ADMIN_HOME,
    'ADMIN_PANEL' => Config::ADMIN_PANEL,
    'HOME_URI' => Config::HOME_URI,
    'AJAX_URI' => Config::AJAX_URI,
    'FORGOT_PASSWORD_URI' =>  Config::FORGOT_PASSWORD_URI,
    'VISIT_ANON_URI' => Config::VISIT_ANON_URI
  );

}


?>
