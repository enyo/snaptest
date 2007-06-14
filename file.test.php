<?php

include_once 'file.php';

/**
 * Tests Snap_FileUtil
 */
class Snap_FileUtil_Test extends Snap_UnitTestCase {
    
    public function setUp() {
        $this->util = new Snap_FileUtil();
    }
    
    public function tearDown() {
        unset($this->util);
    }
    
    public function testIsInstanceOfSnapFileUtil() {
        return $this->assertIsA($this->util, 'Snap_FileUtil');
    }
    
}

/**
 * Tests Snap_FileUtil and it's makefile format
 */
class Snap_FileUtil_Test_makeFile extends Snap_UnitTestCase {

    public function setUp() {
        $this->util = new Snap_FileUtil();
        $this->path = $this->util->makeFile();
    }
    
    public function tearDown() {
        unset($this->util);
        unset($this->path);
    }
    
    public function testPathIsFilename() {
        return $this->assertTrue((strlen($this->path) > 0) ? true : false);
    }
    
    public function testFileExists() {
        return $this->assertTrue(file_exists($this->path));
    }
    
    public function testFileIsWritable() {
        return $this->assertTrue(is_writable($this->path));
    }

}

/**
 * test getFileList
 */
class Snap_FileUtil_Test_getFileList extends Snap_UnitTestCase {
    public function setUp() {
        $this->util = new Snap_FileUtil();
        $this->util->makeFile();
        $this->util->makeFile();
        $this->file_count = count($this->util->getFileList());
    }
    public function tearDown() {
        unset($this->util);
        unset($this->file_count);
    }
    
    public function testIsEqualToNumberOfMakeFileCalled() {
        return $this->assertEqual($this->file_count, 2);
    }
    
    public function testEveryFileIsUnique() {
        $files = $this->util->getFileList();
        return $this->assertNotEqual($files[0], $files[1]);
    }
}

/**
 * test reset method on Snap_FileUtil
 */
class Snap_FileUtil_Test_reset extends Snap_UnitTestCase {
    public function setUp() {
        $this->util = $this->mock('Snap_FileUtil')->requiresInheritence()->construct();
        $this->util->makeFile();
        $this->util->makeFile();
        $this->file_count = count($this->util->getFileList());
        
        $this->util->reset();
        $this->reset_file_count = count($this->util->getFileList());
    }
    
    public function tearDown() {
        unset($this->util);
        unset($this->file_count);
        unset($this->reset_file_count);
    }
    
    public function testCountAfterResetIsZero() {
        return $this->assertEqual($this->reset_file_count, 0);
    }
    
    public function testCountBeforeAndAfterResetAreDifferent() {
        return $this->assertNotEqual($this->file_count, $this->reset_file_count);
    }
    
    public function testResetTriggersGarbageCollection() {
        return $this->assertCallCount($this->util, 'gc', 1);
    }
}

/**
 * test gc to ensure it garbage collects
 */
class Snap_FileUtil_Test_gc extends Snap_UnitTestCase {
    public function setUp() {
        $this->util = new Snap_FileUtil();
        $this->path = $this->util->makeFile();
        
        $this->util->gc();
    }
    
    public function tearDown() {
        unset($this->util);
    }
    
    public function testFileNoLongerExists() {
        return $this->assertFalse(file_exists($this->path));
    }
}

?>