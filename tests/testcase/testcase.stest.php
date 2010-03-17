<?php

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'path.php';

class Snap_UnitTestCase_AssertCount_Test extends Snap_UnitTestCase {

    public function setUp() {
        $this->injected_mock = $this->mock('Snap_UnitTestCase_AssertCount_Test')
                                    ->construct();

        $this->injected_mock->onlyCalledOnce();
        
        $this->injected_mock->onlyCalledTwice();
        $this->injected_mock->onlyCalledTwice();
        
        $this->injected_mock->onlyCalledThrice();
        $this->injected_mock->onlyCalledThrice();
        $this->injected_mock->onlyCalledThrice();
    }
    
    public function tearDown() {
        
    }
    
    public function onlyCalledOnce() {}
    public function onlyCalledTwice() {}
    public function onlyCalledThrice() {}
    public function calledOnceWithParam($p = TRUE) {}
    
    public function assertCallCountFailure() {
        return $this->assertCallCount($this->injected_mock, 'onlyCalledOnce', 2);
    }
    
    public function assertCallCountSuccess() {
        return $this->assertCallCount($this->injected_mock, 'onlyCalledOnce', 1);
    }
    
    public function assertMinimumCallCountFailure() {
        return $this->assertMinimumCallCount($this->injected_mock, 'onlyCalledOnce', 2);
    }
    
    public function assertMinimumCallCountSuccess() {
        return $this->assertMinimumCallCount($this->injected_mock, 'onlyCalledTwice', 2);
    }
    
    public function assertMaximumCallCountFailure() {
        return $this->assertMaximumCallCount($this->injected_mock, 'onlyCalledThrice', 1);
    }
    
    public function assertMaximumCallCountSuccess() {
        return $this->assertMaximumCallCont($this->injected_mock, 'onlyCalledTwice', 2);
    }

}

class Snap_UnitTestCase_AssertTrue_Test extends Snap_UnitTestCase {

    public function setUp() {}
    public function tearDown() {}
    
    /**
     * This is the function all other functions are based on
     * it must be tested manually
     */
    public function testFailingAssertTrueThrowsException() {
        try {
            $this->assertTrue(FALSE);
        }
        catch (Snap_AssertIdenticalUnitTestException $e) {
            return new Snap_PassedTestAssertion();
        }
    }
    
    public function testSuccessfulAssertTrueReturnsAssertionObject() {
        try {
            $result = $this->assertTrue(TRUE);
            if ($result instanceof Snap_PassedTestAssertion) {
                return $result;
            }
        }
        catch (Exception $e) {
            throw $e;
        }
        
        return $this->assertTrue(FALSE);
    }
}

class Snap_UnitTestCase_AssertIsA_Test extends Snap_UnitTestCase {

    public function setUp() {}
    public function tearDown() {}
        
    /**
     * test assertIsA based on assertTrue
     */
    public function testAssertIsAReturnsPassedTestAssertion() {
        $tester = new Snap_UnitTestCase_AssertIsA_Test();
        try {
            $result = $this->assertIsA($tester, 'Snap_UnitTestCase_AssertIsA_Test');
            if ($result instanceof Snap_PassedTestAssertion) {
                return $result;
            }
        }
        catch (Exception $e) {
            throw $e;
        }
        
        return $this->assertTrue(FALSE);
    }
    
    public function testFailingAssertIsAThrowsException() {
        $tester = new Snap_UnitTestCase_AssertIsA_Test();
        try {
            $this->assertIsA($tester, 'Exception');
        }
        catch (Snap_AssertInstanceOfUnitTestException $e) {
            return $this->assertTrue(TRUE);
        }
        
        return $this->assertTrue(FALSE);
    }
}

class Snap_UnitTestCase_AssertFalse_Test extends Snap_UnitTestCase {

    public function setUp() {}
    public function tearDown() {}   
    
    /**
     * assert false testing can now use IsA tests
     */
    public function testAssertFalseReturnsPassedTest() {
        $result = $this->assertFalse(FALSE);
        return $this->assertIsA($result, 'Snap_PassedTestAssertion');
    }
    
    public function testFailingAssertFalseThrowsException() {
        try {
            $this->assertFalse(TRUE);
        }
        catch (Snap_AssertIdenticalUnitTestException $e) {
            return $this->assertTrue(TRUE);
        }
        
        return $this->assertTrue(FALSE);
    }
}
    

class Snap_UnitTestCase_AssertEqual_Test extends Snap_UnitTestCase {

    public function setUp() {}
    public function tearDown() {}
    
    public function testAssertEqualReturnsPassedTest() {
        $result = $this->assertEqual(2, 2);
        return $this->assertIsA($result, 'Snap_PassedTestAssertion');
    }
    
    public function testFailingAssertEqualThrowsException() {
        try {
            $this->assertEqual(2, 3);
        }
        catch (Snap_AssertEqualUnitTestException $e) {
            return $this->assertTrue(TRUE);
        }
        
        return $this->assertTrue(FALSE);
    }
}
    
class Snap_UnitTestCase_AssertNotEqual_Test extends Snap_UnitTestCase {

    public function setUp() {}
    public function tearDown() {}
    
    public function testAssertNotEqualReturnsPassedTest() {
        $result = $this->assertNotEqual(2, 3);
        return $this->assertIsA($result, 'Snap_PassedTestAssertion');
    }
    
