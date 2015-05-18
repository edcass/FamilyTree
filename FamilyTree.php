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
    private $currentLine = array();
    private $currentGeneration = 0;
    private $currentSibling = 0;
    private $previousSibling = 0;
    private $searchedFor = '';
    private $list = array();
    private $treeImagePath = '';
    private $treeImageSource;
    private $currentPosition = array('x' => 20, 'y' => 20);

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

        return $this->runLoop($callback, $this->tree, 0);
    }

    public function getOnlyChildren() {
        $this->list = array();
        $callback = function($name, $children) {

            if (count(array_keys($children)) == 1) {
                $children = array_keys($children);
                $this->list[] = $children[0];
            }
        };
        $this->runLoop($callback, $this->tree, 0);
        return $this->list;
    }

    public function getChildfree() {
        $callback = function($name, $children) {
            if (count($children) == 0) {
                array_push($this->list, $name);
            }
            return false;
        };
        $this->runLoop($callback, $this->tree, 0);
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

    public function drawFamilyTree() {
        require_once('./lib/phpsvg-read-only/svglib/svglib.php');
        $filename = './images/' . uniqid() . '.svg'; // make sure you don't have file name collisions.
        $filename = './images/test.svg';
        //unlink($filename);
        $this->treeImageSource = SVGDocument::getInstance();


        $callback = function($name, $children, $previousSibling) {

            $x = $this->currentPosition['x'] + (($this->currentSibling + $previousSibling) * 50);
            $y = $this->currentPosition['y'] + ($this->currentGeneration * 50);
            $text = SVGText::getInstance(
                $x,
                $y,
                null,
                $name,
                new SVGStyle( array('fill'=>'blue', 'stroke' =>'black' ))
            );
            $this->treeImageSource->addShape($text);

        };

        $this->runLoop($callback, $this->tree, 0);
        $this->treeImageSource->saveXML($filename);
    }

    private function runLoop($callback, $lineage, $previousSibling) {
        $this->currentGeneration++;

        $this->currentSibling = 0;
        foreach ($lineage as $name => $children) {
            $this->currentSibling++;
            echo $name . ' : ' . $this->currentGeneration . ' : ' . $this->currentSibling . " : $previousSibling" . "\n";
            $response = $callback($name, $children, $this->currentSibling);
            if ($response) {
                return $response;
            } elseif (count($children)) {
                $this->currentLine[$this->currentGeneration] = $name;
                $response = $this->runLoop($callback, $children, $this->currentSibling);
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
var_dump($family->drawFamilyTree());


/**
 * $this->treeImageSource->addShape($text);
$this->treeImageSource->addShape($line);$ids[$name] = uniqid();
$text = SVGText::getInstance( 20, 20, $ids['blah'],'Bob', new SVGStyle( array('fill'=>'blue', 'stroke' =>'black' )));
$style = new SVGStyle(); #create a style object
#set fill and stroke
$style->setFill( '#f2f2f2' );
$style->setStroke( '#e1a100' );
$style->setStrokeWidth( 2 );
$line = SVGLine::getInstance(50, 50, 100, 100, null, $style);
 */