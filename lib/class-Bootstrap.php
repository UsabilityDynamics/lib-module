<?php
/**
 * Handles Module's management functionality and UI
 * 
 */
namespace UsabilityDynamics\Module {

  /**
   * Class Bootstrap
   *
   * @package UsabilityDynamics\Installers
   */
  class Bootstrap {

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
    private $modules = null;
  
    /**
     * Constructor
     *
     */
    public function __construct( $args = array() ) {
      
      /** Init our variables */
      $this->key = isset( $args[ 'key' ] ) ? $args[ 'key' ] : $this->key;
      $this->system = isset( $args[ 'system' ] ) ? $args[ 'system' ] : $this->system;
      $this->version = isset( $args[ 'version' ] ) ? $args[ 'version' ] : $this->version;
      $this->path = untrailingslashit( wp_normalize_path( defined( 'UD_MODULES_DIR' ) ? UD_MODULES_DIR : ( isset( $args[ 'path' ] ) ? $args[ 'path' ] : $this->path ) ) );
      
      //echo "<pre>"; print_r( $this ); echo "</pre>"; die();
      
    }
    
    /**
     * Returns the list of installed modules
     * 
     */
    public function getList() {
      return $this->modules;
    }
    
    /**
     * Activates available modules
     *
     */
    public function activateModules( $args = array() ) {
      
      
    }
    
  }

}