<?php
/**
 * Handles Module's management functionality and UI
 * Note: it also is used as API.
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
      
      private $args = 
      
      /**
       * Manager
       *
       * @type object UsabilityDynamics\Module\Manager
       */
      private $manager = null;
      
      /**
       * UI
       *
       * @type object UsabilityDynamics\Module\UI
       */
      private $ui = null;
      
      /**
       * Constructor
       *
       * @param array $args See information about params inside method.
       * @author peshkov@UD
       */
      public function __construct( $args = array() ) {
        $this->args = wp_parse_args( $args, array(
          // Required. API Key. It's related to current domain.
          'key' => false,
          // Required. Plugin's slug. Determines which modules can be installed for current plugin.
          'system' => null,
          // Required. System's version. Determines which modules (and their versions) current version supports
          'version' => null,
          // Required. Path, where plugin's modules must be installed. It may be defined via UD_MODULES_DIR constant
          'path' => null,
          // Optional. Use or not use transient memory. 
          'cache' => true,
          // Optional. Mode Handler can be used to do some processes automatic. 
          // see self::_runMode()
          'mode' => 'default',
        ) );
        
        /** 
         * In some cases, transient memory must not be used ( set '?tmcache=false' to disable cache  ).
         * ( e.g., need to get the latest information about available modules and their versions ).
         */
        $this->args[ 'cache' ] = ( isset( $_REQUEST[ 'tmcache' ] ) && $_REQUEST[ 'tmcache' ] == 'false' ) ? false : $this->args[ 'cache' ];
        
        /** Determine if global Modules DIR ( UD_MODULES_DIR ) is defined. */
        $this->args[ 'path' ] = untrailingslashit( wp_normalize_path( defined( 'UD_MODULES_DIR' ) ? UD_MODULES_DIR : $this->args[ 'path' ] ) );
        /** 
         * To prevent the issues with different systems ( plugins ) 
         * which use modules we're installing all modules to 'system' dir
         * when UD_MODULES_DIR is defined.
         */
        if( defined( 'UD_MODULES_DIR' ) && !empty( $this->args[ 'system' ] ) ) {
          $this->args[ 'path' ] .= '/' . $this->args[ 'system' ];
        }
        
        /** Init our Manager */
        $this->manager = new Manager( $this->args );
        
        /** Runs mode handler. */
        $this->_runMode();
      }
      
      /**
       * Returns the list of modules
       * If key is passed, - returns modules data depending on key
       *
       * @param string $key
       * @param mixed $default
       */
      public function getModules( $key = false, $default = false ) {
        return $this->manager->getModules( $key, $default );
      }
      
      /**
       * Handles some actions
       * Adds automatic processes for different cases ( modes )
       *
       * @param string $mode Mode ( handler ).
       * @author peshkov@UD
       */
      private function _runMode( $mode = false ) {
      
        switch( $mode ) {
        
          /**
           * Default Mode.
           * 
           * Does the following:
           * - Adds Modules UI ( if it doesn't exist ).
           */
          case 'default':
            /** UI can be only Singleton. Because the object renders only one UI for all systems. */
            $this->ui = UI::getInstance();
            /** Add our current system manager to UI */
            $this->ui->set( "system.{$this->args[ 'system' ]}", $this->manager );
            break;
        
          /**
           * Does the following:
           * - Automatically installs available modules.
           * - Automatically upgrades installed modules.
           * - Automatically activates all installed modules.
           * - Disables UI for modules for current system to prevent issues between automatic and manual processes.
           */
          case 'automaticModulesInstallUpgrade':
            //$r = $this->manager->install( 'usabilitydynamics/wp-property-admin-tools' ); die( var_dump( $r ) );
            //echo "<pre>"; print_r( $this ); echo "</pre>"; die();
            break;
        
        }
      
      }
      
    }
  
  }

}