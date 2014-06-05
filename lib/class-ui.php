<?php
/**
 * Module UI
 * Adds User Interface on admin panel.
 * It is initialized once!
 *
 * @see UsabilityDynamics\Module\Bootstrap
 */
namespace UsabilityDynamics\Module {

  if( !class_exists( 'UsabilityDynamics\Module\UI' ) ) {

    /**
     * Class UI
     * Must not be called directly!
     * Called by Bootstrap
     *
     * @package UsabilityDynamics\Installers
     */
    class UI {
    
      /**
       * Class Instance
       *
       * @type object UsabilityDynamics\Module\UI
       */
      private $instance;
    
      /**
       * Manager
       *
       * @type object UsabilityDynamics\Module\Manager
       */
      private $settings = null;
    
      /**
       * Instantaite class.
       * Note: must not be called directly, because
       * we're using the only one object for UI
       *
       * Constructor does the following:
       * - applies specific hooks ( adds menu page )
       * - renders UI based on added System Module Managers
       * - handles UI requests
       *
       * @author peshkov@UD
       */
      private function __construct( $manager ) {
        
      }
      
      /**
       * Determine if instance already exists and Return UI Instance
       *
       * @author peshkov@UD
       */
      static public function get_instance() {
        if( null === self::$instance ) {
          self::$instance = new self();
        }
        return self::$instance->core;
      }
      
      /**
       * Set settings data.
       * System Module Manager is being added via the current method.
       *
       * @author peshkov@UD
       */
      public function set( $key, $value ) {
        return null;
      }
      
      /**
       * Get settings data.
       *
       * @author peshkov@UD
       */
      public function get( $key = false, $default = false ) {
        return null;
      }

    }
  
  }

}