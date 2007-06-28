<?php

include_once 'expectations.php';

/** 
 * Tests Snap_Anything_Expectation
 */
class Snap_Anything_Expectation_Test extends Snap_UnitTestCase {

    const expect = 'foo';
    const match = 'bar';

    public function setUp() {
        $this->expectation = new Snap_Anything_Expectation(self::expect);
        $this->result = $this->expectation->match(self::match);
    }
    
    public function tearDown() {
        unset($this->expectation);
        unset($this->result);
    }
    
    public function testIsAnInstanceOfSnapExpectation() {
        return $this->assertTrue($this->expectation instanceof Snap_Expectation);
    }
    
    public function testMatchReturnsTrue() {
        return $this->assertTrue($this->result);
    }
}

/**
 * Tests Snap_Equals_Expectation
 */
class Snap_Equals_Expectation_Test extends Snap_UnitTestCase {

    const expect = 1;
    const actual = '1';

    public function setUp() {
        $this->expectation = new Snap_Equals_Expectation(self::expect);
        $this->response = $this->expectation->match(self::actual);
    }
    
    public function tearDown() {
        unset($this->expectation);
        unset($this->actual);
    }
    
    public function testIsAnInstanceOfSnapExpectation() {
        return $this->assertTrue($this->expectation instanceof Snap_Expectation);
    }
    
    public function testLooseTypeMatches() {
        return $this->assertTrue($this->response);
    }
}


/**
 * Tests Snap_Same_Expectation
 */
class Snap_Same_Expectation_Test extends Snap_UnitTestCase {

    const expect = 1;
    const actual = '1';

    public function setUp() {}
    
    public function tearDown() {}
    
    public function testIsAnInstanceOfSnapExpectation() {
        $expectation = new Snap_Same_Expectation(self::expect);
        return $this->assertTrue($expectation instanceof Snap_Expectation);
    }
    
    public function testLooseTypeFails() {
        $expectation = new Snap_Same_Expectation(self::expect);
        $response = $expectation->match(self::actual);
        return $this->assertFalse($response);
    }
    
    public function testStrictTypeMatches() {
        $expectation = new Snap_Same_Expectation(self::expect);
        $response = $expectation->match(self::expect);
        return $this->assertTrue($response);
    }

}


/**
 * Tests Snap_Object_Expectation
 */
class Snap_Object_Expectation_Test extends Snap_UnitTestCase {

    public function setUp() {
        $this->expect = new Exception();
        $this->non_object = 'foo';
        $this->wrong_object = new Snap_Anything_Expectation();
    }
    
    public function tearDown() {
        unset($this->expect);
        unset($this->non_object);
        unset($this->wrong_object);
    }
    
    public function testIsAnInstanceOfSnapExpectation() {
        $expectation = new Snap_Same_Expectation($this->expect);
        return $this->assertTrue($expectation instanceof Snap_Expectation);
    }
    
    public function testNonObjectFails() {
        $expectation = new Snap_Same_Expectation($this->expect);
        $response = $expectation->match($this->non_object);
        return $this->assertFalse($response);
    }
    
    public function testWrongObjectFails() {
        $expectation = new Snap_Same_Expectation($this->expect);
        $response = $expectation->match($this->wrong_object);
        return $this->assertFalse($response);
    }
    
    public function testStrictTypeMatches() {
        $expectation = new Snap_Same_Expectation($this->expect);
        $response = $expectation->match($this->expect);
        return $this->assertTrue($response);
    }

}



/**
 * Tests Snap_Regex_Expectation
 */
class Snap_Regex_Expectation_Test extends Snap_UnitTestCase {

    const haystack = 'the quick brown fox jumped over the lazy dogs';
    const ok_needle = '/quick brown/';
    const bad_needle = '/slow fox/';
    
    public function setUp() {}
    public function tearDown() {}
    
    public function testIsAnInstanceOfSnapExpectation() {
        $exp = new Snap_Regex_Expectation(self::ok_needle);
        return $this->assertTrue($exp instanceof Snap_Expectation);
    }
    
    public function testValidRegexPasses() {
        $exp = new Snap_Regex_Expectation(self::ok_needle);
        return $this->assertTrue($exp->match(self::haystack));
    }
    
    public function testNonMatchingRegexFails() {
        $exp = new Snap_Regex_Expectation(self::bad_needle);
        return $this->assertFalse($exp->match(self::haystack));
    }

}

?>