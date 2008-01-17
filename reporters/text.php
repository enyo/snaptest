<?php
/**
 * Text Output Unit Test Reporter
 */
class Snap_Text_UnitTestReporter extends Snap_UnitTestReporter {

    /**
     * generate a text based report of the output data
     * @return void
     */
    public function generateReport($reports) {
        $cases  = 0;
        $pass   = 0;
        $defect = 0;
        $fail   = 0;
        $error  = 0;

        echo "\n";
        if (is_array($reports)) foreach ($reports as $report) {
            
            // passes
            if ($report['type'] == 'pass') {
                $pass++;
                continue;
            }
            elseif ($report['type'] == 'case') {
                $cases++;
                continue;
            }
            elseif ($report['type'] == 'defect') {
                $defect++;
            }
            elseif ($report['type'] == 'phperr') {
                $error++;
            }
            else {
                $fail++;
            }
            
            $output  = (isset($report['message'])) ? $report['message'] : '[No Message Supplied]';
            $output .= "\n";
                    
            $output .= '    in method: ';
            $output .= (isset($report['function'])) ? $report['function'] : 'unknown';
            $output .= "\n";
                    
            $output .= '    in class:  ';
            $output .= (isset($report['class'])) ? $report['class'] : 'unknown';
            $output .= "\n";
                    
            $output .= '    in file:   ';
            $output .= (isset($report['file'])) ? $report['file'] : 'unknown';
            $output .= (isset($report['line']) && strlen($report['line']) > 0) ? ' ('.$report['line'].')' : '';
            $output .= "\n";
            
            echo $output;
        }
        
        $tests = $pass + $fail + $defect;
        
        echo '______________________________________________________________________'."\n";
        echo 'Total Cases:    '.$cases."\n";
        echo 'Total Tests:    '.$tests."\n";
        echo 'Total Pass:     '.$pass."\n";
        echo 'Total Defects:  '.$defect."\n";
        echo 'Total Failures: '.$fail."\n";
        
        if ($error > 0) {
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
    
    public function announceTestPass() {
        echo '.';
    }
    public function announceTestFail() {
        echo 'F';
    }
    public function announceTestDefect() {
        echo 'D';
    }
    public function announceTestCaseComplete() {}
}
