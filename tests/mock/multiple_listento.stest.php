<?php

/**
 * in this test, the first test fails with a call of 2
 * while the second test fails with a call of 0
 * no idea the proper fix
 **/

class failing_FooBar {
    public function foo($s) {}
}

class failing extends Snap_UnitTestCase {
    public function setUp() {    
        $mock = $this->mock('failing_FooBar')
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