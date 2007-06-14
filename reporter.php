<?php

abstract class Snap_UnitTestReporter {

    /**
     * contains the lines of the report as they occur
     * @var array $reports
     */
    protected $reports;
    
    /**
     * contains the total number of tests
     * @var int $passes
     */
    protected $tests;
    
    /**
     * contains the total of passed tests
     * @var int $passes
     */
    protected $passes;
    
    /**
     * contains the total of defective tests
     * @var int $defects
     */
    protected $defects;
    
    /**
     * contains the total of php errors found
     * @var int $php_errors
     */
    protected $php_errors;
    
    /**
     * contains the total of cases
     * @var int $test_cases
     */
    protected $test_cases;
    
    /**
     * report constructor, initializes all the variables
     */
    public function __construct() {
        $this->tests = 0;
        $this->passes = 0;
        $this->defects = 0;
        $this->php_errors = 0;
        $this->test_cases = 0;
        $this->reports = array();
    }

    /**
     * records a test exception and adds it to the report queue
     * @param UnitTestException $e
     */
    public function recordTestException(Snap_UnitTestException $e) {
        $this->recordMessage($e->getUserMessage(), $this->cullTrace($e->getTrace()));
    }
    
    /**
     * records an unhandled exception and adds it to the report queue
     * @param Exception $e
     */
    public function recordUnhandledException(Exception $e) {
        $this->recordMessage('Unhandled exception with message: '.$e->getMessage(), $this->cullTrace($e->getTrace()));
    }
    
    /**
     * records a test defect exception that occured in setup/teardown
     * @param UnitTestException $e
     */
    public function recordTestDefect(Exception $e) {
        if (method_exists($e, 'getUserMessage')) {
            $this->recordMessage('Defect: '.$e->getUserMessage(), $this->cullTrace($e->getTrace()));
        }
        else {
            $this->recordMessage('Defect: '.$e->getMessage(), $this->cullTrace($e->getTrace()));
        }
    }
    
    /**
     * records a PHP error encountered
     * @param string $errstr the php error string
     * @param string $errfile the php error file
     * @param int $errline the line of the php error
     * @param array $trace the backtrace of the error
     */
    public function recordPHPError($errstr, $errfile, $errline, $trace) {

        $trace = $this->cullTrace($trace);
        
        // file trace is worthless
        unset($trace['file']);
        
        $this->recordMessage('Error: '.$errstr. '[line: '.$errline.']', $trace);
        $this->php_errors++;
    }
    
    /**
     * add the test results to the reporter's totals
     * @param int $passes the number of passes
     * @param int $tests the number of tests ran
     * @param string $classname the name of the class the report came from
     */
    public function addTestPasses($passes, $defects, $tests, $classname) {
        $this->passes += $passes;
        $this->tests += $tests;
        $this->defects += $defects;
        $this->test_cases++;
        
        $this->announceTestPasses($passes, $defects, $tests, $classname);
    }
    
    /**
     * cull a trace, removing the unit test cruft and leaving the traced item
     * @param array $trace the trace array
     * @return array an array reduced to the occurance of the test/setup
     */
    protected function cullTrace($trace) {
        while (true) {
            if (!isset($trace[0])) {
                break;
            }
        
            // drill up until you find a unit test: testXXXXX or setUp or tearDown
            if (isset($trace[0]['function']) && (!preg_match('/^(test.*)|(setUp)|(tearDown)$/i', $trace[0]['function']))) {
                array_shift($trace);
                continue;
            }
            
            break;
        }
        
        if (!isset($trace[0])) {
            return array();
        }
        
        return $trace[0];
    }
    
    /**
     * add a report to the output stack
     * @param string $output the output to add to the report
     */
    protected function addReport($output) {
        $this->reports[] = $output;
    }
    
    /**
     * abstract function, records a message calling $this->addReport()
     * @param string $message the message to store
     * @param array $origin the origin of the message
     */
    abstract protected function recordMessage($message, $origin);
    
    /**
     * abstract function, generates the final report
     */
    abstract public function generateReport();
    
    /**
     * abstract function, handles announcing a test's completion to the output
     * @param int $passes the number of passes
     * @param int $defects the number of defects
     * @param int $tests the number of tests
     * @param string $classname the name of the test class
     */
    abstract protected function announceTestPasses($passes, $defects, $tests, $classname);

}

?>