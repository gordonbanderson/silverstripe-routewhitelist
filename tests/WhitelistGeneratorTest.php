<?php
class WhitelistGeneratorTest extends SapphireTest{

	protected static $fixture_file = 'WhitelistTest.yml';

    public function setUp() {
        WhitelistGenerator::ensureWhitelistFolderExists();
        parent::setUp();
    }

	function testGenerateWhitelist(){
		$whitelist = WhitelistGenerator::generateWhitelistRules();

		$top1 = $this->objFromFixture('SiteTree', 'top1');
		$top2 = $this->objFromFixture('SiteTree', 'top2');
		$top3 = $this->objFromFixture('SiteTree', 'top3');
		$child1 = $this->objFromFixture('SiteTree', 'child1');
		$child2 = $this->objFromFixture('SiteTree', 'child2');
		$child3 = $this->objFromFixture('SiteTree', 'childchild1');
		$child4 = $this->objFromFixture('SiteTree', 'childchild2');
		$child5 = $this->objFromFixture('SiteTree', 'childchildchild1');

		$this->assertContains(trim($top1->relativeLink(),'/'), $whitelist);
		$this->assertContains(trim($top2->relativeLink(),'/'), $whitelist);
		$this->assertContains(trim($top3->relativeLink(),'/'), $whitelist);
		$this->assertNotContains(trim($child1->relativeLink(),'/'), $whitelist);
		$this->assertNotContains(trim($child2->relativeLink(),'/'), $whitelist);
		$this->assertNotContains(trim($child3->relativeLink(),'/'), $whitelist);
		$this->assertNotContains(trim($child4->relativeLink(),'/'), $whitelist);
		$this->assertNotContains(trim($child5->relativeLink(),'/'), $whitelist);

        error_log(print_r($whitelist, 1));
	}

    function testWhitelistAfterDelete() {
        $dir = BASE_PATH . DIRECTORY_SEPARATOR . Config::inst()->get('WhitelistGenerator', 'dir');
        $whitelist = WhitelistGenerator::generateWhitelistRules();
        $top1 = $this->objFromFixture('SiteTree', 'top1');
        $path = $dir . '/' . $top1->URLSegment;

        //Check that relevant file exists in cache directory of checks
        $this->assertTrue(file_exists($path));

        //Now assert that the same file has been rightfully deleted
        $top1->delete();
        $this->assertFalse(file_exists($path));
    }

    private function getFilesFromCacheDir() {
        $dir = BASE_PATH . DIRECTORY_SEPARATOR . Config::inst()->get('WhitelistGenerator', 'dir');
        $files = array();
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..') {
                    array_push($files, $entry);
                }
            }
        }
        return $files;
    }

    function testWhitelistAfterUnpublish() {
        $dir = BASE_PATH . DIRECTORY_SEPARATOR . Config::inst()->get('WhitelistGenerator', 'dir');
        $whitelist = WhitelistGenerator::generateWhitelistRules();
        $top1 = $this->objFromFixture('SiteTree', 'top1');
        $path = $dir . '/' . $top1->URLSegment;

        //Check that relevant file exists in cache directory of checks
        $this->assertTrue(file_exists($path));

        //Now assert that the same file has not been deleted, still exists on Stage
        $top1->doUnpublish();
        $this->assertTrue(file_exists($path));
    }

    function testClearWhitelist() {
        $whitelist = WhitelistGenerator::generateWhitelistRules();
        $files = $this->getFilesFromCacheDir();
        error_log(print_r($files,1));
        $this->assertEquals(119, sizeof($files));

        WhitelistGenerator::clearWhitelist();
        $files = $this->getFilesFromCacheDir();
        //error_log(print_r($files,1));
        $this->assertEquals(119, sizeof($files));

    }

	function testCustomControllerWhitelist() {
		$whitelist = WhitelistGenerator::generateWhitelistRules();

		//test that custom class defined below is included in the whitelist
		$this->assertContains('WhitelistTestController', $whitelist);
	}

}

class WhitelistTestController extends ContentController {


}
