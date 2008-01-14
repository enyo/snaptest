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
     * addons loaded
     * @var array $addons
     **/
    protected $addons;
    
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
    
    public final function createReport($reports, $test_cases, $total_tests, $passes, $defects, $php_errors) {
        $this->reports = $reports;
        $this->test_cases = $test_cases;
        $this->tests = $total_tests;
        $this->passes = $passes;
        $this->defects = $defects;
        $this->php_errors = $php_errors;
        $this->generateReport();
    }

    /**
     * records a test exception and adds it to the report queue
     * @param UnitTestException $e
     */
    public final function recordTestException(Snap_UnitTestException $e) {
        $this->addReport($this->record('user', $e->getUserMessage(), $this->cullTrace($e->getTrace())));
    }
    
    /**
     * records an unhandled exception and adds it to the report queue
     * @param Exception $e
     */
    public final function recordUnhandledException(Exception $e) {
        $this->addReport($this->record('unhandled_exception', 'Unhandled exception of type '.get_class($e).' with message: '.$e->getMessage(), $this->cullTrace($e->getTrace())));
    }
    
    /**
     * records a test defect exception that occured in setup/teardown
     * @param UnitTestException $e
     */
    public final function recordTestDefect(Exception $e) {
        if (method_exists($e, 'getUserMessage')) {
            $this->addReport($this->record('defect', $e->getUserMessage(), $this->cullTrace($e->getTrace())));
        }
        else {
            $this->addReport($this->record('defect', $e->getMessage(), $this->cullTrace($e->getTrace())));
        }
    }
    
    /**
     * records a PHP error encountered
     * @param string $errstr the php error string
     * @param string $errfile the php error file
     * @param int $errline the line of the php error
     * @param array $trace the backtrace of the error
     */
    public final function recordPHPError($errstr, $errfile, $errline, $trace) {

        $trace = $this->cullTrace($trace);
        
        // file trace is worthless
        unset($trace['file']);
        
        $this->addReport($this->record('error', $errstr, $trace, $errline));
        $this->php_errors++;
    }
    
    /**
     * add the test results to the reporter's totals
     * @param int $passes the number of passes
     * @param int $tests the number of tests ran
     * @param string $classname the name of the class the report came from
     */
    public final function addTestPasses($passes, $defects, $tests, $classname) {
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
    protected final function cullTrace($trace) {
        $file = '';
        
        while (true) {
            if (!isset($trace[0])) {
                break;
            }
        
            // drill up until you find a unit test: testXXXXX or setUp or tearDown
            if (isset($trace[0]['function']) && (!preg_match('/^(test.*)|(setUp)|(tearDown)$/i', $trace[0]['function']))) {
                $file = $trace[0]['file'];
                array_shift($trace);
                continue;
            }
            
            break;
        }
        
        if (!isset($trace[0])) {
            return array();
        }
        
        // restore the proper file
        $trace[0]['file'] = $file;
        
        return $trace[0];
    }
    
    /**
     * add a report to the output stack
     * @param string $output the output to add to the report
     */
    protected final function addReport($output) {
        $this->reports[] = $output;
    }
    
    /**
     * turn a trace and message into it's final output.
     * @param string $message the input message
     * @param array $origin the array origin for the message
     */
    protected function record($type, $message, $backtrace, $line = '') {
        $output = array(
            'type'      => $type,
            'message'   => $message,
            'function'  => (isset($backtrace['function'])) ? $backtrace['function'] : 'unknown',
            'class'     => (isset($backtrace['class'])) ? $backtrace['class'] : 'unknown',
            'file'      => (isset($backtrace['file'])) ? $backtrace['file'] : 'unknown',
            'line'      => $line,
        );
        
        return $output;
    }
    
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