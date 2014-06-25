<?php
/**
 * PHP Unit Test Bootstrap
 */
echo 'Loading CIRCLECI environment...';
// Set ROOT of current module
define( 'TEST_ROOT_PATH', dirname( __DIR__ ) );
// Set correct path to Composer Autoload file
$path = TEST_ROOT_PATH . '/vendor/autoload.php';
if( !file_exists( $path ) || !require_once( $path ) ) {
  exit( "Could not load composer autoload file. Path: {$path}\n" );
}
// Determine if our Bootstrap class exists.
if( !class_exists( 'UsabilityDynamics\Test\Bootstrap' ) ) {
  exit( "Bootstrap class for init WP PHPUnit Tests is not found.\n" );
}
// Loader
UsabilityDynamics\Test\Bootstrap::get_instance( array(
  'config' => dirname( __FILE__ ) . '/config/wp-config.php',
) );