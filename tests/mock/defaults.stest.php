<?php

/**
 * This test addresses a bug with mocking an object that has optional
 * arguments
 **/

class Mockable_Object_With_Defaults {
    public function foo($one, $two = false, $three = false) {
        return FALSE;
    }
}

class Mock_Object_Default_Test extends Snap_UnitTestCase {
    public function setUp() {
        $this->obj = $this->mock('Mockable_Object_With_Defaults')
                          ->setReturnValue('foo', TRUE, array(
                              new Snap_Anything_Expectation(),
                              new Snap_Anything_Expectation(),
                              new Snap_Anything_Expectation(),
                          ))->construct();
    }
    public function tearDown() {
        unset($this->obj);
    }
    
    public function testFalseChangedToTrueNoDefaults() {
        return $this->assertTrue($this->obj->foo('bar', 'baz', 'quux'));
    }
    
    public function testFalseChangedToTrueUsingDefaults() {
        return $this->assertTrue($this->obj->foo('bar')); // called without remaining 2 methods
    }
}