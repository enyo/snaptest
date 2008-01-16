<?php

/**
 * Serialized Reports Output, used in aggregation
 */
class Snap_PHPSerializer_UnitTestReporter extends Snap_UnitTestReporter {

    /**
     * generate a text based report of the output data
     * @return void
     */
    public function generateReport() {
        echo '===START===' . serialize($this->reports) . '===END===';
    }
    
    public function announceTestPass() {}
    
    public function announceTestFail() {}
    
    public function announceTestDefect() {}
    
    public function announceTestCaseComplete() {}
}
