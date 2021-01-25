<?php
/**
 * Copyright (c) 2021. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */

namespace ComboStrap;


class Analytics
{



    const DATE_MODIFIED = 'date_modified';
    /**
     * Constant in Key or value
     */
    const HEADER_POSITION = 'header_id';
    const INTERNAL_BACKLINKS = "internal_backlinks";
    const WORDS = 'words';
    const INTERNAL_LINK_DISTANCE = 'internal_links_distance';
    const CHANGES = 'changes';
    const INTERNAL_LINKS_BROKEN = 'internal_links_broken';
    const TITLE = 'title';
    const INTERNAL_LINKS = 'internal_links';
    const EXTERNAL_MEDIAS = 'external_medias';
    const CHARS = 'chars';
    const INTERNAL_MEDIAS = 'internal_medias';
    const EXTERNAL_LINKS = 'external_links';
    const HEADERS = 'headers';
    const QUALITY = 'quality';
    const STATISTICS = "statistics";

    /**
     * The format returned by the renderer
     */
    const RENDERER_FORMAT = "analytics";
    const RENDERER_NAME = "combo_".self::RENDERER_FORMAT;

    /**
     * @param $pageId
     * @param bool $cache - if true, the data is returned from the cache
     * @return mixed
     * The p_render function was stolen from the {@link p_cached_output} function
     * used the in the switch of the {@link \dokuwiki\Action\Export::preProcess()} function
     */
    public static function getDataAsJson($pageId, $cache = false)
    {

        return json_decode(self::getDataAsString($pageId, $cache));

    }

    private static function getDataAsString($pageId, $cache = false)
    {

        global $ID;
        $oldId = $ID;
        $ID = $pageId;
        if (!$cache) {
            $file = wikiFN($pageId);
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $instructions = RenderUtility::getInstructions($content,false);
                return p_render(self::RENDERER_NAME, $instructions, $info);
            } else {
                return false;
            }
        } else {
            $result = p_cached_output(wikiFN($pageId, 0), self::RENDERER_NAME, $pageId);
        }
        $ID = $oldId;
        return $result;

    }

    public static function getDataAsArray($pageId, $cache = false)
    {

        return json_decode(self::getDataAsString($pageId, $cache),true);

    }

    public static function process($pageId)
    {
        self::getDataAsJson($pageId, false);
    }

}
