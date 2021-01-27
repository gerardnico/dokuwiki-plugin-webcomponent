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


require_once(__DIR__ . '/../../combo/class/'.'Text.php');

class TextTest extends \DokuWikiTest
{

    public function testBase()
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

    public function testHtml()
    {
        /**
         * Test node and attribute are not taken into account
         */
        $content = "==== bla ====\n<note group name='value'>one two th-ree fo_ur</note>";
        $count = Text::getWordCount($content);
        $this->assertEquals(6,$count);

    }

    public function testIsWord()
    {

        $this->assertTrue(Text::isWord("bla"));
        $this->assertTrue(Text::isWord("bl-a"));
        $this->assertTrue(Text::isWord("bl_a"));
        $this->assertFalse(Text::isWord(""));
        $this->assertFalse(Text::isWord("bl_a>"));
        $this->assertFalse(Text::isWord("<bl_a>"));
        $this->assertFalse(Text::isWord("<bl_a"));
        $this->assertFalse(Text::isWord("a=b"));

    }


}
