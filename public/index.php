<?php
// Define path to application directory
//define these global path constants here
define( 'ROOT_PATH' , dirname( dirname( __FILE__ ) ) ) ;
define( 'LIB_PATH' , ROOT_PATH . '/library' ) ;
define( 'APPLICATION_PATH' , ROOT_PATH . '/application' ) ;
define( 'MODULE_PATH' , ROOT_PATH . '/application/modules' ) ;

// define the path for config.ini
define( 'CONFIG_PATH' , ROOT_PATH . '/application/config' ) ;
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));


/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);


$front = Zend_Controller_Front::getInstance();
$front->throwExceptions(true);
try {


	$application->bootstrap()
	->run();	

		} catch (Exception $e) {
		
	$msgError =  $e->getMessage();
	include "custom.error.php";
}	
	


