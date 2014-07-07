<?php
/**
 * Core module tests
 *
 * @class coreTest
 */
class coreTest extends WP_UnitTestCase {
  
  var $module;

  /**
   * WP Test Framework Constructor
   */
  function setUp() {
	  parent::setUp();
    $this->module = new \UsabilityDynamics\Module\Bootstrap( array(
      'key' => '33c2a8c5102403f5098e3fe410145e96dfeb23c2',
      'system' => 'test',
      'version' => '0.1.0',
      'path' => dirname( __DIR__ ) . '/fixtures/modules',
      'mode' => 'custom',
    ) );
  }
  
  /**
   * WP Test Framework Destructor
   */
  function tearDown() {
	  parent::tearDown();
    $this->module = NULL;
  }
  
  /**
   *
   */
  function testModule(  ) {
    $this->assertTrue( is_object( $this->module ) );
    $this->assertObjectHasAttribute( 'manager', $this->module );
  }
  
  /**
   * Test. Get Available Modules
   *
   */
  public function testGetAvailableModules() {
    $data = $this->module->getModules( 'available' );
    $this->assertNotEmpty( $data );
  }
  
}
