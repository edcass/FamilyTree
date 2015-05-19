<?php
/**
 * 
 * @author: ecass
 * Date: 5/18/15
 * Time: 3:18 PM
 */
require_once('../FamilyTree.php');
chdir('../');
class FamilyTreeTest extends PHPUnit_Framework_TestCase {
    public function testGetGrandParent() {
        $tests = array(
            'Nancy' => array('Cathrine', 'Kevin'),
            'Jill' => array('Samuel', 'George', 'James', 'Aaron')
        );

        foreach ($tests as $grandparent => $grandkid) {
            foreach($grandkid as $kid) {
                $familyTree = new FamilyTree();
                $tested = $familyTree->getGrandParent($kid);
                $this->assertEquals($tested, $grandparent);
            }
        }
    }

    public function testGetOnlyChildren() {
        $family = new FamilyTree();
        $noSibs = $family->getOnlyChildren();

        $test = array('Kevin', 'Mary');
        $this->assertEquals(count($noSibs), count($test));
        foreach ($test as $k) {
            $this->assertTrue(in_array($k, $noSibs));
        }
    }


    public function testGetChildfree() {
        $test = array('Kevin', 'Mary');
        $family = new FamilyTree();
        $free = $family->getChildfree();
        $this->assertEquals(count($free), count($test));

        foreach ($test as $k) {
            $this->assertTrue(in_array($k, $free));
        }
    }

    public function testGetMostProlific() {
        $family = new FamilyTree();
        $most = $family->getMostProlific();
        $this->assertEquals($most, 'Kevin');
    }

    public function testDrawFamilyTree() {
        $shouldBe = file_get_contents('images/test.svg');
        $family = new FamilyTree();
        $filePath = $family->drawFamilyTree();
        $is = file_get_contents($filePath); // prefix a dot to fix path.
        $this->assertEquals($shouldBe, $is);

    }
}
