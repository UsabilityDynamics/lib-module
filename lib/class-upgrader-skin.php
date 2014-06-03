<?php
/**
 * Upgrader Skin
 * Must not be called directly.
 * Use Upgrader_Skin_loader for getting instance.
 * The User Interface "Skin" for the Module File Upgrader
 */
namespace UsabilityDynamics\Module {

  if( !class_exists( 'UsabilityDynamics\Module\Upgrader_Skin' ) ) {

    /**
     * Silent SKin
     *
     * @package WordPress
     * @subpackage Upgrader_Skin
     */
    class Upgrader_Skin extends \WP_Upgrader_Skin {

      var $options = array();

      function __construct( $args = array() ) {
        parent::__construct( array(
          'title' => __( 'Update Module' ),
        ));
      }

      function request_filesystem_credentials( $error = null ) {
        include_once( ABSPATH . 'wp-admin/includes/file.php' );
        return parent::request_filesystem_credentials( $error );
      }

      function header() {}

      function footer() {}

      function error( $error ) {}

      function feedback( $string ) {}

      function before() {}

      function after() {}

    }

  }

}