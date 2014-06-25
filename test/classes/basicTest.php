<?php
/**
 * Class BasicTest
 */
class BasicTest extends WP_UnitTestCase {

  protected static $fixture;

  /**
   * Test Object Extending with Defaults
   *
   */
  public function testInitModule() {
    $this->assertObjectHasAttribute( 'manager', $this->getFixture() );
  }
  
  /**
   * Test. Get Available Modules
   *
   */
  public function testGetAvailableModules() {
    $data = $this->getFixture()->getModules( 'available' );
    $this->assertNotEmpty( $data );
  }
  
  /**
   * Test. Install Modules
   *
   */
  public function testloadModules() {
    $data = $this->getFixture()->getModules( 'available' );
    reset( $data );
    $first_key = key( $data );
    //$data = $this->getFixture()->loadModules( $first_key );
    //$this->assertArrayHasKey( 'installed', $data );
    //$this->assertNotEmpty( $data[ 'installed' ] );
  }
  
  /**
   *
   */
  private function getFixture() {
    if( self::$fixture === NULL ) {
      /** Initialize Modules logic */
      self::$fixture = new \UsabilityDynamics\Module\Bootstrap( array(
        'key' => '33c2a8c5102403f5098e3fe410145e96dfeb23c2',
        'system' => 'test',
        'version' => '0.1.0',
        'path' => TEST_ROOT_PATH . '/vendor/modules',
        'mode' => 'custom',
      ) );
    }
    return self::$fixture;
  }
  
}
