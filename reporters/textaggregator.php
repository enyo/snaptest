<?php

/**
 * Text Output Unit Test Reporter
 */
class Snap_TextAggregator_UnitTestReporter extends Snap_UnitTestReporter {

    /**
     * generate a text based report of the output data
     * @return void
     */
    public function generateReport() {
        echo "===BEGIN TEST===\n";

        $output = array(
            'reports' => $this->reports,
            'test_cases' => $this->test_cases,
            'tests' => $this->tests,
            'passes' => $this->passes,
            'defects' => $this->defects,
            'php_errors' => $this->php_errors,
        );
        
        echo serialize($output);

        echo "\n===END TEST===\n";
    }
    
    protected function announceTestPasses($passes, $defects, $tests, $classname) {}
    
    /**
     * Retrieve Test Data from Output
     * This is a reversal of generateReport(), capturing the data and unserializing
     * it.
     * @param $data the string of data to unserialize
     * @return array
     **/
    public function extractReportData($data) {
        preg_match('/===BEGIN TEST===\s*(.*?)\s*===END TEST===/m', $data, $matches);
        return unserialize($matches[1]);
    }
    
}
