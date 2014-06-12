<?php
/**
 * A File upgrader class for WordPress.
 * Must not be called directly.
 * Use Upgrader_Skin_loader for getting instance.
 */
namespace UsabilityDynamics\Module {

  if( !class_exists( 'UsabilityDynamics\Module\Upgrader' ) && class_exists( 'WP_Upgrader' ) ) {

    /**
     * 
     * @package WordPress
     * @subpackage Upgrader
     */
    class Upgrader extends \WP_Upgrader {
    
      

    }

  }

}