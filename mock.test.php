<?php

include_once 'mock.php';

class Snap_MockObject_Mockable {
    public function pubReturnTrue() {
        return true;
    }
    public function pubCallReturnTrueThreeTimes() {
        $this->pubReturnTrue();
        $this->pubReturnTrue();
        $this->pubReturnTrue();
    }
    
    public function pubCallReturnTrueTwoTimesFromPro() {
        $this->proCallReturnTrueTwoTimes();
    }

    protected function proReturnTrue() {
        return true;
    }
    protected function proCallReturnTrueTwoTimes() {
        $this->proReturnTrue();
        $this->proReturnTrue();
    }
}

class Snap_MockObject_Test_StubGeneration extends Snap_UnitTestCase {

    public function setUp() {
        $this->mock = new Snap_MockObject('Snap_MockObject_Mockable');
        $this->mock_generated = $this->mock->construct();
        $this->mock_generated->pubCallReturnTrueThreeTimes();
    }
    
    public function tearDown() {
        unset($this->mock);
        unset($this->mock_generated);
    }
    
    public function testInstanceIsMockOjbect() {
        return $this->assertIsA($this->mock, 'Snap_MockObject');
    }
    
    public function testGeneratedMockHasNoParentClass() {
        return $this->assertFalse(get_parent_class($this->mock_generated));
    }
    
    public function testPublicMethodsAreCopied() {
        return $this->assertTrue(method_exists($this->mock_generated, 'pubReturnTrue'));
    }
    
    public function testProtectedMethodsAreNotCopied() {
        return $this->assertFalse(method_exists($this->mock_generated, 'proReturnTrue'));
    }
    
    public function testCalledMethodsCanBeTallied() {
        $count = $this->mock->getTally('pubCallReturnTrueThreeTimes');
        return $this->assertEqual($count, 1);
    }
    
    public function testIndirectCalledMethodsNotCalled() {
        $count = $this->mock->getTally('pubReturnTrue');
        return $this->assertEqual($count, 0);
    }

}


class Snap_MockObject_Test_MockGeneration extends Snap_UnitTestCase {

    public function setUp() {
        $this->mock = new Snap_MockObject('Snap_MockObject_Mockable');
        $this->mock_generated = $this->mock->requiresInheritence()
                                            ->setReturnValue('pubReturnTrue', false)
                                            ->construct();
        $this->mock_generated->pubCallReturnTrueThreeTimes();
    }
    
    public function tearDown() {
        unset($this->mock);
        unset($this->mock_generated);
    }
    
    public function testProectedMethodsAreCopied() {
        return $this->assertTrue(method_exists($this->mock_generated, 'proReturnTrue'));
    }
    
    public function testSetReturnValueChangesReturnValue() {
        return $this->assertFalse($this->mock_generated->pubReturnTrue());
    }
    
    public function testProtectedMethodsCalled() {
        $this->mock_generated->pubCallReturnTrueTwoTimesFromPro();
        $count = $this->mock->getTally('proReturnTrue');
        return $this->assertEqual($count, 2);
    }

}

?>