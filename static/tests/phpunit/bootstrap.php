<?php
/**
 * PHP Unit Test Bootstrap
 */

// Set correct path to Composer Autoload file
$path = dirname( dirname( dirname( __DIR__ ) ) ) . '/vendor/autoload.php';


if( !file_exists( $path ) || !require_once( $path ) ) {
  exit( "Could not load composer autoload file. Path: {$path}\n" );
}
// Determine if our Bootstrap class exists.
if( !class_exists( 'UsabilityDynamics\Test\Bootstrap' ) ) {
  exit( "Bootstrap class for init WP PHPUnit Tests is not found.\n" );
}
// Loader
UsabilityDynamics\Test\Bootstrap::get_instance();