<?php
/**
 *
 * Class BasicUtilityTest
 */
class BasicTest extends PHPUnit_Framework_TestCase {

  /**
   * Test Object Extending with Defaults
   *
   */
  public function testDefaults() {    

    $_root = dirname( dirname( dirname( __DIR__ ) ) );

    if( !file_exists(  $_root . '/vendor/autoload.php' ) ) {
      throw new Exception( 'Unable to bootstrap, autoload.php file not found.' );      
    }

    require_once( $_root . '/vendor/autoload.php' );
  
    if( !class_exists( 'UsabilityDynamics\Module\Bootstrap' ) ) {
      throw  Exception( 'asdf' );
    }

    $myConfiguration = array(
      "someOther" => 10
    );

    $defaultsSettings = array(
      "someDefault" => 7
    );

    $finalConfiguration = new UsabilityDynamics\Module\Bootstrap(array(
      'key' => 'library-audit-key',
      'system' => 'library-audit',
      'version' => '1.0.1',
      'path' => 'fixtures/modules',
      'cache' => false,
      'mode' => 'default'
    ));

    // $this->assertEquals( 7,   $finalConfiguration->someDefault );
    // $this->assertEquals( 10,  $finalConfiguration->someOther );

  }

  /**
   * Test Utility::findUp();
   *
   */
  public function secondTest() {

  }

}


//require_once( $_root . '/lib/class-bootstrap.php' );
//require_once( $_root . '/lib/class-manager.php' );
//require_once( $_root . '/lib/class-ui.php' );
//require_once( $_root . '/lib/class-upgrader-loader.php' );
//require_once( $_root . '/lib/class-upgrader-skin.php' );
//require_once( $_root . '/lib/class-upgrader.php' );
