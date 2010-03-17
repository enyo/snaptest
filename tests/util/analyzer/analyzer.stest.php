<?php

require dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'path.php';

/**
 * tests the analyzer
 **/
class Snap_FileAnalyzer_Test extends Snap_UnitTestCase {
    
    const test_output_one = 'test output one';
    const test_output_two = 'test output two';
    
    public function setUp() {
        $this->seed_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'seed.php';
        $this->seed_file2 = dirname(__FILE__).DIRECTORY_SEPARATOR.'seed.php2';
        
        $this->FileAnalyzer = new Snap_FileAnalyzer();
        $this->file_analysis = $this->FileAnalyzer->analyzeFile($this->seed_file);
        
        $this->FileAnalyzer->onThreadComplete($this->seed_file, SNAPTEST_TOKEN_START . serialize(self::test_output_one) . SNAPTEST_TOKEN_END);
        $this->FileAnalyzer->onThreadFail($this->seed_file2, self::test_output_two);
    }
    
    public function tearDown() {
        unset($this->FileAnalyzer);
        unset($this->file_analysis);
        unset($this->seed_file);
        unset($this->seed_file2);
    }
    
    public function testSeedFileRecordedTwoTests() {
        return $this->assertIdentical(count($this->file_analysis), 2);
    }
    
    public function testSeedTestHasTwoTestsInIt() {
        return $this->assertIdentical(count($this->file_analysis['Snap_FileAnalyzer_Seed_Test']), 2);
    }
    
    public function testOnCompleteReturnsTestOutputOneForSeedOne() {
        $results = $this->FileAnalyzer->onComplete();
        return $this->assertEqual($results[$this->seed_file], self::test_output_one);
    }
    
    public function testOnCompleteReturnsTestOutputTwoForSeedTwo() {
        $results = $this->FileAnalyzer->onComplete();
        return $this->assertEqual($results[$this->seed_file2], self::test_output_two);
    }
}

/**
 * test an analyzer on a class that is abstract (should find no tests)
 **/
class Snap_FileAnalyzer_Test_AbstractClass extends Snap_UnitTestCase {
    const test_output_one = 'test output one';
    const test_output_two = 'test output two';
    
    public function setUp() {
        $this->seed_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'seed_abstract.php';
        
        $this->FileAnalyzer = new Snap_FileAnalyzer();
        $this->file_analysis = $this->FileAnalyzer->analyzeFile($this->seed_file);
        
        $this->FileAnalyzer->onThreadComplete($this->seed_file, SNAPTEST_TOKEN_START . serialize(self::test_output_one) . SNAPTEST_TOKEN_END);
    }
    
    public function tearDown() {
        unset($this->FileAnalyzer);
        unset($this->file_analysis);
        unset($this->seed_file);
    }
    
    public function testSeedFileContainsNoTests() {
      return $this->assertIdentical(count($this->file_analysis), 0);
    }
}