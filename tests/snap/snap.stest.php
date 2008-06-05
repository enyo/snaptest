<?php

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'path.php';

class Snap_Tester_Test extends Snap_UnitTestCase {
    public function setUp() {
        $this->snap = new Snap_Tester('text');
        
        ob_start();
        $this->result = $this->snap->runTests();
        ob_end_clean();
    }
    public function tearDown() {
        unset($this->snap);
    }
    
    public function testInstanceIsASnapTester() {
        return $this->assertIsA($this->snap, 'Snap_Tester');
    }
    
    public function testResultOfRunningTestsIsTrue() {
        return $this->assertTrue($this->result);
    }
    
    public function testMoo() {
        return $this->assertTrue(false);
    }
}

class Snap_Tester_Test_Invalid_Output extends Snap_UnitTestCase {
    public function setUp() {}
    
    public function tearDown() {}
    
    public function testThrowsAnException() {
        $this->willThrow('Exception');
        $this->willError();
        new Snap_Tester('magical_non_existant_thing');
    }
}

class Snap_Tester_Test_Invalid_Input extends Snap_UnitTestCase {
    public function setUp() {
        $this->snap = new Snap_Tester('text');
    }
    
    public function tearDown() {
        unset($this->snap);
    }
    
    public function testThrowsAnException() {
        $this->willThrow('Exception');
        $this->willError();
        $this->snap->addInput('whonkows', 'foo');
    }
}

