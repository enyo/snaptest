<?php

include_once SNAPTEST_ROOT.'exceptions.php';

/**
 * Tests Snap_UnitTestException
 */
class Snap_UnitTestException_Test extends Snap_UnitTestCase {

    const code      = 'foo';
    const message   = 'message';

    public function setUp() {
        try {
            throw new Snap_UnitTestException(self::code, self::message);
        }
        catch (Exception $e) {
            $this->exception = $e;
        }
    }
    
    public function tearDown() {
        unset($this->exception);
    }
    
    public function testIsAnException() {
        return $this->assertIsA($this->exception, 'exception');
    }
    
    public function testConstructorMessageIsSaved() {
        return $this->assertEqual(self::code, $this->exception->getMessage());
    }
    
    public function testUserMessageIsSaved() {
        return $this->assertEqual(self::message, $this->exception->getUserMessage());
    }

}

/**
 * Test Snap_NotImplementedException
 * has no tests, is a direct extention
 */
// class Snap_NotImplementedException_Test extends Snap_UnitTestCase {}


/**
 * Tests Snap_AssertCallCountUnitTestException
 */
class Snap_AssertCallCountUnitTestException_Test extends Snap_UnitTestCase {

    const code          = 'foo';
    const message       = 'quick fox dog';
    const class_name    = 'lolercoaster';
    const expected      = 2;
    const actual        = 3;
    
    public function setup() {
        try {
            throw new Snap_AssertCallCountUnitTestException(self::code, self::message, self::class_name, self::expected, self::actual);
        }
        catch (Exception $e) {
            $this->exception = $e;
        }
    }
    
    public function tearDown() {
        unset($this->exception);
    }
    
    public function testIsAnException() {
        return $this->assertIsA($this->exception, 'exception');
    }
    
    public function testConstructorMessageIsSaved() {
        return $this->assertEqual(self::code, $this->exception->getMessage());
    }
    
    public function testClassNameIsInUserMessage() {
        return $this->assertRegex($this->exception->getUserMessage(), '/'.self::class_name.'/');
    }
    
    public function testExpectedCountIsInUserMessage() {
        return $this->assertRegex($this->exception->getUserMessage(), '/'.self::expected.'/');
    }
    
    public function testActualCountIsInUserMessage() {
        return $this->assertRegex($this->exception->getUserMessage(), '/'.self::actual.'/');
    }

}


/**
 * Tests Snap_AssertInstanceOfUnitTestException
 */
class Snap_AssertInstanceOfUnitTestException_Test extends Snap_UnitTestCase {

    const code = 'foo';
    const message = 'quick lazy dog';
    const class_name = 'fooclass';
    
    public function setUp() {
        try {
            throw new Snap_AssertInstanceOfUnitTestException(self::code, self::message, new Exception('msgbar'), self::class_name);
        } catch(Exception $e) {
            $this->exception = $e;
        }
    }
    
    public function tearDown() {
        unset($this->exception);
    }
    
    public function testIsAnException() {
        return $this->assertIsA($this->exception, 'exception');
    }
    
    public function testExpectedClassNameIsInMessage() {
        return $this->assertRegex($this->exception->getUserMessage(), '/'.self::class_name.'/i');
    }
    
    public function testActualClassNameIsInMessage() {
        return $this->assertRegex($this->exception->getUserMessage(), '/'.'exception'.'/i');
    }

}


/**
 * Tests Snap_AssertRegexUnitTestException
 */
class Snap_AssertRegexUnitTestException_Test extends Snap_UnitTestCase {

    const code      = 'foo';
    const message   = 'lolmsg';
    const value     = 'value';
    const regex     = '/pattern/';
    
    public function setUp() {
        try {
            throw new Snap_AssertRegexUnitTestException(self::code, self::message, self::value, self::regex);
        } catch(Exception $e) {
            $this->exception = $e;
        }
    }
    
    public function tearDown() {
        unset($this->exception);
    }
    
    public function testIsAnException() {
        return $this->assertIsA($this->exception, 'exception');
    }
    
    public function testValueIsInMessage() {
        return $this->assertRegex($this->exception->getUserMessage(), '/'.self::value.'/i');
    }
    
    public function testRegexIsInMessage() {
        return $this->assertRegex($this->exception->getUserMessage(), '#'.self::regex.'#i');
    }

}


/**
 * Tests Snap_AssertCompareUnitTestException
 */
class Snap_AssertCompareUnitTestException_Test extends Snap_UnitTestCase {

    const code      = 'foo';
    const message   = 'lollermessage';
    const value     = '123';
    const expected  = '456';
    const operator  = '==';
    
    public function setUp() {
        try {
            throw new Snap_AssertCompareUnitTestException(self::code, self::message, self::value, self::expected, self::operator);
        } catch (Exception $e) {
            $this->exception = $e;
        }
    }
    
    public function tearDown() {}
    
    public function testValueIsInMessage() {
        return $this->assertRegex($this->exception->getUserMessage(), '/'.self::value.'/');
    }
    
    public function testExpectedValueIsInMessage() {
        return $this->assertRegex($this->exception->getUserMessage(), '/'.self::expected.'/');
    }

}


/**
 * Tests all possible codes for Snap_AssertCompareUnitTestException
 */
class Snap_AssertCompareUnitTestException_Test_Codes extends Snap_UnitTestCase {

    const message   = 'lollermessage';
    const value     = '123';
    const expected  = '456';
    const operator  = '==';

    public function setUp() {}
    public function tearDown() {}
    
    /**
     * Helper function to build a thrown exception with the supplied code
     */
    public function buildAssertWithCode($code) {
        try {
            throw new Snap_AssertCompareUnitTestException($code, self::message, self::value, self::expected, self::operator);
        }
        catch (Exception $e) {
            return $e;
        }
    }

    public function testAssertTrueInMessage() {
        $e = $this->buildAssertWithCode('assert_true');
        return $this->assertRegex($e->getUserMessage(), '/true assertion/i');
    }
    
    public function testAssertFalseInMessage() {
        $e = $this->buildAssertWithCode('assert_false');
        return $this->assertRegex($e->getUserMessage(), '/false assertion/i');
    }
    
    public function testAssertEqualInMessage() {
        $e = $this->buildAssertWithCode('assert_equal');
        return $this->assertRegex($e->getUserMessage(), '/equal/i');
    }
    
    public function testAssertSameInMessage() {
        $e = $this->buildAssertWithCode('assert_same');
        return $this->assertRegex($e->getUserMessage(), '/same/i');
    }
    
    public function testAssertNullInMessage() {
        $e = $this->buildAssertWithCode('assert_null');
        return $this->assertRegex($e->getUserMessage(), '/null assertion/i');
    }
    
    public function testAssertNotNullInMessage() {
        $e = $this->buildAssertWithCode('assert_not_null');
        return $this->assertRegex($e->getUserMessage(), '/not null assertion/i');
    }

}

?>