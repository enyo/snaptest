<?php

require dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'path.php';

class Snap_File_UnitTestLoader_Test extends Snap_UnitTestCase {

    public function setUp() {
        $this->loader = new Snap_File_UnitTestLoader();
        $this->fileutil = new Snap_FileUtil();
        
        $this->file = $this->fileutil->makeFile();
        
        $random_class_name = 'cl'.md5(mt_rand());
        
        $random_class_name_a = $random_class_name.'_a';
        $random_class_name_b = $random_class_name.'_b';
        $random_class_name_c = $random_class_name.'_c';
        
        $random_class_name_not_test         = $random_class_name.'_na';
        $random_class_name_secondary_test   = $random_class_name.'_sec';
        
        // create
        $content  = "<"."?php\n";
        $content .= "class $random_class_name_a extends Snap_UnitTestCase {\n";
        $content .= "    public function setUp() {}\n";
        $content .= "    public function tearDown() {}\n";
        $content .= "    public function testItemA() {}\n";
        $content .= "}\n";
        $content .= "class $random_class_name_b extends Snap_UnitTestCase {\n";
        $content .= "    public function setUp() {}\n";
        $content .= "    public function tearDown() {}\n";
        $content .= "    public function testItemA() {}\n";
        $content .= "}\n";
        $content .= "class $random_class_name_c extends Snap_UnitTestCase {\n";
        $content .= "    public function setUp() {}\n";
        $content .= "    public function tearDown() {}\n";
        $content .= "    public function testItemA() {}\n";
        $content .= "}\n";
        $content .= "class $random_class_name_not_test {\n";
        $content .= "    public function setUp() {}\n";
        $content .= "    public function tearDown() {}\n";
        $content .= "    public function testItemA() {}\n";
        $content .= "}\n";
        $content .= "class $random_class_name_secondary_test {\n";
        $content .= "    public function setUp() {}\n";
        $content .= "    public function tearDown() {}\n";
        $content .= "    public function testItemA() {}\n";
        $content .= "    public function runTests() {}\n";
        $content .= "}\n";
        $content .= "?".">\n";
        
        file_put_contents($this->file, $content);
        
        $this->loader->add($this->file);
        $this->tests_loaded = $this->loader->getTests();
    }
    
    public function tearDown() {
        unset($this->loader);
        unset($this->fileutil);
        unset($this->file);
        unset($this->tests_loaded);
    }
    
    public function testIsASnapFileUnitTestLoader() {
        return $this->assertIsA($this->loader, 'Snap_File_UnitTestLoader');
    }
    
    public function testFourClassesLoaded() {
        return $this->assertEqual(count($this->tests_loaded), 4);
    }
}

class Snap_File_UnitTestLoader_Test_Bad_Files extends Snap_UnitTestCase {
    public function setUp() {
        $this->loader = new Snap_File_UnitTestLoader();
        $this->fileutil = new Snap_FileUtil();
        
        $this->file = $this->fileutil->makeFile();

        @unlink($this->file);
    }
    
    public function tearDown() {
        unset($this->loader);
        unset($this->fileutil);
        unset($this->file);
    }
    
    public function testWillThrowAnException() {
        $this->willThrow('Snap_File_UnitTestLoader_LoadException');
        $this->loader->add($this->file);
    }
}

