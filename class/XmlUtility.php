<?php


namespace ComboStrap;


use DOMDocument;
use SimpleXMLElement;

/**
 * Class XmlUtility
 * @package ComboStrap
 * SimpleXML Utility
 *
 *
 */
class XmlUtility
{
    const OPEN = "open";
    const CLOSED = "closed";
    const NORMAL = "normal";


    /**
     * @param $attName
     * @param $attValue
     * @param SimpleXMLElement $mediaSvgXml
     */
    public static function setAttribute($attName, $attValue, SimpleXMLElement $mediaSvgXml)
    {
        $actualWidthValue = (string)$mediaSvgXml[$attName];
        if ($actualWidthValue != "") {
            $mediaSvgXml[$attName] = $attValue;
        } else {
            $mediaSvgXml->addAttribute($attName, $attValue);
        }
    }

    /**
     * Delete the class value from the class attributes
     * @param $classValue
     * @param SimpleXMLElement $xml
     */
    public static function deleteClass($classValue, SimpleXMLElement $xml)
    {
        $class = (string)$xml["class"];
        if ($class != "") {
            $classValues = explode(" ", $class);
            if (($key = array_search($classValue, $classValues)) !== false) {
                unset($classValues[$key]);
            }
            $xml["class"] = implode(" ", $classValues);
        }
    }

    /**
     *
     * Add a value to an attribute value
     * Example
     * <a class="actual">
     *
     * if you add "new"
     * <a class="actual new">
     *
     * @param $attName
     * @param $attValue
     * @param SimpleXMLElement $xml
     */
    public static function addAttributeValue($attName, $attValue, SimpleXMLElement $xml)
    {
        $actualWidthValue = (string)$xml[$attName];
        if ($actualWidthValue != "") {
            $xml[$attName] .= " $attValue";
        } else {
            $xml->addAttribute($attName, $attValue);
        }
    }

    /**
     * Get a Simple XMl Element and returns it without the XML header (ie as HTML node)
     * @param SimpleXMLElement $linkDom
     * @return false|string
     */
    public static function asHtml(SimpleXMLElement $linkDom)
    {
        $domXml = dom_import_simplexml($linkDom);
        /**
         * ownerDocument returned the DOMElement
         */
        return $domXml->ownerDocument->saveXML($domXml->ownerDocument->documentElement);
    }

    /**
     * Check of the text is a valid XML
     * @param $text
     * @return bool
     */
    public static function isXml($text)
    {
        $valid = true;
        try {
            /** @noinspection PhpComposerExtensionStubsInspection */
            new SimpleXMLElement($text);
        } catch (\Exception $e) {
            $valid = false;
        }
        return $valid;
    }

    /**
     * Return a formatted HTML
     * @param $text
     * @return mixed
     * DOMDocument supports formatted XML while SimpleXMLElement does not.
     * @throws \Exception if empty
     */
    public static function format($text)
    {
        if (empty($text)){
            throw new \Exception("The text should not be empty");
        }
        $doc = new DOMDocument();
        $doc->loadXML($text);
        $doc->normalize();
        $doc->formatOutput = true;
        // Type doc can also be reach with $domNode->ownerDocument
        return $doc->saveXML();


    }

    /**
     * @param $text
     * @return mixed
     * @throws \Exception
     */
    public static function normalize($text)
    {
        if (empty($text)){
            throw new \Exception("The text should not be empty");
        }
        $doc = new DOMDocument();
        $doc->loadXML($text);
        $doc->normalize();
        $doc->formatOutput = true;
        // Type doc can also be reach with $domNode->ownerDocument
        return $doc->saveXML($doc->documentElement).DOKU_LF;
    }


}
