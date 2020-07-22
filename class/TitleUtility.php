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


class TitleUtility
{

    /**
     * Header pattern that we expect in a card (teaser) ie  ==== Hello =====
     * Found in {@link \dokuwiki\Parsing\ParserMode\Header}
     */
    const HEADING_PATTERN = '[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)';

    const TITLE = 'title';
    const LEVEL = 'level';

    public static function parseHeading($match)
    {
        $title = trim($match);
        $level = 7 - strspn($title, '=');
        if ($level < 1) $level = 1;
        $title = trim($title, '=');
        $title = trim($title);
        $parameters[self::TITLE] = $title;
        $parameters[self::LEVEL] = $level;
        return $parameters;
    }

}
