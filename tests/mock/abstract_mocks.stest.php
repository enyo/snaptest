<?php

/**
 * in this test, an abstract class is mocked to test compatibility layers
 **/

abstract class abstract_Mockable_Object {
    public function foo() {
        return TRUE;
    }
    
    // true means lazy null won't work
    abstract public function bar();
}


class MockObject_Test_Mocking_Abstract_Classes extends Snap_UnitTestCase {
    public function setUp() {    
        $this->mock_obj = $this->mock('abstract_Mockable_Object')
                               ->construct();
        
        $this->override_obj = $this->mock('abstract_Mockable_Object')
                                   ->setReturnValue('bar', TRUE)
                                   ->construct();
    }
    
    public function tearDown() {
        unset($this->mock_obj);
        unset($this->override_obj);
    }
    
    public function testPrivateMethodProperlyCalled() {
        return $this->assertIsA($this->mock_obj, 'abstract_Mockable_Object');
    }
    
    public function testFooMethodExists() {
        return $this->assertTrue(method_exists($this->mock_obj, 'foo'));
    }
    
    public function testBarMethodExists() {
        return $this->assertTrue(method_exists($this->mock_obj, 'bar'));
    }
    
    public function testAbstractMethodsCanBeOverridden() {
        return $this->assertTrue($this->override_obj->bar());
    }
}
