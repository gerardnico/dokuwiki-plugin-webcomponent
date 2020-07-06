<?php


namespace ComboStrap;


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
     * @param SimpleXMLElement $mediaSvgXml
     */
    public static function addAttributeValue($attName, $attValue, SimpleXMLElement $mediaSvgXml)
    {
        $actualWidthValue = (string)$mediaSvgXml[$attName];
        if ($actualWidthValue != "") {
            $mediaSvgXml[$attName] .= " $attValue";
        } else {
            $mediaSvgXml->addAttribute($attName, $attValue);
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
        return $domXml->ownerDocument->saveXML($domXml->ownerDocument->documentElement);
    }

}
