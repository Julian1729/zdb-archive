<?php
/**
* Front Controller
*
* Written By: Julian Hernandez
*/

/**
* Composer Autoloader
*/
require_once '../vendor/autoload.php';

/**
* Error and Exception Handling
*/
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

$router = new \Core\Router;

//HOME PAGE
$router->add('', array( 'controller' => 'Landing', 'action' => 'index' ));

// ANONYMOUS VISIT
$router->add('anon/{action}/{token:.+}', array('controller' => 'Anon'));

// LITERALS
$router->add('admin', array('namespace' => 'Admin', 'controller' => 'Landing', 'action' => 'login'));
$router->add('admin/{controller}', array('namespace' => 'Admin'));
$router->add('admin/{controller}/{action}', array('namespace' => 'Admin'));
$router->add('admin/{controller}/{action}/{id:\d+}', array('namespace' => 'Admin'));


// CONGREGATIONS
$router->add('{controller}/{action}');
$router->add('{controller}/{action}/{id:\d+}');
$router->add('{controller}');


$router->dispatch($_SERVER['QUERY_STRING']);

?>
