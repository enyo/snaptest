<?php

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'path.php';

class Snap_MockObject_Mockable {
    public function pubReturnTrue() {
        return TRUE;
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
        return TRUE;
    }
    protected function proCallReturnTrueTwoTimes() {
        $this->proReturnTrue();
        $this->proReturnTrue();
    }
}

interface Snap_MockObject_MockableInterface {
    public function pubReturnTrue();
    public function pubCallReturnTrueThreeTimes();
    public function pubCallReturnTrueTwoTimesFromPro();
}

class Snap_MockObject_Mockable_Using_Interface implements Snap_MockObject_MockableInterface {
    public function pubReturnTrue() {}
    public function pubCallReturnTrueThreeTimes() {}
    public function pubCallReturnTrueTwoTimesFromPro() {}
}

interface Snap_MockObject_MockableInterfaceWithReference {
    public function pubReturnRef(&$ref);
}

class Snap_MockObject_Mockable_With_Protected_Members {
    protected $foo;
    public function __construct($foo) {
        $this->foo = $foo;
    }
    public function getFoo() {
        return $this->foo;
    }
}

class Snap_MockObject_Mockable_With_Static_Members {
    protected static function getTrue() {
        return TRUE;
    }
    public static function getProtectedTrue() {
        return self::getTrue();
    }
}

class Snap_MockObject_Mockable_With_Static_Members_Extended extends Snap_MockObject_Mockable_With_Static_Members {
    public static function getProtectedParentTrue() {
        return parent::getProtectedTrue();
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
    
    public function testGeneratedMockHasParentClass() {
        return $this->assertEqual(get_parent_class($this->mock_generated), 'Snap_MockObject_Mockable');
    }
    
    public function testPublicMethodsAreCopied() {
        return $this->assertTrue(method_exists($this->mock_generated, 'pubReturnTrue'));
    }
    
    public function testProtectedMethodsAreCopied() {
        return $this->assertTrue(method_exists($this->mock_generated, 'proReturnTrue'));
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

class Snap_MockObject_Test_StubGeneration_With_Interface extends Snap_UnitTestCase {

    public function setUp() {
        $this->mock = new Snap_MockObject('Snap_MockObject_MockableInterface');
        $this->mock_generated = $this->mock->construct();
    }
    
    public function tearDown() {
        unset($this->mock);
        unset($this->mock_generated);
    }
    
    public function testBaseIsMockOjbect() {
        return $this->assertIsA($this->mock, 'Snap_MockObject');
    }
    
    public function testInstanceImplementsInterface() {
        return $this->assertIsA($this->mock_generated, 'Snap_MockObject_MockableInterface');
    }
}

class Snap_MockObject_Test_MockGeneration_With_Interface_With_Inheritance extends Snap_UnitTestCase {

    public function setUp() {
        $this->mock = new Snap_MockObject('Snap_MockObject_Mockable_Using_Interface');
        $this->mock_generated = $this->mock->requiresInheritance()->construct();
    }
    
    public function tearDown() {
        unset($this->mock);
        unset($this->mock_generated);
    }
    
    public function testBaseIsMockOjbect() {
        return $this->assertIsA($this->mock, 'Snap_MockObject');
    }
    
    public function testInstanceImplementsInterface() {
        return $this->assertIsA($this->mock_generated, 'Snap_MockObject_MockableInterface');
    }
    
    public function testMaintainsOriginalClassAsSuperClass() {
        return $this->assertIsA($this->mock_generated, 'Snap_MockObject_Mockable_Using_Interface');
    }
}


class Snap_MockObject_Test_MockGeneration extends Snap_UnitTestCase {

    public function setUp() {
        $this->mock = new Snap_MockObject('Snap_MockObject_Mockable');
        $this->mock_generated = $this->mock->requiresInheritance()
                                            ->setReturnValue('pubReturnTrue', FALSE)
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


class Snap_MockObject_Test_MockGenerationWithReferenceInterface extends Snap_UnitTestCase {
    public function setUp() {}
    public function tearDown() {}
    
    public function testObjectMocksCorrectly() {
        $obj = $this->mock('Snap_MockObject_MockableInterfaceWithReference')->construct();
        return $this->assertIsA($obj, 'Snap_MockObject_MockableInterfaceWithReference');
    }
}

class Snap_MockObject_Test_MockGenerationWithInheritanceProtectedMembers extends Snap_UnitTestCase {
    const foo_constructor = 'bar';
    public function setUp() {}
    public function tearDown() {}
    
    public function testObjectMemberAccessible() {
        $foo = $this->mock('Snap_MockObject_Mockable_With_Protected_Members')
                    ->requiresInheritance()
                    ->construct(self::foo_constructor);

        return $this->assertEqual($foo->getFoo(), self::foo_constructor);
    }
}

class Snap_MockObject_Test_MockGenerationWithStaticMethods extends Snap_UnitTestCase {
    public function setUp() {
        $this->mocked_obj = $this->mock('Snap_MockObject_Mockable_With_Static_Members')
                                 ->requiresInheritance()
                                 ->construct();
    }
    public function tearDown() {
        unset($this->mocked_obj);
    }
    
    public function testStaticClassIsMocked() {
        return $this->assertIsA($this->mocked_obj, 'Snap_MockObject_Mockable_With_Static_Members');
    }
    
    public function testProtectedMethodCallsCopiedCorrectly() {
        return $this->assertTrue(SNAP_callStatic($this->mocked_obj, 'getProtectedTrue'));
    }
}

class Snap_MockObject_Test_MockGenerationWithStaticMethodsAndOverriding extends Snap_UnitTestCase {
    public function setUp() {
        $this->mocked_obj = $this->mock('Snap_MockObject_Mockable_With_Static_Members')
                                 ->requiresInheritance()
                                 ->setReturnValue('getTrue', FALSE)
                                 ->construct();
    }
    public function tearDown() {
        unset($this->mocked_obj);
    }
    
    public function testStaticClassIsMocked() {
        return $this->assertIsA($this->mocked_obj, 'Snap_MockObject_Mockable_With_Static_Members');
    }
    
    public function testProtectedMethodCallsCopiedCorrectly() {
        return $this->assertFalse(SNAP_callStatic($this->mocked_obj, 'getProtectedTrue'));
    }
}

class Snap_MockObject_Test_MockGenerationWithStaticMethodsAndExtension extends Snap_UnitTestCase {
    public function setUp() {
        $this->mocked_obj = $this->mock('Snap_MockObject_Mockable_With_Static_Members_Extended')
                                 ->setReturnValue('getTrue', FALSE)
                                 ->requiresInheritance()
                                 ->construct();
    }
    public function tearDown() {
        unset($this->mocked_obj);
    }
    
    public function testStaticClassIsMocked() {
        return $this->assertIsA($this->mocked_obj, 'Snap_MockObject_Mockable_With_Static_Members_Extended');
    }
    
    public function testStaticClassExtendsCorrectly() {
        return $this->assertIsA($this->mocked_obj, 'Snap_MockObject_Mockable_With_Static_Members');
    }
    
    // Despite overriding getTrue, this is a call to parent::, so it should not be overriden
    public function testProtectedMethodCallsCopiedCorrectly() {
        return $this->assertTrue(SNAP_callStatic($this->mocked_obj, 'getProtectedParentTrue'));
    }
}

