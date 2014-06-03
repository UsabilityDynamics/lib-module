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
    class Upgrader_Skin_Loader {
    
      static private $instance = null;
      
      /** 
       * Prevent object initizalization for current class
       * It must be static.
       */
      private function __construct() {}
      
      /**
       *
       */
      static private function init() {
        try {
          
          self::$instance = new Upgrader_Skin();
        } catch ( Exception $e ) {
          return null;
        }
        return $instance;
      }
 
      static public function get_instance() {
        return self::$instance === null ? self::init() : $instance;
      }

    }

  }

}