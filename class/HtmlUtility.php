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
            PluginUtility::msg("The HTML ($html) is not a valid HTML element. The error returned is $e", PluginUtility::LVL_MSG_ERROR);
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
            PluginUtility::msg("The HTML ($html) is not a valid HTML element. The error returned is $e", PluginUtility::LVL_MSG_ERROR);
            return false;
        }
        XmlUtility::deleteClass($classValue, $domElement);

        return XmlUtility::asHtml($domElement);

    }
}
