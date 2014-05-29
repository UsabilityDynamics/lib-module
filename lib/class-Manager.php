<?php
/**
 * Module Manager
 *
 * The current manager:
 * Installs, activates, uploads and upgrades modules
 */
namespace UsabilityDynamics\Module {

  /**
   * Class Loader
   *
   * @package UsabilityDynamics\Installers
   */
  class Manager {

    /**
     * Constructor
     *
     */
    public function __construct() {
    
    }
    
    /**
     * Make API call to UD to get list of modules that my system can support.
     *
     */
    public function moduleLoadout() {
      
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
     * @param null $args
     *
     * @todo the current function must be totally refactored. peshkov@UD
     * @internal param $id
     * @return bool|void
     */
    public function install() {
      include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

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
     * Similar to legacy WPP_F::get_api_key() method, 
     * either returns an Access Token determined automatically based on domain, 
     * or validates provided key (if set). 
     * Key should be stored in DB as it will be needed to get downloadable module URLs.
     *
     */
    public function validateKey() {
      
    }
    
    /**
     * Ability to define directories to walk for look for modules 
     * and generate list of all found modules and their settings 
     * (extracted from PHP header or composer.json). 
     * http://screencast.com/t/r6rC9WNcl
     *
     */
    public function loadModules() {
      
    }

  }

}