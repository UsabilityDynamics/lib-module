<?php
/**
 * Class BasicTest
 *
 */
class BasicTest extends WP_UnitTestCase {

  protected $fixture;

  /**
   * Test Object Extending with Defaults
   *
   */
  public function testInitModule() {
    
    /** Initialize Modules logic */
    $this->fixture = new \UsabilityDynamics\Module\Bootstrap( array(
      'key' => '33c2a8c5102403f5098e3fe410145e96dfeb23c2',
      'system' => 'test',
      'version' => '0.1.0',
      'path' => TEST_ROOT_PATH . '/vendor/modules',
      'mode' => 'custom',
    ));
    
    $this->assertTrue( true );

  }
  
}
