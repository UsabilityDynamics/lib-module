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
      
      /**
       * Constructor Arguments
       *
       * @type array
       */
      private $args = null;
      
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
       * @param bool|string $key
       * @param mixed       $default
       *
       * @return array|bool|null
       */
      public function getModules( $key = false, $default = false ) {
        return $this->manager->getModules( $key, $default );
      }

      /**
       * Enables module or list of modules for current system.
       * Bulk process
       *
       * @param mixed $modules
       *
       * @throws \Exception
       * @return bool
       * @author peshkov@UD
       */
      public function enableModules( $modules = array() ) {
        $s = array(); // List of successfully enabled modules.
        if( is_string( $modules ) ) {
          $modules = array( $modules );
        }
        if ( is_array( $modules ) ) {
          foreach( $modules as $module ) {
            $r = $this->manager->enableModule( $module );
            if( !is_wp_error( $r ) ) {
              array_push( $s, $module );
            }
          }
        }
        return $s;
      }

      /**
       * Disables module or list of modules for current system.
       * Bulk process
       *
       * @param mixed $modules
       *
       * @throws \Exception
       * @return bool
       * @author peshkov@UD
       */
      public function disableModules( $modules = array() ) {
        $s = array(); // List of successfully disabled modules.
        if( is_string( $modules ) ) {
          $modules = array( $modules );
        }
        if ( is_array( $modules ) ) {
          foreach( $modules as $module ) {
            $r = $this->manager->disableModule( $module );
            if( !is_wp_error( $r ) ) {
              array_push( $s, $module );
            }
          }
        }
        return $s;
      }
      
      /**
       * Activates (instantiate) all installed enabled modules for current system.
       * Bulk Process
       *
       * @author peshkov@UD
       */
      public function activateModules( $modules = null ) {
        $s = array(); // List of successfully activated modules
        $installed = $this->getModules( 'installed' );
        /** Determine if we should activate specific modules manually */
        if( !empty( $modules ) ) {
          $modules = is_string( $modules ) ? array( $modules ) : $modules;
          $modules = is_array( $modules ) ? $modules : array();
          foreach( $modules as $k => $m ) {
            if( !key_exists( $m, $installed ) ) {
              unset( $modules[ $k ] );
            }
          }
        } else {
          $modules = array_keys( $installed );
        }
        foreach( $modules as $k => $module ) {
          $r = $this->manager->activateModule( $module );
          if( !is_wp_error( $r ) ) {
            array_push( $s, $module );
          }
        }
        return $s;
      }
      
      /**
       * Install/Upgrade Modules
       * Bulk Process
       * 
       * @return array $s. List of successfully installed and upgraded modules: array( 'installed' => array, 'upgraded' => array() )
       * @author peshkov@UD
       */
      public function loadModules( $modules = array() ) {
        $s = array(
          'installed' => array(),
          'upgraded' => array(),
        ); // List of successfully loaded/upgraded modules
        if( is_string( $modules ) ) {
          $modules = array( $modules );
        }
        if ( is_array( $modules ) ) {
          $installed = $this->getModules( 'installed' );
          $available = $this->getModules( 'available' );
          foreach( $modules as $module ) {
            if( !key_exists( $module, $available ) ) {
              continue;
            }
            /** Determine if we have to install or upgrade module */
            if( key_exists( $module, $installed ) ) {
              $r = $this->manager->upgradeModule( $module );
              if( !is_wp_error( $r ) ) {
                array_push( $s[ 'upgraded' ], $module );
              }
            } else {
              $r = $this->manager->installModule( $module );
              if( !is_wp_error( $r ) ) {
                array_push( $s[ 'installed' ], $module );
              }
            }            
          }
        }
        /** If we have any just installed or upgraded modules, - flush modules data. */
        if( !empty( $s[ 'installed' ] ) || !empty( $s[ 'upgraded' ] ) ) {
          $this->manager->flushModulesData();
        }
        return $s;
      }
      
      /**
       * Handles some actions
       * Adds automatic processes for different cases ( modes )
       *
       * @param bool|string $mode Mode ( handler ).
       *
       * @author peshkov@UD
       */
      private function _runMode() {
      
        switch( $this->args[ 'mode' ] ) {
        
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
              $transient = get_transient( 'ud:module:mode:automatic' );
            }
            if( empty( $transient ) ) {
              /** Maybe Install/Upgrade all Modules */
              $this->loadModules( array_keys( $this->getModules( 'available' ) ) );
              /** Maybe Enable All Installed Modules */
              $this->enableModules( array_keys( $this->getModules( 'installed' ) ) );
            }
            /** Set TM to call functionality above once per week. */
            set_transient( 'ud:module:mode:automatic', 'true', ( 60 * 60 * 24 * 7 ) );
            /** Activate all enabled modules */
            $this->activateModules();
            break;
          
          /**
           * Run custom mode.
           * Not sure if the hook below is needed here, but added it just in case. peshkov@UD
           */
          default:
            do_action( "ud:module:mode:{$this->args[ 'mode' ]}:run", $this );
            break;
        
        }
      
      }
      
    }
  
  }

}