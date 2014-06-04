<?php
/**
 * Handles Module's management functionality and UI
 * 
 */
namespace UsabilityDynamics\Module {

  if( !class_exists( 'UsabilityDynamics\Module\Bootstrap' ) ) {

    /**
     * Class Bootstrap
     *
     * @package UsabilityDynamics\Installers
     * @author peshkov@UD
     */
    class Bootstrap {
      
      /**
       * Manager
       *
       * @type object UsabilityDynamics\Module\Manager
       */
      private $manager = null;
    
      /**
       * Constructor
       *
       * @param array $args See information about params inside method.
       * @author peshkov@UD
       */
      public function __construct( $args = array() ) {
        
        $args = wp_parse_args( $args, array(
          // API Key. It's related to current domain.
          'key' => false,
          // Plugin's slug. Determines which modules can be installed for current plugin.
          'system' => null,
          // System's version. Determines which modules (and their versions) current version supports
          'version' => null,
          // Path, where plugin's modules must be installed. It may be defined via UD_MODULES_DIR constant
          'path' => null,
          // Use or not use transient memory. 
          // In some cases, transient memory must not be used ( set '?tmcache=false' to disable cache  ).
          // ( e.g., need to get the latest information about available modules and their versions ).
          'cache' => true,
        ) );
        
        /** Determine if global Modules DIR ( UD_MODULES_DIR ) is defined. */
        $args[ 'path' ] = untrailingslashit( wp_normalize_path( defined( 'UD_MODULES_DIR' ) ? UD_MODULES_DIR : $args[ 'path' ] ) );
        
        /** Init our Manager */
        $this->manager = new Manager( $args );
        
        //echo "<pre>"; print_r( $this ); echo "</pre>"; die();
        
      }
      
      /**
       * Returns the list of installed modules
       * 
       */
      public function getModules() {
        return $this->manager->getModules();
      }
      
      /**
       * Activates available modules
       *
       */
      public function activateModules( $args = array() ) {
        
        
      }
      
    }
  
  }

}