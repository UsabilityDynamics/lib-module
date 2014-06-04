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
       * @type string
       */
      private $key = false;
      
      /**
       * Plugin/Module/Library slug. Determines which modules can be installed for current system.
       * @type string
       */     
      private $system = null;
      
      /**
       * Version of current system
       * @type string
       */
      private $version = null;
      
      /**
       * Path, where modules must be installed. It may be defined via UD_MODULES_DIR constant!
       * @type string
       */
      private $path = null;
      
      /**
       * The list of all available modules based on current system and api key
       * @type array
       */
      private $modules = array(
        'installed' => array(),
        'available' => array(),
      );
      
      /**
       * Use or not use transient memory
       * @type boolean
       */
      private $cache = false;
      
      /**
       * API properties
       * @type string
       */
      private $apiUrl = 'http://api.ud-dev.com/';
      private $apiVersion = 'v2';
      private $apiController = 'modules';
    
      /**
       * Constructor
       *
       */
      public function __construct( $args = array() ) {
        /** Init our variables */
        $this->key = isset( $args[ 'key' ] ) ? $args[ 'key' ] : $this->key;
        $this->system = isset( $args[ 'system' ] ) ? $args[ 'system' ] : $this->system;
        $this->version = isset( $args[ 'version' ] ) ? $args[ 'version' ] : $this->version;
        $this->path = isset( $args[ 'path' ] ) ? $args[ 'path' ] : $this->path;
        $this->cache = isset( $args[ 'cache' ] ) ? $args[ 'cache' ] : $this->cache;
        
        /** Set the list of available and installed modules */
        $this->modules[ 'installed' ] = $this->loadModules();
        $this->modules[ 'available' ] = $this->moduleLoadout();    
        //echo "<pre>"; print_r( $this ); echo "</pre>";die();
        
        /** TEMP */
        $this->install( 'class_admin_tools' );
      }
      
      /**
       * Returns modules data depending on key
       *
       */
      public function getModules( $key = false, $default = false ) {
        return $this->_getData( $this->modules, $key, $default );
      }
            
      /**
       * Ability to define directories to walk for look for modules 
       * and generate list of all found modules and their settings 
       * (extracted from PHP header or composer.json). 
       * http://screencast.com/t/r6rC9WNcl
       *
       */
      public function loadModules() {
        $modules = array();
        
        return $modules;
      }
      
      /**
       * Makes API call to UD to get list of modules that current system can support.
       *
       */
      private function moduleLoadout() {
        $response = array();
        /** Maybe get cache */
        if( $this->cache ) {
          $response = get_transient( 'ud:module:loadout' );
        }
        /** If there is no cache, do request to server. In other case, just return cache. */
        if( !empty( $response ) ) {
          $response = json_decode( $response, true );
        } else {
          $modules = $this->getModules( 'installed' );
          /** Do request to UD */
          $response = $this->_doRequest( 'loadout', array(
            'installed' => array(),
          ) );
          /** Determine if request was successful */
          if( !isset( $response[ 'ok' ] ) || $response[ 'ok' ] != true ) {
            $response = array();
          } else {
            $response = !empty( $response[ 'modules' ] ) ? $response[ 'modules' ] : array();
          }
          if( !empty( $response ) ) {
            /** Cache our response for day. */
            set_transient( 'ud:module:loadout', json_encode( $response ), ( 60 * 60 * 24 ) );
          }
        }
        return $response;
      }
      
      /**
       * If module does not already exist, do not install it. (optional)
       *
       */
      public function upgrade() {
        
      }
      
      /**
       * Download any missing / outdated modules from repository
       *
       */
      public function upgradeModules() {
        
      }
      
      /**
       * Validate a specific module - make sure it can be enabled, etc
       *
       */
      public function validateModule() {
        
      }
      
      /**
       * Activate (instantiate) loaded modules
       *
       */
      public function enableModules() {
        
      }
      
      /**
       * Install Module from Repository
       *
       * @param string $module Slug of module which must be installed
       */
      public function install( $module ) {
        
        //echo "<pre>"; print_r( $module ); echo "</pre>"; die();
        
        return null;

        $args = Utility::parse_args( $args, array(
          'name'   => '',
          'version' => '',
          'path' => WP_PLUGIN_DIR
        ));

        $args->url = array(
          'http://',
          'repository.usabilitydynamics.com/',
          $args->name
        );

        if( $args->version ) {
          array_push( $args->url, '.', $args->version );
        }

        array_push( $args->url, '.zip' );

        $args->url = implode( $args->url );

        // Concatenate full path.
        $args->path = trailingslashit( $args->path ) . $args->name;

        // Initialize silent skin.
        $_upgrader = new \WP_Upgrader( new Upgrader_Skin() );

        if( is_wp_error( $_upgrader->fs_connect( array( WP_CONTENT_DIR, $args->path )))) {
          $_upgrader->skin->error( new WP_Error( 'Unable to connect to file system.' ) );
        };

        $_source = $_upgrader->unpack_package( $_upgrader->download_package( $args->url ) );

        $_result = $_upgrader->install_package(array(
          'source' => $_source,
          'destination' => $args->path,
          'abort_if_destination_exists' => false,
          'clear_destination' => true,
          'hook_extra' => $args
        ));

        // e.g. folder_exists
        if( is_wp_error( $_result ) ) {
          $_upgrader->skin->error( new WP_Error( 'Installation failed.' ) );
        }

        return $_result;
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
        $p = strtok( $path, '.' );
        while( $p !== false ) {
          if( !isset( $current[ $p ] ) ) {
            return $default;
          }
          $current = $current[ $p ];
          $p = strtok( '.' );
        }
        return $current;
      }
      
      /**
       * Does request to UD server
       *
       * @param string $name Name of request
       * @param array $args
       */
      private function _doRequest( $name, $args = array(), $method = 'POST' ) {
        $response = false;
        /** Prepare URL for request based on method */
        $url = untrailingslashit( $this->apiUrl ) . '/' . $this->apiController . '/' . $this->apiVersion . '/' . $name;
        $url .= '?' . http_build_query( array(
          'key' => $this->key,
          'system' => $this->system,
          'version' => $this->version,
        ) );
        if( $method == 'GET' ) {
          $url .= '&' . http_build_query( $args );
        }
        /** Do request */
        $r = @wp_remote_request( $url, array_filter( array(
          'method' => ( in_array( $method, array( 'GET', 'POST' ) ) ? $method : 'POST' ),
          'body' => ( $method == 'POST' ? $args : false ),
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