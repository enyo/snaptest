<?php

/**
 * Serialized Reports Output, used in aggregation
 */
class Snap_PHPSerializer_UnitTestReporter extends Snap_UnitTestReporter implements Snap_UnitTestReporterInterface {

    /**
     * generate a text based report of the output data
     * @return void
     */
    public function generateReport($reports) {
        echo SNAPTEST_TOKEN_START . serialize($reports) . SNAPTEST_TOKEN_END;
    }
    
    public function announceTestPass() {}
    
    public function announceTestFail() {}
    
    public function announceTestDefect() {}

    public function announceTestTodo() {}
    
    public function announceTestSkip() {}
    
    public function announceTestCaseComplete() {}
}
