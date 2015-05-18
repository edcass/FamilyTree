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
    private $currentSibling = array();
    private $searchedFor = '';
    private $list = array();
    private $treeImagePath = '';
    private $treeImageSource;
    private $currentPosition = array('x' => 20, 'y' => 20);
    private $parentText;

    /**
     * @param bool $jsonFilePath
     * @throws Exception
     */
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

    /**
     * @param $name
     * @return string
     */
    public function getGrandParent($name) {
        $this->currentLine = array();
        $this->searchedFor = $name;
        $callback = function($name, $children) {
            //print_r($this->currentLine);
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


    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return string
     */
    public function getMostProlific() {
        $this->searchedFor = 0;
        $callback = function($name, $children) {
            if (count($children) > $this->searchedFor) {
                $this->searchedFor = count($children);
                $this->list[0] = $name;
            }
        };
        $this->runLoop($callback, $this->tree, 0);
        return $this->list[0];
    }

    /**
     * @throws Exception
     * @return string
     * This will give you back the file path to an svg of the tree drawn out.
     */
    public function drawFamilyTree() {
        require_once('./lib/phpsvg-read-only/svglib/svglib.php');
        $filename = './images/' . uniqid() . '.svg'; // make sure you don't have file name collisions.

        $this->treeImageSource = SVGDocument::getInstance();


        $callback = function($name, $children, $previousSibling) {

            $x = $this->currentPosition['x'] + (($this->currentSibling[$this->currentGeneration] + $previousSibling) * 60);
            $y = $this->currentPosition['y'] + ($this->currentGeneration * 50);
            $text = SVGText::getInstance(
                $x,
                $y,
                null,
                $name,
                new SVGStyle( array('fill'=>'blue', 'stroke' =>'black' ))
            );
            $this->treeImageSource->addShape($text);

            $this->parentText[$name] = $text;

            $tree = new FamilyTree($this->jsonFilePath);
            $parent = $tree->getParent($name, $this->tree);
            echo $name . ': ' . $parent . "\n";
            if ($parent) {
                FamilyTree::drawLine($this->treeImageSource, $this->parentText[$name], $this->parentText[$parent]);
            }
        };

        $this->runLoop($callback, $this->tree, 0);
        $this->treeImageSource->saveXML($filename);
    }

    /**
     * @param $callback
     * @param $lineage
     * @param $previousSibling
     * @return bool
     * Getting all recursive here.
     */
    private function runLoop($callback, $lineage, $previousSibling) {
        $this->currentGeneration++;

        if (!isset($this->currentSibling[$this->currentGeneration])) {
            $this->currentSibling[$this->currentGeneration] = 0;
        }
        foreach ($lineage as $name => $children) {
            $this->currentSibling[$this->currentGeneration]++;
            //echo $name . ' : ' . $this->currentGeneration . ' : ' . $this->currentSibling[$this->currentGeneration] . " : $previousSibling" . "\n";
            $this->currentLine[$this->currentGeneration] = $name;
            $response = $callback($name, $children, $previousSibling);

            if ($response) {
                return $response;
            } else {

                $response = $this->runLoop($callback, $children, $this->currentSibling[$this->currentGeneration]);
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

    /**
     * @param SVGDocument $doc
     * @param SVGText $current
     * @param SVGText $parent
     */
    private static function drawLine(SVGDocument $doc, SVGText $current, SVGText $parent) {
        $x1 = $parent->getX();
        $y1 = $parent->getY();

        $x2 = $current->getX();
        $y2 = $current->getY();

        $style = new SVGStyle();
        $style->setFill( '#f2f2f2' );
        $style->setStroke( '#e1a100' );
        $style->setStrokeWidth( 2 );
        $line = SVGLine::getInstance($x1, $y1, $x2, $y2, null, $style);
        $doc->addShape($line);
    }

    /**
     * @param $name
     * @return string | boolean
     */
    private function getParent($name) {

        $this->currentLine = array();
        $this->searchedFor = $name;
        $callback = function($name, $children) {
            if ($name == $this->searchedFor) {
                if (count($this->currentLine) < 1) {
                    return false;
                } else {
                    return $this->currentLine[count($this->currentLine) - 1];
                }
            }
        };

        return $this->runLoop($callback, $this->tree, 0);
    }
}