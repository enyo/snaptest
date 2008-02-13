<?php

include_once SNAPTEST_ROOT.'reporters'.DIRECTORY_SEPARATOR.'text.php';

class Snap_Text_UnitTestReporter_Test extends Snap_UnitTestCase {
    public function setUp() {
        ob_start();
        $this->reporter = new Snap_Text_UnitTestReporter();

        // throw assert
        try {
            throw new Snap_AssertIdenticalUnitTestException('assert_true', 'test_exception', false, true);
        }
        catch (Exception $e) {
            $this->reporter->recordTestException($e);
        }
        
        
        // unhandled exception
        try {
            throw new Exception('unhandled_foobar');
        }
        catch (Exception $e) {
            $this->reporter->recordUnhandledException($e);
        }
        
        
        // unit test exception
        try {
            throw new Snap_UnitTestException('short_msg', 'setup_exception');
        }
        catch (Exception $e) {
            $this->reporter->recordTestDefect($e);
        }
        
        
        // php error
        $this->reporter->recordPHPError('phperr_message', __FILE__, __LINE__, debug_backtrace());
        // generate and store report
        ob_start();
        $this->reporter->createReport();
        $this->reporter_output = ob_get_contents();
        ob_end_clean();
        
        ob_end_clean();
    }
    
    public function tearDown() {
        unset($this->reporter);
        unset($this->reporter_output);
    }
    
    public function testTestExceptionsInReport() {
        return $this->assertRegex($this->reporter_output, '/test_exception/');
    }
    
    public function testUnhandledExceptionInReport() {
        return $this->assertRegex($this->reporter_output, '/unhandled_foobar/');
    }
    
    public function testDefectExceptionInReport() {
        return $this->assertRegex($this->reporter_output, '/setup_exception/');
    }
    
    public function testPHPErrorInReport() {
        return $this->assertRegex($this->reporter_output, '/phperr_message/');
    }
}

class Snap_Text_UnitTestReporter_Test_Pass_Reporting_Totals extends Snap_UnitTestCase {

    const passes = 4;
    const defects = 2;
    const testcount = 7;
    
    public function setUp() {
        $this->reporter = new Snap_Text_UnitTestReporter();

        ob_start();
        $this->reporter->generateReport(array(
            array('type' => 'pass'),
            array('type' => 'pass'),
            array('type' => 'pass'),
            array('type' => 'pass'),
            array('type' => 'fail'),
            array('type' => 'defect'),
            array('type' => 'defect'),
            array('type' => 'case'),
        ));
        $this->reporter_output = ob_get_contents();
        ob_end_clean();
    }

    public function tearDown() {
        unset($this->reporter);
        unset($this->reporter_output);
    }

    public function testPassesTotalInReport() {
        return $this->assertRegex($this->reporter_output, '/^.*?total pass:.*?'.self::passes.'.*?$/im');
    }
    
    public function testDefectsTotalInReport() {
        return $this->assertRegex($this->reporter_output, '/^.*?total defects:.*?'.self::defects.'.*?$/im');
    }
    
    public function testTestsRanTotalInReport() {
        return $this->assertRegex($this->reporter_output, '/^.*?total tests:.*?'.self::testcount.'.*?$/im');
    }

    public function testFailuresTotalInReport() {
        return $this->assertRegex($this->reporter_output, '/^.*?total failures:.*?'.(self::testcount - self::passes - self::defects).'.*?$/im');
    }
    
    public function testCasesRanTotalInReport() {
        return $this->assertRegex($this->reporter_output, '/^.*?total cases:.*?1.*?$/im');
    }

}

?>