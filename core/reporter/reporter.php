<?php

interface Snap_UnitTestReporterInterface {
    public function __construct();
    public function createReport();
    public function recordTestPass($class, $method);
    public function recordTestCaseComplete($class);
    public function recordTestException(Snap_UnitTestException $e);
    public function recordTestTodo(Snap_UnitTestException $e);
    public function recordTestSkip(Snap_UnitTestException $e);
    public function recordUnhandledException(Exception $e);
    public function recordTestDefect(Exception $e);
    public function recordPHPError($errstr, $errfile, $errline, $trace);
    public function generateReport($reports);
    public function announceTestPass();
    public function announceTestFail();
    public function announceTestDefect();
    public function announceTestSkip();
    public function announceTestTodo();
    public function announceTestCaseComplete();
}

class Snap_UnitTestReporter {

    /**
     * contains the lines of the report as they occur
     * @var array $reports
     */
    protected $reports;
    
    /**
     * report constructor, initializes all the variables
     */
    public function __construct() {
        $this->reports = array();
    }
    
    public final function createReport() {
        $this->generateReport($this->reports);
    }
    
    /**
     * records a test passing and adds it to the queue
     * @param string Class name
     * @param string Method name
     **/
    public final function recordTestPass($class_name, $method_name) {
        $this->reports[] = array(
            'type'      => 'pass',
            'function'  => $method_name,
            'class'     => $class_name,
        );
        $this->announceTestPass();
    }
    
    public final function recordTestCaseComplete($class_name) {
        $this->reports[] = array(
            'type'      => 'case',
            'class'     => $class_name,
        );
        $this->announceTestCaseComplete();
    }

    /**
     * records a test exception and adds it to the report queue
     * @param UnitTestException $e
     */
    public final function recordTestException(Snap_UnitTestException $e) {
        $this->addReport($this->record('fail', $e->getUserMessage(), $this->cullTrace($e->getTrace())));
        $this->announceTestFail();
    }
    
    public final function recordTestTodo(Snap_UnitTestException $e) {
        $this->addReport($this->record('todo', $e->getUserMessage(), $this->cullTrace($e->getTrace())));        
        $this->announceTestTodo();
    }
    
    public final function recordTestSkip(Snap_UnitTestException $e) {
        $this->addReport($this->record('skip', $e->getUserMessage(), $this->cullTrace($e->getTrace())));        
        $this->announceTestSkip();
    }
    
    /**
     * records an unhandled exception and adds it to the report queue
     * @param Exception $e
     */
    public final function recordUnhandledException(Exception $e) {
        $this->addReport($this->record('exception', 'Unhandled exception of type '.get_class($e).' with message: '.$e->getMessage(), $this->cullTrace($e->getTrace())));
        $this->announceTestFail();
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
        $this->announceTestDefect();
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
        $trace['file'] = $errfile;
        
        $this->addReport($this->record('phperr', $errstr, $trace, $errline));
        $this->announceTestFail();
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
                if (isset($trace[0]['file'])) {
                    $file = $trace[0]['file'];
                }
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

}

