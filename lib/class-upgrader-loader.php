<?php
/**
 * Upgrader Loader.
 *
 */
namespace UsabilityDynamics\Module {

  if( !class_exists( 'UsabilityDynamics\Module\Upgrader_Loader' ) ) {

    /**
     * Upgrader Loader
     *
     * @package WordPress
     * @subpackage Upgrader
     */
    class Upgrader_Loader {
      
      /** 
       * Prevent object initizalization for current class
       * It must be static.
       */
      private function __construct() {}
      
      /**
       * Init and return Upgrader object.
       * Determine if all neccessary classes included.
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
          /** File functions list ( e.g. request_filesystem_credentials() ) */
          if( !file_exists( wp_normalize_path( ABSPATH ) . 'wp-admin/includes/file.php' ) ) {
            throw new \Exception( __( 'Upgrader can not be loaded. Required File\'s location is not found.' ) );
          }
          include_once( wp_normalize_path( ABSPATH ) . 'wp-admin/includes/file.php' );
          $instance = new Upgrader( new Upgrader_Skin() );
        } catch ( Exception $e ) {
          /** @todo Add error handler instead of wp_die!!! */
          wp_die( $e->getMessage() );
        }
        return $instance;
      }

    }

  }

}