    public function testFailingAssertNotEqualThrowsException() {
        try {
            $this->assertNotEqual(3, 3);
        }
        catch (Snap_AssertNotEqualUnitTestException $e) {
            return $this->assertTrue(TRUE);
        }
        
        return $this->assertTrue(FALSE);
    }
}
    
class Snap_UnitTestCase_AssertIdentical_Test extends Snap_UnitTestCase {

    public function setUp() {}
    public function tearDown() {}
    
    public function testAssertIdenticalReturnsPassedTest() {
        $result = $this->assertIdentical(TRUE, TRUE);
        return $this->assertIsA($result, 'Snap_PassedTestAssertion');
    }
    
    public function testFailingAssertIdenticalThrowsException() {
        try {
            $this->assertIdentical(1, TRUE);
        }
        catch (Snap_AssertIdenticalUnitTestException $e) {
            return $this->assertTrue(TRUE);
        }
        
        return $this->assertTrue(FALSE);
    }
}

class Snap_UnitTestCase_AssertNotIdentical_Test extends Snap_UnitTestCase {
    public function setUp() {}
    public function tearDown() {}
    
    public function testAssertNotIdenticalReturnsPassedTest() {
        $result = $this->assertNotIdentical(TRUE, 1);
        return $this->assertIsA($result, 'Snap_PassedTestAssertion');
    }
    
    public function testFailingAssertNotIdenticalThrowsException() {
        try {
            $this->assertNotIdentical(TRUE, TRUE);
        }
        catch (Snap_AssertNotIdenticalUnitTestException $e) {
            return $this->assertTrue(TRUE);
        }
        
        return $this->assertTrue(FALSE);
    }
}
    
class Snap_UnitTestCase_AssertNull_Test extends Snap_UnitTestCase {

    public function setUp() {}
    public function tearDown() {}
    
    public function testAssertNullReturnsPassedTest() {
        $result = $this->assertNull(NULL);
        return $this->assertIsA($result, 'Snap_PassedTestAssertion');
    }
    
    public function testFailingAssertNullThrowsException() {
        try {
            $this->assertNull(0);
        }
        catch (Snap_AssertIdenticalUnitTestException $e) {
            return $this->assertTrue(TRUE);
        }
        
        return $this->assertTrue(FALSE);
    }
}
    

class Snap_UnitTestCase_AssertNotNull_Test extends Snap_UnitTestCase {

    public function setUp() {}
    public function tearDown() {}
    
    public function testAssertNotNullReturnsPassedTest() {
        $result = $this->assertNotNull(1);
        return $this->assertIsA($result, 'Snap_PassedTestAssertion');
    }
    
    public function testFailingAssertNotNullThrowsException() {
        try {
            $this->assertNotNull(NULL);
        }
        catch (Snap_AssertNotIdenticalUnitTestException $e) {
            return $this->assertTrue(TRUE);
        }
        
        return $this->assertTrue(FALSE);
    }
}


class Snap_UnitTestCase_AssertRegex_Test extends Snap_UnitTestCase {

    public function setUp() {}
    public function tearDown() {}
    
    public function testAssertRegexReturnsPassedTest() {
        $result = $this->assertRegex('foobarbaz', '/bar/');
        return $this->assertIsA($result, 'Snap_PassedTestAssertion');
    }
    
    public function testFailingAssertNotNullThrowsException() {
        try {
            $this->assertRegex('foobarbaz', '/quuz/');
        }
        catch (Snap_AssertRegexUnitTestException $e) {
            return $this->assertTrue(TRUE);
        }
        
        return $this->assertTrue(FALSE);
    }
}

// this is a test object that demonstrates calling skip during setup
class Snap_UnitTestCase_Calling_Skip_In_Setup_Test_Object extends Snap_UnitTestCase {
    public function setUp() {
        $this->skip('foo');
    }
    public function tearDown() {}
    public function ftestSetupSkipExample() {
        return $this->assertTrue(TRUE);
    }
}
class Snap_UnitTestCase_Calling_Skip_In_Setup extends Snap_UnitTestCase {
    public function setUp() {
        $this->reporter = $this->mock('Snap_UnitTestReporterInterface')->construct();
        $test = new Snap_UnitTestCase_Calling_Skip_In_Setup_Test_Object();
        $test->runTests($this->reporter, 'ftestSetupSkip');
    }
    public function tearDown() {
        unset($this->reporter);
    }
    public function testCallingSkipNotRecordedAsDefect() {
        return $this->assertCallCount($this->reporter, 'recordTestDefect', 0);
    }
    public function testCallingSkipRecordsTestSkip() {
        return $this->assertCallCount($this->reporter, 'recordTestSkip', 1);
    }
}

// handles exceptions being thrown
class Snap_UnitTestCase_WillThrow_Test extends Snap_UnitTestCase {
    public function setUp() {}
    public function tearDown() {}
    public function testWillThrowPasses() {
        $this->willThrow('Exception');
        throw new Exception('foo');
    }
}

// disabled. Fatal errors must always fall through as failures
// issue #4
// handles fatal errors
class Snap_UnitTestCase_WillError_Test extends Snap_UnitTestCase {
    public function setUp() {}
    public function tearDown() {}
    public function testWillErrorPasses() {
        $this->willError();
        ljifsdkjndjkdjkdsjk(); // call to function with no arguments
    }
}

// handles warnings
class Snap_UnitTestCase_WillWarn_Test extends Snap_UnitTestCase {
    public function setUp() {}
    public function tearDown() {}
    public function testWillWarnPasses() {
        $this->willWarn();
        $file = md5(time().file_get_contents(__FILE__));
        include $file;
    }
}