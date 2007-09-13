<?php

/**
 * Text Output Unit Test Reporter
 */
class Snap_Text_UnitTestReporter extends Snap_UnitTestReporter {

    /**
     * generate a text based report of the output data
     * @return void
     */
    public function generateReport() {
        echo "\n";
        foreach ($this->reports as $report) {
            echo $report."\n";
        }
        
        echo '______________________________________________________________________'."\n";
        echo 'Total Cases:    '.$this->test_cases."\n";
        echo 'Total Tests:    '.$this->tests."\n";
        echo 'Total Pass:     '.$this->passes."\n";
        echo 'Total Defects:  '.$this->defects."\n";
        echo 'Total Failures: '.($this->tests - $this->passes - $this->defects)."\n";
        
        if ($this->php_errors > 0) {
            echo "\n".'You have unchecked errors in your tests.  These errors should be'."\n";
            echo 'removed, or acknowledged with $this->willError() in their respective'."\n";
            echo 'tests.'."\n";
        }
    }
    
    protected function announceTestPasses($passes, $defects, $tests, $classname) {
        $failures = $tests - $passes - $defects;
        
        if ($failures > 0) {
            echo 'F';
            return;
        }
        
        if ($defects > 0) {
            echo 'D';
            // echo $defects . ' in '.$classname;
            return;
        }
        
        echo '.';
        return;
    }
    
    /**
     * turn a trace and message into it's final output.
     * @param string $message the input message
     * @param array $origin the array origin for the message
     */
    protected function recordMessage($message, $origin) {
        $output  = $message;
        $output .= "\n";
        
        $output .= '    in method: ';
        $output .= (isset($origin['function'])) ? $origin['function'] : 'unknown';
        $output .= "\n";
        
        $output .= '    in class:  ';
        $output .= (isset($origin['class'])) ? $origin['class'] : 'unknown';
        $output .= "\n";
        
        $output .= '    in file:   ';
        $output .= (isset($origin['file'])) ? $origin['file'] : 'unknown';
        
        return $output;
    }
}
?>