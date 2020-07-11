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


use Doku_Renderer_metadata;
use Doku_Renderer_xhtml;

/**
 * Class LinkUtility
 * @package ComboStrap
 *
 */
class LinkUtility
{

    /**
     * Link pattern
     * Found in {@link \dokuwiki\Parsing\ParserMode\Internallink}
     */
    const LINK_PATTERN = "\[\[.*?\]\](?!\])";

    /**
     * Type of link
     */
    const TYPE_INTERWIKI = 'interwiki';
    const TYPE_WINDOWS_SHARE = 'windowsShare';
    const TYPE_EXTERNAL = 'external';
    const TYPE_EMAIL = 'email';
    const TYPE_LOCAL = 'local';
    const TYPE_INTERNAL = 'internal';

    const ATTRIBUTE_ID = 'id';
    const ATTRIBUTE_TITLE = 'title';
    const ATTRIBUTE_IMAGE = 'image';
    const ATTRIBUTE_TYPE = 'type';


    /**
     * Parse the match of a syntax {@link DokuWiki_Syntax_Plugin} handle function
     * @param $match
     * @return string[] - an array with the attributes constant `ATTRIBUTE_xxxx` as key
     *
     * Code adapted from  {@link Doku_Handler}->internallink($match,$state,$pos)}
     */
    public static function getAttributes($match)
    {

        // Strip the opening and closing markup
        $link = preg_replace(array('/^\[\[/', '/\]\]$/u'), '', $match);

        // Split title from URL
        $link = explode('|', $link, 2);

        // Id
        $attributes[self::ATTRIBUTE_ID] = trim($link[0]);

        // Text or image
        if (!isset($link[1])) {
            $attributes[self::ATTRIBUTE_TITLE] = null;
        } else {
            // An image in the title
            if (preg_match('/^\{\{[^\}]+\}\}$/', $link[1])) {
                // If the title is an image, convert it to an array containing the image details
                $attributes[self::ATTRIBUTE_IMAGE] = Doku_Handler_Parse_Media($link[1]);
            } else {
                $attributes[self::ATTRIBUTE_TITLE] = $link[1];
            }
        }

        // Type
        $attributes[self::ATTRIBUTE_TYPE] = self::getType($attributes[self::ATTRIBUTE_ID]);
        return $attributes;

    }

    /**
     * @param Doku_Renderer_xhtml - $renderer
     * @param array $attributes
     * @return mixed
     */
    public static function renderHTML($renderer, array $attributes)
    {
        $id = $attributes[self::ATTRIBUTE_ID];
        $title = $attributes[self::ATTRIBUTE_TITLE];

        // Always return the string
        $returnOnly = true;

        // The HTML created by DokuWiki
        switch (self::getType($id)) {
            case self::TYPE_INTERWIKI:
                // Interwiki
                $interWiki = explode('>', $id, 2);
                $wikiName = strtolower($interWiki[0]);
                $wikiUri = $interWiki[1];
                $html = $renderer->interwikilink($id, $title, $wikiName, $wikiUri, $returnOnly);
                break;
            case self::TYPE_WINDOWS_SHARE:
                $html = $renderer->windowssharelink($id, $title);
                break;
            case self::TYPE_EXTERNAL:
                $html = $renderer->externallink($id, $title, $returnOnly);
                break;
            case self::TYPE_EMAIL:
                // E-Mail (pattern above is defined in inc/mail.php)
                $html = $renderer->emaillink($id, $title, $returnOnly);
                break;
            case self::TYPE_LOCAL:
                $html = $renderer->locallink(substr($id, 1), $title, $returnOnly);
                break;
            default:
                $urlQuery = null;
                $html = $renderer->internallink($id, $title, $urlQuery, $returnOnly);
                break;
        }
        return $html;

    }

    /**
     * Keep track of the backlinks ie meta['relation']['references']
     * @param Doku_Renderer_metadata $metaDataRenderer
     * @param array $attributes
     */
    public
    static function handleMetadata($metaDataRenderer, array $attributes)
    {

        if ($attributes[self::ATTRIBUTE_TYPE] == self::TYPE_INTERNAL) {
            $metaDataRenderer->internallink($attributes[self::ATTRIBUTE_ID]);
        }
    }

    /**
     * Return the type of link from an ID
     *
     * @param $id
     * @return string a `TYPE_xxx` constant
     * Code adapted from {@link Doku_Handler}->internallink($match,$state,$pos)
     */
    public
    static function getType($id)
    {
        /**
         * Email validation pattern
         */
        $emailRfc2822 = "0-9a-zA-Z!#$%&'*+/=?^_`{|}~-";
        $emailPattern = '[' . $emailRfc2822 . ']+(?:\.[' . $emailRfc2822 . ']+)*@(?i:[0-9a-z][0-9a-z-]*\.)+(?i:[a-z]{2,63})';

        if (link_isinterwiki($id)) {
            return self::TYPE_INTERWIKI;
        } elseif (preg_match('/^\\\\\\\\[^\\\\]+?\\\\/u', $id)) {
            return self::TYPE_WINDOWS_SHARE;
        } elseif (preg_match('#^([a-z0-9\-\.+]+?)://#i', $id)) {
            return self::TYPE_EXTERNAL;
        } elseif (preg_match('<' . $emailPattern . '>', $id)) {
            return self::TYPE_EMAIL;
        } elseif (preg_match('!^#.+!', $id)) {
            return self::TYPE_LOCAL;
        } else {
            return self::TYPE_INTERNAL;
        }

    }

    /**
     * Inherit the color of their parent and not from Dokuwiki
     * @param $htmlLink
     * @return bool|false|string
     */
    public static function inheritColorFromParent($htmlLink)
    {
        /**
         * The extra style for the link
         */
        $styleValue = ";background-color:inherit;border-color:inherit;color:inherit";
        return HtmlUtility::addAttributeValue($htmlLink,"style", $styleValue);

    }

}
