<?php

/**
 * in this test, private methods are screwed up in the inheritance chain
 **/

class Mockable_Object_With_Private_Members {
    public function foo() {
        return $this->bar();
    }
    
    // true means lazy null won't work
    private function bar() {
        return TRUE;
    }
}

class Mockable_Object_With_Private_Static_Members {
    public static function foo() {
        return self::bar();
    }
    
    // true means lazy null won't work
    private static function bar() {
        return TRUE;
    }
}

class MockObject_Test_Inheriting_Private_Methods extends Snap_UnitTestCase {
    public function setUp() {    
        $mock = $this->mock('Mockable_Object_With_Private_Members')
                     ->requiresInheritance()
                     ->construct();
        
        $this->result = $mock->foo();
    }
    
    public function tearDown() {
        unset($this->result);
    }
    
    public function testPrivateMethodProperlyCalled() {
        return $this->assertTrue($this->result);
    }
}

class MockObject_Test_Inheriting_Private_Methods_On_Static_Objects extends Snap_UnitTestCase {
    public function setUp() {
        $mock = $this->mock('Mockable_Object_With_Private_Static_Members')
                     ->requiresInheritance()
                     ->construct();
        $this->result = SNAP_callStatic($mock, 'foo');
    }
    public function tearDown() {
        unset($this->result);
    }
    
    public function testPrivateMethodProperlyCalled() {
        return $this->assertTrue($this->result);
    }
}