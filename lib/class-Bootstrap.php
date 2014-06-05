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
       * Enables Modules UI for current system
       */
      public function enableUI() {
        if( !$this->ui && is_admin() ) {
          /** UI can be only Singleton. Because the object renders only one UI for all systems. */
          $this->ui = UI::getInstance();
          /** Add our current system manager to UI */
          $this->ui->set( "system.{$this->args[ 'system' ]}", $this->manager );
        }
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
       * Enables module or list of modules for current system.
       * Bulk process
       *
       * @param mixed $modules
       * @author peshkov@UD
       */
      public function enableModules( $modules ) {
        try {
          if( is_string( $modules ) ) {
            $modules = array( $modules );
          }
          if ( is_array( $modules ) ) {
            $_modules = $this->getModules( 'installed' );
            foreach( $modules as $module ) {
              if( !key_exists( $module, $_modules ) ) {
                throw new \Exception( sprintf( __( 'Module \'%s\' is not installed and can not be enabled.' ), $module ) );
              }
              if( !$this->manager->enableModule( $module ) ) {
                throw new \Exception( sprintf( __( 'Module \'%s\' could not be enabled' ), $module ) );
              }
            }
          } else {
            throw new \Exception( __( 'Something went wrong. Could not enable module(s).' ) );
          }
        } catch ( Exception $e ) {
          /** @todo: add error handler instead of wp_die!!! */
          wp_die( $e->getMessage() );
          return false;
        }
        return true;
      }
      
      /**
       * Disables module or list of modules for current system.
       * Bulk process
       *
       * @param mixed $modules
       * @author peshkov@UD
       */
      public function disableModules( $modules ) {
        try {
          if( is_string( $modules ) ) {
            $modules = array( $modules );
          }
          if ( is_array( $modules ) ) {
            $_modules = $this->getModules( 'installed' );
            foreach( $modules as $module ) {
              if( !key_exists( $module, $_modules ) ) {
                throw new \Exception( sprintf( __( 'Module \'%s\' is not installed and can not be disabled as well.' ), $module ) );
              }
              if( !$this->manager->disableModule( $module ) ) {
                throw new \Exception( sprintf( __( 'Module \'%s\' could not be disabled' ), $module ) );
              }
            }
          } else {
            throw new \Exception( __( 'Something went wrong. Could not disable module(s).' ) );
          }
        } catch ( Exception $e ) {
          /** @todo: add error handler instead of wp_die!!! */
          wp_die( $e->getMessage() );
          return false;
        }
        return true;
      }
      
      /**
       * Activates (instantiate) all installed enabled modules for current system.
       * Bulk Process
       *
       * @author peshkov@UD
       */
      public function activateModules() {
        return $this->manager->activateModules();
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
            /** Activate all enabled modules */
            $this->activateModules();
            /** Enable UI on Back End */
            $this->enableUI();
            break;
        
          /**
           * Does the following:
           * - Automatically installs available modules.
           * - Automatically upgrades installed modules.
           * - Automatically activates all installed modules.
           * - Disables UI for modules for current system to prevent issues between automatic and manual processes.
           *
           * Note: some mode's functionality runs once per week. Use GET tmcache=false to run it manually.
           */
          case 'automaticModulesInstallUpgrade':
            /** Use TM ( transient memory ) to prevent call of functionality below on every server request! */
            if( $this->args[ 'cache' ] ) {
              $transient = get_transient( 'ud:module:mode:automaticModulesInstallUpgrade:run' );
            }
            if( empty( $transient ) ) {
              /** Enable All Installed Modules */
              $this->enableModules( array_keys( $this->getModules( 'installed' ) ) );
            }
            /** Set TM to call functionality above once per week. */
            set_transient( 'ud:module:mode:automaticModulesInstallUpgrade:run', 'true', ( 60 * 60 * 24 * 7 ) );
            /** Activate all enabled modules */
            $this->activateModules();
            break;
          
          /**
           * Run custom mode.
           * Not sure if the hook below is needed here, but added it just in case. peshkov@UD
           */
          case default:
            do_action( "ud:module:mode:{$mode}:run", $this );
            break;
        
        }
      
      }
      
    }
  
  }

}