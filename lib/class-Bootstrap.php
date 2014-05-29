<?php
/**
 * Handles Module's management functionality and UI
 * 
 */
namespace UsabilityDynamics\Installers {

  /**
   * Class Bootstrap
   *
   * @package UsabilityDynamics\Installers
   */
  class Bootstrap {

    /**
     * Constructor
     *
     */
    public function __construct() {
      
      
      
    }
    
    /**
     * Activates available modules
     * Moved from WPP_F::load_premium()
     *
     * @todo: do refactor of code ( get rid of $wp_properties, etc ). peshkov@UD
     */
    public function load() {
    
      $args = (object) wp_parse_args( $args, array(
        'location' => array(
          defined( 'WPP_Premium' ) ? WPP_Premium : null
        ),
        'main_map' => array(),
        'headers' => array(
          '_id'           => 'Feature ID',
          '_type'         => 'Feature Type',
          'name'          => 'Name',
          'version'       => 'Version',
          'description'   => 'Description',
          'class'         => 'Class',
          'slug'          => 'Slug',
          'screen.id'     => 'Screen ID',
          'minimum.core'  => 'Minimum Core Version',
          'minimum.php'   => 'Minimum PHP Version',
          'capability'    => 'Capability'
        )
      ));

      $args->location = array_filter( $args->location );

      if( defined( 'WPP_Premium' ) && is_dir( WPP_Premium ) && $premium_dir = opendir( WPP_Premium ) ) {

        $_verified = array();

        while( false !== ( $file = readdir( $premium_dir ) ) ) {

          if( $file == 'index.php' || $file == '.' || $file == '..' ) {
            continue;
          }

          // Load files in nested module directories. @since 1.42.0
          if( is_dir( trailingslashit( WPP_Premium ) . $file ) ) {

            $_locations = isset( $args->main_map[ $file ] ) ? (array) $args->main_map[ $file ] : array();

            foreach( $_locations as $_relative_path ) {

              $file = str_replace( $file, $_relative_path, $file );

              if( is_file( WPP_Premium . DIRECTORY_SEPARATOR . $file ) ) {
                continue;
              };

            }

          }

          if( isset( $file ) && is_file( WPP_Premium . DIRECTORY_SEPARATOR . $file ) && end( @explode( ".", $file ) ) == 'php' ) {

            $_upgraed         = false;
            $_absolute_path   = trailingslashit( WPP_Premium ) . $file;
            $_basename        = basename( $file );
            $plugin_slug      = str_replace( array( '.php' ), '', $_basename );

            $plugin_data = wp_parse_args( get_file_data( $_absolute_path, $args->headers, 'plugin' ), array(
              '_id' => null,
              '_type' => 'module',
              '_key' => null,
              'name' => $plugin_slug,
              'description' => null,
              'version' => null,
              'capability' => null,

              'minimum.core' => null,
              'minimum.wp' => null,
              'minimum.php' => null,

              // System
              'slug' => $plugin_slug,
              'class' => $plugin_slug,

              // Locked. (non-dynamic, renaming will break model)
              'disabled' => null,
              '$system' => array(
                'uid' => $plugin_slug,
                'path' => $_absolute_path,
                'requires_upgrade' => null
              )

            ));

            // ID is numeric.
            $plugin_data[ '_id' ] = isset( $plugin_data[ '_id' ] ) ? intval( $plugin_data[ '_id' ] ) : $plugin_data[ '_id' ];

            // Key is Lowercased.
            $plugin_data[ '_key' ] = isset( $plugin_data[ '_key' ] ) ? $plugin_data[ '_key' ] : str_replace( array( 'wpp-', 'wpp_', 'class_', 'class-', '_' ), array( '', '', '', '', '-' ), $plugin_slug );

            // Force lowercase.
            $plugin_slug =  strtolower( str_replace( '-', '_', isset( $plugin_data[ 'slug' ] ) ? $plugin_data[ 'slug' ] : $plugin_slug ) );

            if( isset( $wp_properties[ 'installed_features' ][ $plugin_slug ] ) &&  is_array( $wp_properties[ 'installed_features' ][ $plugin_slug ] ) && $wp_properties[ 'installed_features' ][ $plugin_slug ][ 'version' ] ) {

              if( version_compare( $plugin_data[ 'version' ], $wp_properties[ 'installed_features' ][ $plugin_slug ][ 'version' ] ) > 0 ) {
                $_upgraed = true;
              }

            }

            $wp_properties[ 'installed_features' ][ $plugin_slug ] =( $plugin_data );

            $_verified[] = $plugin_slug;

            if( isset( $plugin_data[ 'minimum.core' ] ) && $plugin_data[ 'minimum.core' ] ) {
              $wp_properties[ 'installed_features' ][ $plugin_slug ][ 'minimum.core' ] = $plugin_data[ 'minimum.core' ];
            }

            // If feature has a Minimum Core Version and it is more than current version - we do not load
            $feature_requires_upgrade = ( !empty( $wp_properties[ 'installed_features' ][ $plugin_slug ][ 'minimum.core' ] ) && ( version_compare( WPP_Version, $wp_properties[ 'installed_features' ][ $plugin_slug ][ 'minimum.core' ] ) < 0 ) ? true : false );

            if( $feature_requires_upgrade ) {
              // Disable feature if it requires a higher WPP version
              $wp_properties[ 'installed_features' ][ $plugin_slug ][ 'disabled' ]  = 'true';
              $wp_properties[ 'installed_features' ][ $plugin_slug ][ '$system' ][ 'requires_upgrade' ] = 'true';
            } elseif( !isset( $wp_properties[ 'installed_features' ][ $plugin_slug ][ 'disabled' ] ) || $wp_properties[ 'installed_features' ][ $plugin_slug ][ 'disabled' ] != 'true' ) {

              // Continue with loading feature...
              $wp_properties[ 'installed_features' ][ $plugin_slug ][ '$system' ][ 'requires_upgrade' ] = 'false';

              // Module requires a higher version of PHP than is available.
              if( !$plugin_data[ 'minimum.php' ] || version_compare( PHP_VERSION, $plugin_data[ 'minimum.php' ] ) > 0 ) {

                if( WP_DEBUG == true ) {
                  include_once( $_absolute_path );
                } else {
                  @include_once( $_absolute_path );
                }

                // Initialize Module that declare a class.
                if( $plugin_data[ 'class' ] && class_exists( $_class = $plugin_data[ 'class' ] ) ) {
                  $_instance = new $_class( $wp_properties, $plugin_data );

                  // Call Upgrade Method, if exists.
                  if( $_upgraed && is_callable( array( $_instance, 'upgrade' ) ) ) {
                    $_instance->upgrade( $wp_properties );
                  }

                }

              }

              // Disable plugin if class does not exists - file is empty
              if( !$_class && !class_exists( $plugin_slug ) ) {
                unset( $wp_properties[ 'installed_features' ][ $plugin_slug ] );
              }

              $wp_properties[ 'installed_features' ][ $plugin_slug ][ 'disabled' ] = 'false';

            } else {

              //** We unset requires core upgrade in case feature was update while being disabled */
              $wp_properties[ 'installed_features' ][ $plugin_slug ][ '$system' ][ 'requires_upgrade' ] = 'false';

            }

          }

        }

        // Remove features that are not found on disk
        foreach( (array) $wp_properties[ 'installed_features' ] as $_slug => $data ) {

          if( !in_array( $_slug, $_verified ) ) {
            unset( $wp_properties[ 'installed_features' ][ $_slug ] );
          }

        }

      }

      return array(
        'installed' =>  isset( $wp_properties[ 'installed_features' ] ) ? $wp_properties[ 'installed_features' ] : array(),
        'available' =>  isset( $wp_properties[ 'available_features' ] ) ? $wp_properties[ 'available_features' ] : array()
      );
      
    }
    
  }

}