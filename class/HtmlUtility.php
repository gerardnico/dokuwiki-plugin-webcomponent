<?php
/**
 * Copyright (c) 2020. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */

namespace ComboStrap;


use DOMDocument;
use http\Exception\RuntimeException;
use SimpleXMLElement;
require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/XmlUtility.php');

/**
 * Class HtmlUtility
 * @package ComboStrap
 * On HTML as string, if you want to work on HTML as XML, see the {@link XmlUtility} class
 */
class HtmlUtility
{

    /**
     * @param $html - An Html
     * @param $attributeName
     * @param $attributeValue
     * @return bool|false|string
     */
    public static function addAttributeValue($html, $attributeName, $attributeValue)
    {
        try {
            /** @noinspection PhpComposerExtensionStubsInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            $domElement = new SimpleXMLElement ($html);
        } catch (\Exception $e) {
            LogUtility::msg("The HTML ($html) is not a valid HTML element. The error returned is $e", LogUtility::LVL_MSG_ERROR);
            return false;
        }
        XmlUtility::addAttributeValue($attributeName, $attributeValue, $domElement);

        return XmlUtility::asHtml($domElement);
    }

    /**
     * @param $html - the html of an element
     * @param $classValue - the class to delete
     * @return bool|false|string
     */
    public static function deleteClassValue($html, $classValue)
    {
        try {
            /** @noinspection PhpComposerExtensionStubsInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            $domElement = new SimpleXMLElement ($html);
        } catch (\Exception $e) {
            LogUtility::msg("The HTML ($html) is not a valid HTML element. The error returned is $e", LogUtility::LVL_MSG_ERROR);
            return false;
        }
        XmlUtility::deleteClass($classValue, $domElement);

        return XmlUtility::asHtml($domElement);

    }

    /**
     * Return a formatted HTML that does take into account the {@link DOKU_LF}
     * @param $text
     * @return mixed
     */
    public static function normalize($text)
    {
        $text = str_replace(DOKU_LF, "", $text);
        return self::format($text);
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
        /**
         * The @ is to suppress the error because of HTML5 tag such as footer
         * https://stackoverflow.com/questions/6090667/php-domdocument-errors-warnings-on-html5-tags
         */
        @$doc->loadHTML($text);
        $doc->normalize();
        $doc->formatOutput = true;
        $domNode = $doc->getElementsByTagName("body")->item(0)->childNodes->item(0);
        // Type doc can also be reach with $domNode->ownerDocument
        return $doc->saveXML($domNode);


    }
}
