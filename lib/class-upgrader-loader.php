<?php
/**
 * Upgrader Skin Loader.
 
 * The User Interface "Skin" for the Module File Upgrader
 */
namespace UsabilityDynamics\Module {

  if( !class_exists( 'UsabilityDynamics\Module\Upgrader_Skin_Loader' ) ) {

    /**
     * Silent Skin
     *
     * @package WordPress
     * @subpackage Upgrader_Skin
     */
    class Upgrader_Loader {
      
      /** 
       * Prevent object initizalization for current class
       * It must be static.
       */
      private function __construct() {}
      
      /**
       *
       */
      static public function call() {
        $instance = null;
        try {
          /** WP_Upgrader */
          if( !class_exists( 'WP_Upgrader' ) ) {
            /** Determine if we can load WP_Upgrader class */
            if( !defined( 'ABSPATH' ) ) {
              throw new \Exception( __( 'WP_Upgrader can not be loaded. ABSPATH constant is undefined. Are you using Wordpress?' ) );
            }
            if( !file_exists( wp_normalize_path( ABSPATH ) . 'wp-admin/includes/class-wp-upgrader.php' ) ) {
              throw new \Exception( __( 'WP_Upgrader can not be loaded. File location is not found.' ) );
            }
            include_once( wp_normalize_path( ABSPATH ) . 'wp-admin/includes/class-wp-upgrader.php' );
          }
          /** WP_Upgrader_Skin */
          if( !class_exists( 'WP_Upgrader_Skin' ) ) {
            /** Determine if we can load WP_Upgrader_Skin class */
            if( !defined( 'ABSPATH' ) ) {
              throw new \Exception( __( 'WP_Upgrader_Skin can not be loaded. ABSPATH constant is undefined. Are you using Wordpress?' ) );
            }
            if( !file_exists( wp_normalize_path( ABSPATH ) . 'wp-admin/includes/class-wp-upgrader-skins.php' ) ) {
              throw new \Exception( __( 'WP_Upgrader_Skin can not be loaded. File location is not found.' ) );
            }
            include_once( wp_normalize_path( ABSPATH ) . 'wp-admin/includes/class-wp-upgrader-skins.php' );
          }
          $instance = new Upgrader( new Upgrader_Skin() );
        } catch ( Exception $e ) {
          /** @todo Add error handler */
          wp_die( $e->getMessage() );
        }
        return $instance;
      }

    }

  }

}