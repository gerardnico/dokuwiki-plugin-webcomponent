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


class Text
{

    /**
     * @param $text
     * @return int the number of words
     */
    public static function getWordCount($text)
    {
        // \w shorthand word notation
        // \- allows also the minus -
        $wordSeparator = '/[^\w\-]/u';
        return count(array_filter(preg_split($wordSeparator, $text)));
    }

}
