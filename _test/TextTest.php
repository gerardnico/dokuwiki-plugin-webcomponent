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


require_once(__DIR__ . '/../class/Text.php');

class TextTest extends \DokuWikiTest
{

    public function testName()
    {
        $count = Text::getWordCount("hallo world");
        $this->assertEquals(2,$count);

        /**
         * Group of character with minus or underscore are
         * accepted as word
         */
        $content = "==== bla ====\none two th-ree fo_ur";
        $count = Text::getWordCount($content);
        $this->assertEquals(5,$count);

    }

}
