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
        if (is_array($this->reports)) foreach ($this->reports as $report) {
            $output  = $report['message'];
            $output .= "\n";
                    
            $output .= '    in method: ';
            $output .= (isset($report['function'])) ? $report['function'] : 'unknown';
            $output .= "\n";
                    
            $output .= '    in class:  ';
            $output .= (isset($report['class'])) ? $report['class'] : 'unknown';
            $output .= "\n";
                    
            $output .= '    in file:   ';
            $output .= (isset($report['file'])) ? $report['file'] : 'unknown';
            $output .= "\n";
            
            echo $output;
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
        
        $addons = unserialize(SNAP_ADDONS);
        if (count($addons) > 0) {
            echo "\nAddons Loaded:\n";
            foreach($addons as $addon) {
                echo '    '.$addon['name']."\n";
            }
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
            return;
        }
        
        echo '.';
        return;
    }
}
