<?php

declare(strict_types=1);

namespace drzippie\crop\haar;

/**
 * Haar Cascade XML to PHP Converter
 * 
 * Converts OpenCV Haar cascade XML files to PHP arrays
 * Adapted from HAARPHP (https://github.com/foo123/HAARPHP)
 * 
 * Usage: php haartophp.php --xml=cascade.xml
 */

class HaarToPHP
{
    public static function convert($xmlFile, $outputFile = null)
    {
        if (!file_exists($xmlFile)) {
            throw new Exception("XML file not found: $xmlFile");
        }
        
        $xml = simplexml_load_file($xmlFile);
        if (!$xml) {
            throw new Exception("Failed to parse XML file: $xmlFile");
        }
        
        $cascade = self::parseXML($xml);
        $phpCode = self::generatePHPCode($cascade);
        
        if ($outputFile) {
            file_put_contents($outputFile, $phpCode);
        } else {
            echo $phpCode;
        }
        
        return $cascade;
    }
    
    private static function parseXML($xml)
    {
        $cascade = array();
        
        // Find the haar cascade node
        $haarNode = null;
        foreach ($xml->children() as $child) {
            if (strpos($child->getName(), 'haarcascade') !== false) {
                $haarNode = $child;
                break;
            }
        }
        
        if (!$haarNode) {
            throw new Exception("No haarcascade node found in XML");
        }
        
        // Parse size
        if (isset($haarNode->size)) {
            $sizeStr = trim((string)$haarNode->size);
            $sizeValues = explode(' ', $sizeStr);
            $cascade['size'] = array(
                (int)$sizeValues[0],
                (int)$sizeValues[1]
            );
        }
        
        // Parse stages
        $cascade['stages'] = array();
        
        if (isset($haarNode->stages)) {
            foreach ($haarNode->stages->_ as $stage) {
                $stageData = array(
                    'threshold' => 0.0,
                    'features' => array()
                );
                
                // Look for stage_threshold
                if (isset($stage->stage_threshold)) {
                    $stageData['threshold'] = (float)$stage->stage_threshold;
                }
                
                if (isset($stage->trees)) {
                    foreach ($stage->trees->_ as $tree) {
                        if (isset($tree->_)) {
                            $feature = array(
                                'threshold' => (float)$tree->_->threshold,
                                'left_val' => (float)$tree->_->left_val,
                                'right_val' => (float)$tree->_->right_val,
                                'rectangles' => array()
                            );
                            
                            if (isset($tree->_->feature->rects)) {
                                foreach ($tree->_->feature->rects->_ as $rect) {
                                    $rectValues = explode(' ', trim((string)$rect));
                                    if (count($rectValues) >= 5) {
                                        $feature['rectangles'][] = array(
                                            (int)$rectValues[0], // x
                                            (int)$rectValues[1], // y
                                            (int)$rectValues[2], // width
                                            (int)$rectValues[3], // height
                                            (float)$rectValues[4]  // weight
                                        );
                                    }
                                }
                            }
                            
                            $stageData['features'][] = $feature;
                        }
                    }
                }
                
                $cascade['stages'][] = $stageData;
            }
        }
        
        return $cascade;
    }
    
    private static function generatePHPCode($cascade)
    {
        $php = "<?php\n\n";
        $php .= "// Haar Cascade Data\n";
        $php .= "// Generated from OpenCV XML cascade file\n\n";
        $php .= "return " . var_export($cascade, true) . ";\n";
        
        return $php;
    }
}

// CLI Usage
if (php_sapi_name() === 'cli') {
    $options = getopt('h', array('help', 'xml:'));
    
    if (isset($options['h']) || isset($options['help'])) {
        echo "Usage: php haartophp.php --xml=cascade.xml\n";
        echo "Converts OpenCV Haar cascade XML files to PHP arrays\n";
        echo "\nOptions:\n";
        echo "  --xml=FILE    Input XML cascade file\n";
        echo "  -h, --help    Show this help message\n";
        exit(0);
    }
    
    if (!isset($options['xml'])) {
        echo "Error: --xml parameter is required\n";
        echo "Use --help for usage information\n";
        exit(1);
    }
    
    try {
        HaarToPHP::convert($options['xml']);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}