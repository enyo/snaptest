<?php

/**
 * in this test, the first test fails with a call of 2
 * while the second test fails with a call of 0
 * no idea the proper fix
 **/

class Mockable_Simple_Object {
    public function foo($s) {}
}

class MockObject_Call_Count_Test extends Snap_UnitTestCase {
    public function setUp() {    
        $mock = $this->mock('Mockable_Simple_Object')
                    ->listenTo('foo', array(
                        new Snap_Regex_Expectation('/a/')
                        ))
                    ->listenTo('foo', array(
                        new Snap_Regex_Expectation('/b/')
                        ))
                    ->construct();
        $mock->foo('ab');
        $mock->foo('ab');
        $this->mockobj = $mock;
    }
    
    public function tearDown() {
        unset($this->mockobj);
    }
    
    public function testAssertOne() {
        return $this->assertCallCount($this->mockobj, 'foo', 2, array(
            new Snap_Regex_Expectation('/a/')
            ));
    }
    
    public function testAssertTwo() {
        return $this->assertCallCount($this->mockobj, 'foo', 2, array(
            new Snap_Regex_Expectation('/b/')
            ));
    }
}