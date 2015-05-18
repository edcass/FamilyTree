<?php
/**
 * 
 * @author: ecass
 * Date: 5/18/15
 * Time: 8:52 AM
 */

class FamilyTree {
    private $jsonFilePath = './data/example.json';
    private $tree = array();
    private $currentLinage = array();
    private $currentLine = array();
    private $currentGeneration = 0;
    private $searchedFor = '';
    private $list = array();

    public function __construct($jsonFilePath=false) {
        if ($jsonFilePath) {
            $this->jsonFilePath = $jsonFilePath;
        }

        $contents = file_get_contents($this->jsonFilePath);

        if (!$contents) {
            throw new Exception('Could not file file: ' . $this->jsonFilePath);
        }

        $this->tree = json_decode($contents, true);

        if (!is_array($this->tree)) {
            throw new Exception('Usage: php FamilyTree.php <path to valid json file>');
        }
    }

    public function getGrandParent($name) {
        $this->currentLine = array();
        $this->searchedFor = $name;
        $callback = function($name, $children) {
            if ($name == $this->searchedFor) {
                if (count($this->currentLine) < 2) {
                    throw new Exception($name . ' has no grand parent specified.');
                } else {
                    return $this->currentLine[count($this->currentLine) - 2];
                }
            }
        };

        return $this->runLoop($callback, $this->tree);
    }

    public function getOnlyChildren() {
        $this->list = array();
        $callback = function($name, $children) {

            if (count(array_keys($children)) == 1) {
                $children = array_keys($children);
                $this->list[] = $children[0];
            }
        };
        $this->runLoop($callback, $this->tree);
        return $this->list;
    }

    public function getChildfree() {
        $callback = function($name, $children) {
            if (count($children) == 0) {
                array_push($this->list, $name);
            }
            return false;
        };
        $this->runLoop($callback, $this->tree);
        return $this->list;
    }

    public function getMostProlific() {
        $this->searchedFor = 0;
        $callback = function($name, $children) {
            if (count($children) > $this->searchedFor) {
                $this->searchedFor = count($children);
                $this->list[0] = $name;
            }
        };
        $this->runLoop($callback, $this->tree);
        return $this->list[0];
    }

    public function drawFamilyTree() {}

    private function runLoop($callback, $lineage) {
        $this->currentGeneration++;
        foreach ($lineage as $name => $children) {
            $response = $callback($name, $children);
            $showme = print_r($this->currentLine, true);
            //echo $name . ' : ' . $this->currentGeneration . ' : ' . $showme . "\n";
            if ($response) {
                return $response;
            } elseif (count($children)) {
                $this->currentLine[$this->currentGeneration] = $name;
                $response = $this->runLoop($callback, $children);
                if ($response) {
                    return $response;
                } else {
                    unset($this->currentLine[$this->currentGeneration]);

                }
            }
        }
        $this->currentGeneration--;
        return false;
    }
}

$family = new FamilyTree();
var_dump($family->getMostProlific());