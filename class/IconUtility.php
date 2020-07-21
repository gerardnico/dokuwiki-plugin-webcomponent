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

require_once(__DIR__."/../class/XmlUtility.php");
require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/ConfUtility.php');

/**
 * Class IconUtility
 * @package ComboStrap
 * @see https://combostrap.com/icon
 *
 * TODO: Add Feather ? https://github.com/feathericons/feather/tree/master/icons
 * TODO: Add Bootstrap ? https://icons.getbootstrap.com/
 * TODO: Add material ? https://material.io/resources/icons
 *
 */
class IconUtility
{
    const CONF_ICONS_MEDIA_NAMESPACE = "icons_namespace";
    const CONF_ICONS_MEDIA_NAMESPACE_DEFAULT = ":combostrap:icons";
    const NAME = "icon";

    /**
     * A short cut function used also to retrieve an icon internally (ie to style our own message)
     * @param $mediaFile
     * @param $attributes
     * @return bool|mixed
     */
    static public function renderFileIcon($mediaFile, $attributes = array()){

        try {
            /** @noinspection PhpComposerExtensionStubsInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            $mediaSvgXml = simplexml_load_file($mediaFile);
        } catch (\Exception $e) {
            /**
             * Don't use {@link \ComboStrap\LogUtility::msg()}
             * or you will get a recursion
             * because the URL has an icon
             */
            $msg = "The icon file ($mediaFile) could not be loaded as a XML SVG. The error returned is $e";
            LogUtility::log2FrontEnd($msg,LogUtility::LVL_MSG_ERROR,self::NAME, false);
            LogUtility::log2file($msg);
            if (defined('DOKU_UNITTEST')) {
                throw new \RuntimeException($msg);
            } else {
                return false;
            }
        }

        // Set the name attribute for test selection
        $mediaSvgXml->addAttribute('data-name', $attributes["name"]);
        unset($attributes["name"]);


        // Width
        $widthName = "width";
        $widthValue = "24px";
        if (array_key_exists($widthName, $attributes)) {
            $widthValue = $attributes[$widthName];
            unset($attributes[$widthName]);
        }
        XmlUtility::setAttribute($widthName, $widthValue, $mediaSvgXml);

        // Height
        $heightName = "height";
        $heightValue = "24px";
        if (array_key_exists($heightName, $attributes)) {
            $heightValue = $attributes[$heightName];
            unset($attributes[$heightName]);
        }
        XmlUtility::setAttribute($heightName, $heightValue, $mediaSvgXml);

        // Add fill="currentColor"
        $pathXml = $mediaSvgXml->{'path'};
        XmlUtility::setAttribute("fill", "currentColor", $pathXml);
        // for line item such as feather (https://github.com/feathericons/feather#2-use)
        // fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"

        // Process the style
        PluginUtility::processStyle($attributes);

        foreach ($attributes as $name => $value) {
            $mediaSvgXml->addAttribute($name, $value);
        }
        return XmlUtility::asHtml($mediaSvgXml);

    }

    /**
     * The function used to render an icon
     * @param $attributes -  the icon attributes
     * @return bool|mixed - false if any error or the HTML
     */
    static public function renderIconByAttributes($attributes){

        $name = "name";
        if (!array_key_exists($name, $attributes)) {
            LogUtility::msg("The attributes should have a name. It's mandatory for an icon.", LogUtility::LVL_MSG_ERROR, self::NAME);
            return false;
        }

        $iconName = $attributes[$name];


        // If the name have an extension, it's a file
        // Otherwise, it's an icon from the library
        $path = pathinfo($iconName);
        if ($path['extension']!=""){
            // loop through candidates until a match was found:
            $mediaFile = mediaFN($iconName);
            // May be an icon from the templates
            if (!file_exists($mediaFile)){
                $mediaTplFile  = tpl_incdir().'images/'.$iconName;
                if (!file_exists($mediaTplFile)){
                    // Trying to see if it's not in the template images directory
                    $message = "\n The media file could not be found in the media or template library. If you want an icon from the material design icon library, indicate a name without extension.";
                    $message .= "\n Media File Library tested: $mediaFile";
                    $message .= "\n Media Template Library tested: $mediaTplFile";
                    LogUtility::msg($message, LogUtility::LVL_MSG_ERROR,self::NAME);
                    return false;
                } else {
                    $mediaFile = $mediaTplFile;
                }
            }

        } else {

            // It may be a icon name from material design
            $iconNameSpace = ConfUtility::getConf(self::CONF_ICONS_MEDIA_NAMESPACE);
            $mediaId = $iconNameSpace . ":" . $iconName . ".svg";
            $mediaFile = mediaFN($mediaId);
            if (!file_exists($mediaFile)) {

                // The icon was may be not downloaded ?

                // Create the target directory if it does not exist
                $pathinfo = pathinfo($mediaFile);
                $iconDir = $pathinfo['dirname'];
                if (!file_exists($iconDir)) {
                    $return = mkdir($iconDir, $mode = 0770, $recursive = true);
                    if ($return == false) {
                        LogUtility::msg("The icon directory ($iconDir) could not be created.", LogUtility::LVL_MSG_ERROR,self::NAME);
                        return false;
                    }
                }

                // First try on github
                $gitUrl = "https://raw.githubusercontent.com/Templarian/MaterialDesign/master/svg/$iconName.svg";
                $return = file_put_contents($mediaFile, fopen($gitUrl, 'r'));
                if ($return != false) {

                    LogUtility::msg("The material design icon ($attributes[$name]) was downloaded to ($mediaId)", LogUtility::LVL_MSG_INFO,self::NAME);

                } else {

                    LogUtility::msg("The file ($gitUrl) could not be downloaded from ($mediaFile)", LogUtility::LVL_MSG_INFO,self::NAME);

                    // Try the official API
                    // Read the icon meta of
                    // Meta Json file got all icons
                    //
                    //   * Available at: https://raw.githubusercontent.com/Templarian/MaterialDesign/master/meta.json
                    //   * See doc: https://github.com/Templarian/MaterialDesign-Site/blob/master/src/content/api.md)
                    $arrayFormat = true;
                    $iconMetaJson = json_decode(file_get_contents(__DIR__ . '/icon-meta.json'), $arrayFormat);
                    $iconId = null;
                    foreach ($iconMetaJson as $key => $value) {
                        if ($value['name'] == $iconName) {
                            $iconId = $value['id'];
                            break;
                        }
                    }
                    if ($iconId != null) {


                        // Download
                        // Call to the API
                        // https://dev.materialdesignicons.com/contribute/site/api
                        $downloadUrl = "https://materialdesignicons.com/api/download/icon/svg/$iconId";
                        $return = file_put_contents($mediaFile, fopen($downloadUrl, 'r'));
                        if ($return == false) {
                            LogUtility::msg("The file ($downloadUrl) could not be downloaded to ($mediaFile)", LogUtility::LVL_MSG_ERROR,self::NAME);
                            return false;
                        } else {
                            LogUtility::msg("The material design icon ($attributes[$name]) was downloaded to ($mediaId)", LogUtility::LVL_MSG_INFO,self::NAME);
                        }

                    }

                }


            }

        }
        return self::renderFileIcon($mediaFile,$attributes);


    }


}
