<?php
/**
 * Module Manager.
 * Must not be initialized directly.
 *
 * The current manager:
 * Installs, activates, uploads and upgrades modules
 *
 * @see UsabilityDynamics\Module\Bootstrap
 */
namespace UsabilityDynamics\Module {

  if( !class_exists( 'UsabilityDynamics\Module\Manager' ) ) {

    /**
     * Class Manager
     * Must not be called directly!
     *
     * @package UsabilityDynamics\Installers
     */
    class Manager {

      /**
       * Api Key.
       *
       * @type string
       */
      private $key = false;

      /**
       * Plugin/Module/Library slug. Determines which modules can be installed for current system.
       *
       * @type string
       */
      private $system = null;

      /**
       * Version of current system
       *
       * @type string
       */
      private $version = null;

      /**
       * Path, where modules must be installed. It may be defined via UD_MODULES_DIR constant!
       *
       * @type string
       */
      private $path = null;

      /**
       * Use or not use transient memory
       *
       * @type boolean
       */
      private $cache = false;
      
      /**
       * All transient keys
       *
       * @type array
       */
      private $transients = array(
        'ud:module:get_installed_modules',
        'ud:module:get_available_modules',
      );

      /**
       * API properties
       *
       * @type string
       */
      private $apiUrl = 'http://api.ud-dev.com/';
      private $apiVersion = 'v2';
      private $apiController = 'modules';

      /**
       * The list of all available modules based on current system and api key
       *
       * @type array
       */
      private $modules = array(
        'installed' => array(),
        'available' => array(),
        'enabled'   => array(),
        'activated' => array(),
      );

      /**
       * Constructor
       *
       */
      public function __construct( $args = array() ) {
        /** Init our variables */
        $this->key     = isset( $args[ 'key' ] ) ? $args[ 'key' ] : $this->key;
        $this->system  = isset( $args[ 'system' ] ) ? $args[ 'system' ] : $this->system;
        $this->version = isset( $args[ 'version' ] ) ? $args[ 'version' ] : $this->version;
        $this->path    = isset( $args[ 'path' ] ) ? $args[ 'path' ] : $this->path;
        $this->cache   = isset( $args[ 'cache' ] ) ? $args[ 'cache' ] : $this->cache;

        /** Set the list of available and installed modules */
        $this->modules[ 'installed' ] = $this->_getInstalledModules();
        $this->modules[ 'available' ] = $this->_getAvailableModules();
        /** Set the list of enabled modules */
        $this->modules[ 'enabled' ] = $this->_getEnabledModules();
        
        /** Maybe Add specific transients to transients list. Needed for resetTransient() functionality. */
        foreach( $this->modules[ 'installed' ] as $k => $m ) {
          $this->addTransient( "ud:module:validate:" . sanitize_key( $k ) );
        }
        foreach( $this->modules[ 'available' ] as $k => $m ) {
          $this->addTransient( "ud:module:validate:" . sanitize_key( $k ) );
        }
        //echo "<pre>"; print_r( $this ); echo "</pre>"; die();
      }

      /**
       * Returns modules data depending on key
       *
       * @param bool|string $key
       * @param mixed       $default
       */
      public function getModules( $key = false, $default = false ) {
        return $this->_getData( $this->modules, $key, $default );
      }
      
      /**
       * Flush Modules data
       */
      public function flushModulesData( $cache = false ) {
        /** Reset our cache */
        if( !$cache ) {
          $this->resetTransient();
        }
        /** Update our object's data */
        $this->modules[ 'installed' ] = $this->_getInstalledModules();
        $this->modules[ 'available' ] = $this->_getAvailableModules();
      }
      
      /**
       * Adds Transient to list
       *
       * @param string $transient
       * @author peshkov@UD
       */
      public function addTransient( $transient ) {
        if( !in_array( $transient, $this->transients ) ) {
          $this->transients[] = $transient;
        }
      }
      
      /**
       * Resets Transient Cache
       *
       * @param bool | string $transient
       * @author peshkov@UD
       */
      public function resetTransient( $transient = false ) {
        if( !empty( $transient ) && isset( $this->transients[ $transient ] ) ) {
          delete_transient( $this->transients[ $transient ] );
        } else {
          foreach( $this->transients as $t ) {
            delete_transient( $t );
          }
        }
      }

      /**
       * Validates a specific module - make sure it can be enabled.
       *
       */
      public function validateModule( $module ) {
        $is_valid = false;
        /** Maybe get cache */
        if( $this->cache ) {
          $cache = get_transient( "ud:module:validate:" . sanitize_key( $module ) );
        }
        /** If there is no cache, Do request to UD server. */
        if( !empty( $cache ) ) {
          $is_valid = $cache;
        } else {
          /** Do request to UD */
          $response = $this->_doRequest( 'validate', array(
            'module' => $module,
          ) );
          /** Determine if request was successful */
          if( isset( $response[ 'ok' ] ) || $response[ 'ok' ] == true ) {
            $is_valid = isset( $response[ 'is_valid' ] ) ? $response[ 'is_valid' ] : false;
          }
          /** Cache our result per day. */
          set_transient( "ud:module:validate:" . sanitize_key( $module ), $is_valid, ( 60 * 60 * 24 ) );
        }
        return $is_valid;
      }

      /**
       * Activate (instantiate) loaded module
       *
       * Does the following:
       * - validate module
       * - disable module on validation failed
       * - activates module on validation success
       *
       * @param string $_module
       * @author peshkov@UD
       */
      public function activateModule( $_module ) {
        try {
          /** Determine if module is installed */
          $module = $this->getModules( "installed.{$_module}" );
          if( !$module ) {
            throw new \Exception( sprintf( __( 'Module \'%s\' can not be activated, because it is not installed.' ), $_module ) );
          }
          /** Determine if module is enabled */
          $enabled = $this->_getEnabledModules();
          if( !in_array( $_module, $enabled ) ) {
            throw new \Exception( sprintf( __( 'Module \'%s\' can not be activated, because it is not enabled.' ), $module[ 'data' ][ 'title' ] ) );
          }
          /** Validate module */
          if( !$this->validateModule( $_module ) ) {
            $this->disableModule( $_module );
            throw new \Exception( sprintf( __( 'Module \'%s\' can not be activated. Check your license.' ), $module[ 'data' ][ 'title' ] ) );
          }
          /** Maybe check required minimum PHP version */
          if( !empty( $module[ 'data' ][ 'minimum_php' ] ) ) {
            $requiredPhpVersion = floatval( preg_replace( "/[^-0-9\.]/", "", $module[ 'data' ][ 'minimum_php' ] ) );
            $operator = preg_replace( "/[^><=]/", "", $module[ 'data' ][ 'minimum_php' ] );
            if( empty( $operator ) ) {
              $operator = '>=';
            }
            if( $requiredPhpVersion > 0 ) {
              if( !version_compare( phpversion(), $requiredPhpVersion, $operator ) ) {
                throw new \Exception( sprintf( __( 'Module \'%s\' can not be activated. Your PHP version is less than required one.' ), $module[ 'data' ][ 'title' ] ) );
              }
            }
          }
          /** Now try to activate and init module */
          if( empty( $module[ 'data' ][ 'classmap' ] ) ) {
            throw new \Exception( sprintf( __( 'Module \'%s\' can not be activated. Missed classmap parameter.' ), $module[ 'data' ][ 'title' ] ) );
          }
          $classFile = $module[ 'path' ] . '/' . ltrim( $module[ 'data' ][ 'classmap' ], '/' );
          if( !file_exists( $classFile ) ) {
            throw new \Exception( sprintf( __( 'Module \'%s\' can not be activated. Bootstrap file does not exist.' ), $module[ 'data' ][ 'title' ] ) );
          }
          /** Determine if class exists and include it if it does not. */
          if( !class_exists( $module[ 'data' ][ 'bootstrap' ] ) ) {
            include_once( $classFile );
            if( !class_exists( $module[ 'data' ][ 'bootstrap' ] ) ) {
              throw new \Exception( sprintf( __( 'Module \'%s\' can not be activated. Bootstrap class does not exist.' ), $module[ 'data' ][ 'title' ] ) );
            }
          }
          /** Activate module and add it to the list of activated modules. */
          new $module[ 'data' ][ 'bootstrap' ];
          array_push( $this->modules[ 'activated' ], $_module );
        } catch ( \Exception $e ) {
          /** @todo: add error handler!!! */
          return new \WP_Error( 'lib-module-failure', $e->getMessage() );
        }
        return true;
      }

      /**
       * Enable module
       *
       */
      public function enableModule( $module ) {
        try {
          if( !$this->getModules( "installed.{$module}" ) ) {
            throw new \Exception( sprintf( __( 'Module \'%s\' can not be enabled because it is not installed.' ), $module ) );
          }
          $data = $this->_getEnabledModules();
          if( !in_array( $module, $data ) ) {
            array_push( $data, $module );
            $r = update_option( 'ud:module:' . sanitize_key( $this->system ) . ':enabled', $data );
            if( !$r ) {
              throw new \Exception( sprintf( __( 'Module \'%1s\' could not be enabled because of error on trying to update \'%2s\' option.' ), $module, 'ud:module:' . sanitize_key( $this->system ) . ':enabled' ) );
            }
            // Update our enabled modules.
            $this->modules[ 'enabled' ] = $data;
          }
        } catch ( \Exception $e ) {
          /** @todo: add error handler!!! */
          return new \WP_Error( 'lib-module-failure', $e->getMessage() );
        }
        return true;
      }

      /**
       * Disable module
       *
       */
      public function disableModule( $module ) {
        try {
          $data = $this->_getEnabledModules();
          $pos = array_search( $module, $data );
          if( $pos !== false && isset( $data[ $pos ] ) ) {
            unset( $data[ $pos ] );
            $r = update_option( 'ud:module:' . sanitize_key( $this->system ) . ':enabled', $data );
            if( !$r ) {
              throw new \Exception( sprintf( __( 'Module \'%1s\' could not be disabled because of error on trying to update \'%2s\' option.' ), $module, 'ud:module:' . sanitize_key( $this->system ) . ':enabled' ) );
            }
            // Update our enabled modules.
            $this->modules[ 'enabled' ] = $data;
          }
        } catch ( \Exception $e ) {
          /** @todo: add error handler!!! */
          return new \WP_Error( 'lib-module-failure', $e->getMessage() );
        }
        return true;
      }

      /**
       * Upgrades Module
       *
       */
      public function upgradeModule( $module ) {
        try {
          /** Be sure that module is not installed. */
          if( !$_module = $this->getModules( "installed.{$module}", false ) ) {
            throw new \Exception( sprintf( __( 'Module \'%s\' can not be upgraded because it is not installed.' ), $module ) );
          }
          /** Be sure that available module's version is higher then existing one */
          if( version_compare( $_module[ 'data' ][ 'version' ], $this->getModules( "available.{$module}.data.version" ) ) >= 0 ) {
            throw new \Exception( sprintf( __( 'The current Module \'%s\' version is the latest.' ), $module ) );
          }
          $this->_loadModule( $module, array(
            'abort_if_destination_exists' => false,
            'clear_destination'           => true,
          ) );
        } catch ( \Exception $e ) {
          /** @todo: add error handler!!! */
          return new \WP_Error( 'lib-module-failure', $e->getMessage() );
        }
        return true;
      }
      
      /**
       * Installs Module
       *
       */
      public function installModule( $module ) {
        try {
          /** Be sure that module is not installed. */
          if( $this->getModules( "installed.{$module}" ) ) {
            throw new \Exception( sprintf( __( 'Module \'%s\' is already installed.' ), $module ) );
          }
          $this->_loadModule( $module, array(
            'abort_if_destination_exists' => true,
            'clear_destination'           => false,
          ) );
        } catch ( \Exception $e ) {
          /** @todo: add error handler!!! */
          return new \WP_Error( 'lib-module-failure', $e->getMessage() );
        }
        return true;
      }
      
      /**
       * Loads Module from Repository
       *
       * @param string $module Slug of module which must be installed
       *
       * @return bool
       * @author peshkov@UD
       */
      private function _loadModule( $module, $args = array() ) {
        $args = wp_parse_args( $args, array( 
          'abort_if_destination_exists' => false,
          'clear_destination'           => true,
        ) );
        $_module = $this->getModules( "available.{$module}" );
        /** Be sure we have information about module */
        if( empty( $_module[ 'data' ] ) ) {
          throw new \Exception( sprintf( __( 'Module \'%s\' is not available. Check if module can be installed on current domain.' ), $module ) );
        }
        $data = $_module[ 'data' ];
        /** Init some required vars */
        $sourceUrl = !empty( $_module[ 'path' ] ) ? $_module[ 'path' ] : false;
        $moduleDir = !empty( $data[ 'installer_name' ] ) ? $data[ 'installer_name' ] : sanitize_key( $module );
        $destDir   = $this->path . '/' . $moduleDir;
        if( !is_dir( $this->path ) ) {
          /** Looks like required destination directory doesn't exist. Let's try to create it. */
          if( !wp_mkdir_p( $this->path ) ) {
            throw new \Exception( sprintf( __( 'Destination directory ( %s ) for module does not exist and can not be created. Please check file permissions.' ), $this->path ) );
          }
        }
        /** Be sure we have source URL for getting module */
        if( !$sourceUrl ) {
          throw new \Exception( sprintf( __( 'Something went wrong. Module \'%s\' source is not available.' ), $module ) );
        }
        /**
         * Initialize Upgrader
         *
         * @see http://xref.wordpress.org/branches/3.6/WordPress/Upgrader/WP_Upgrader.html
         */
        $upgrader = Upgrader_Loader::call();
        /** Be sure upgrader is inited */
        if( !$upgrader || is_wp_error( $upgrader ) ) {
          throw new \Exception( sprintf( __( 'Something went wrong. Install could not be run. Module \'%s\' is not installed.' ), $module ) );
        }
        $upgrader->init();
        /** Be sure we can connect to file system to upload module. */
        if( is_wp_error( $upgrader->fs_connect( array( $destDir ) ) ) ) {
          throw new \Exception( sprintf( __( 'Install could not be run. Unable to connect to file system. Module \'%s\' is not installed' ), $module ) );
        };
        /** Load and unpack module to temp directory. */
        $package = $upgrader->download_package( $_module[ 'path' ] );
        if( is_wp_error( $package ) ) {
          throw new \Exception( sprintf( __( 'Install failed. Unable to download module \'%s\'.' ), $module ) );
        }
        $source = $upgrader->unpack_package( $package );
        if( is_wp_error( $source ) ) {
          throw new \Exception( sprintf( __( 'Install failed. Unable to unpack module \'%s\'.' ), $module ) );
        }
        /** Try to install module */
        $result = $upgrader->install_package( array(
          'source'                      => $source,
          'destination'                 => $destDir,
          'abort_if_destination_exists' => $args[ 'abort_if_destination_exists' ],
          'clear_destination'           => $args[ 'clear_destination' ],
          'hook_extra'                  => array(
            'module'  => $module,
            'manager' => $this,
          ),
        ) );
        if( is_wp_error( $result ) ) {
          throw new \Exception( sprintf( __( 'Install failed. Unable to install module \'%s\' to file system.' ), $module ) );
        }
        //echo "<pre>"; print_r( $result ); echo "</pre>";die();
        return true;
      }

      /**
       * Returns the list of installed modules.
       * Walks through defined directories and looks for modules
       * and generate list of all found modules and their settings
       * extracted from PHP header.
       *
       * @author peshkov@UD
       */
      private function _getInstalledModules() {
        $modules = array();
        /** Maybe get cache */
        if( $this->cache ) {
          $modules = get_transient( 'ud:module:get_installed_modules' );
          $modules = !empty( $modules ) ? json_decode( $modules, true ) : array();
        }
        /** If there is no cache, parse modules directory. In other case, just return cache. */
        if( empty( $modules ) ) {
          if( !empty( $this->path ) && is_dir( $this->path ) ) {
            if( $dh = opendir( $this->path ) ) {
              while( ( $dir = readdir( $dh ) ) !== false ) {
                if( !in_array( $dir, array( '.', '..' ) ) &&
                  is_dir( $this->path . '/' . $dir ) &&
                  file_exists( $this->path . '/' . $dir . '/composer.json' )
                ) {
                  $composer = @file_get_contents( $this->path . '/' . $dir . '/composer.json' );
                  $composer = @json_decode( $composer, true );
                  if( is_array( $composer ) && !empty( $composer[ 'name' ] ) ) {
                    $modules[ $composer[ 'name' ] ] = wp_parse_args( $this->_prepareModuleData( $composer ), array(
                      'path' => $this->path . '/' . $dir,
                    ) );
                  }
                }
              }
              closedir( $dh );
            }
          }
          if( !empty( $modules ) ) {
            $this->resetTransient();
            /** Cache our result for day. */
            set_transient( 'ud:module:get_installed_modules', json_encode( $modules ), ( 60 * 60 * 24 ) );
          }
        }
        return $modules;
      }

      /**
       * Returns the list of available Modules
       * Makes API call to UD to get list of modules that current system can support.
       *
       */
      private function _getAvailableModules() {
        $response = array();
        /** Maybe get cache */
        if( $this->cache ) {
          $response = get_transient( 'ud:module:get_available_modules' );
          $response = !empty( $response ) ? json_decode( $response, true ) : array();
        }
        /** If there is no cache, do request to server. In other case, just return cache. */
        if( empty( $response ) ) {
          $installed = array();
          foreach( $this->getModules( 'installed', array() ) as $k => $v ) {
            $installed[ $k ] = $v[ 'data' ][ 'version' ];
          }
          /** Do request to UD */
          $response = $this->_doRequest( 'loadout', array(
            'installed' => $installed,
          ) );
          /** Determine if request was successful */
          if( !isset( $response[ 'ok' ] ) || $response[ 'ok' ] != true ) {
            $response = array();
          } else {
            $response = !empty( $response[ 'modules' ] ) ? $response[ 'modules' ] : array();
            /** Prepare response to required modules array */
            $validArr = array();
            foreach( $response as $k => $v ) {
              if( is_array( $v ) && !empty( $v[ 'name' ] ) ) {
                $validArr[ $v[ 'name' ] ] = wp_parse_args( $this->_prepareModuleData( $v ), array(
                  'path' => isset( $v[ 'dist' ][ 'url' ] ) ? $v[ 'dist' ][ 'url' ] : false,
                ) );
              }
            }
            $response = $validArr;
          }
          if( !empty( $response ) ) {
            $this->resetTransient();
            /** Cache our response per day. */
            set_transient( 'ud:module:get_available_modules', json_encode( $response ), ( 60 * 60 * 24 ) );
          }
        }
        return $response;
      }
      
      /**
       * Returns the list of enabled modules
       *
       */
      private function _getEnabledModules() {
        $optName = 'ud:module:' . sanitize_key( $this->system ) . ':enabled';
        $data = get_option( $optName, array() );
        $data = is_array( $data ) ? $data : array();
        return $data;
      }

      /**
       * Returns data.
       *
       */
      private function _getData( $data, $key = false, $default = false ) {
        /** Return all data. */
        if( !$key ) {
          return $data;
        }
        /** Resolve dot-notated key. */
        if( strpos( $key, '.' ) ) {
          return $this->_resolveData( $data, $key, $default );
        }

        /** Return value or default. */

        return isset( $data[ $key ] ) ? $data[ $key ] : $default;
      }

      /**
       * Resolve dot-notated key.
       *
       * @source http://stackoverflow.com/questions/14704984/best-way-for-dot-notation-access-to-multidimensional-array-in-php
       *
       * @param       $a
       * @param       $path
       * @param null  $default
       *
       * @internal param array $a
       * @return array|null
       */
      private function _resolveData( $a, $path, $default = null ) {
        $current = $a;
        $p       = strtok( $path, '.' );
        while( $p !== false ) {
          if( !isset( $current[ $p ] ) ) {
            return $default;
          }
          $current = $current[ $p ];
          $p       = strtok( '.' );
        }

        return $current;
      }

      /**
       * Prepares Module data
       * so installed and available modules have the same structure (schema)
       *
       * @param array $data Schema ( composer.json and loadout response must have almost same structure )
       *
       * @return array
       */
      private function _prepareModuleData( $data ) {
        /** Try to get Title locale */
        $title = $this->_getLocaleValue( 'title', $data );
        /** Try to get Tagline locale */
        $tagline = $this->_getLocaleValue( 'tagline', $data );
        /** Try to get Description locale */
        $description = $this->_getLocaleValue( 'description', $data );
        $module = array(
          'data'   => array(
            // Unique Name
            'name'           => $data[ 'name' ],
            // Current version
            'version'        => isset( $data[ 'version' ] ) ? $data[ 'version' ] : false,
            // Title based on localization
            'title'          => !empty( $title ) ? $title : $data[ 'name' ],
            // Tagline based on localization
            'tagline'        => $tagline,
            // Description based on localization
            'description'    => $description,
            // Logo Image
            'image'          => isset( $data[ 'extra' ][ 'image' ] ) ? $data[ 'extra' ][ 'image' ] : false,
            // Minimum PHP version
            'minimum_php'    => isset( $data[ 'require' ][ 'php' ] ) ? $data[ 'require' ][ 'php' ] : false,
            // Minimum current system's core version
            'minimum_core'   => isset( $data[ 'extra' ][ 'minimum_core' ][ $this->system ] ) ? $data[ 'extra' ][ 'minimum_core' ][ $this->system ] : false,
            // Name of directory where module must be installed
            'installer_name' => isset( $data[ 'extra' ][ 'installer-name' ] ) ? $data[ 'extra' ][ 'installer-name' ] : false,
            // Main file which contains bootstrap class
            'classmap'       => isset( $data[ 'extra' ][ 'classmap' ] ) ? $data[ 'extra' ][ 'classmap' ] : false,
            // Bootstrap Class which must be initialized on module activating.
            'bootstrap'      => isset( $data[ 'extra' ][ 'bootstrap' ] ) ? $data[ 'extra' ][ 'bootstrap' ] : false,
          ),
          'system' => $data,
        );

        return $module;
      }
      
      /**
       * Try to get locale value from data
       *
       * @param $key Key to find
       * @param array $data
       * @return string
       */
      private function _getLocaleValue( $key, $data = array() ) {
        $r = '';
        if( !is_array( $data ) || !isset( $data[ 'extra' ] ) ) {
          return $r;
        }
        $r = isset( $data[ 'extra' ][ $key ][ get_locale() ] ) ? $data[ 'extra' ][ $key ][ get_locale() ] : '';
        if( empty( $r ) ) {
          $r = isset( $data[ 'extra' ][ $key ][ 'en_US' ] ) ?
            $data[ 'extra' ][ $key ][ 'en_US' ] : ( isset( $data[ 'extra' ][ $key ] ) && is_string( $data[ 'extra' ][ $key ] ) ?
              $data[ 'extra' ][ $key ] : '' );
        }
        return $r;
      }

      /**
       * Does request to UD server
       *
       * @param string $name Name of request
       * @param array  $args
       *
       * @param string $method
       *
       * @return array|bool|mixed
       * @author peshkov@UD
       */
      private function _doRequest( $name, $args = array(), $method = 'POST' ) {
        $response = false;
        /** Prepare URL for request based on method */
        $url = untrailingslashit( $this->apiUrl ) . '/' . $this->apiController . '/' . $this->apiVersion . '/' . $name;
        $url .= '?' . http_build_query( array(
            'key'     => $this->key,
            'system'  => $this->system,
            'version' => $this->version,
          ) );
        if( $method == 'GET' ) {
          $url .= '&' . http_build_query( $args );
        }
        /** Do request */
        $r = @wp_remote_request( $url, array_filter( array(
          'method'  => ( in_array( $method, array( 'GET', 'POST' ) ) ? $method : 'POST' ),
          'body'    => ( $method == 'POST' ? $args : false ),
          // Prevent too long waiting on fron end
          'timeout' => ( is_admin() ? 15 : 5 ),
        ) ) );
        /** Check if request was successful and get our response */
        if( !empty( $r[ 'response' ][ 'code' ] ) && $r[ 'response' ][ 'code' ] == 200 ) {
          $response = !empty( $r[ 'body' ] ) ? @json_decode( $r[ 'body' ], true ) : false;
        }
        //echo "<pre>"; print_r( $response ); echo "</pre>"; die();
        return $response;
      }

    }

  }

}