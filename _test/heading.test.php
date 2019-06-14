<?php

require_once(__DIR__ . '/../webcomponent.php');

/**
 * Test the heading component plugin
 *
 * @group plugin_webcomponent
 * @group plugins
 */
class plugin_webcomponent_heading_test extends DokuWikiTest
{

    protected $pluginsEnabled = [webcomponent::PLUGIN_NAME];


    public function test_heading_character()
    {

        $componentName = syntax_plugin_webcomponent_heading::getHeadingCharacter();

        $this->assertEquals('#', $componentName);

    }

    public function test_h1()
    {

        $doku_text = '====== Heading 1 ======'.DOKU_LF."Content";
        $markdown_text = '# Heading 1'.DOKU_LF."Content";

        $info = array();

        $instructionsDoku = p_get_instructions($doku_text);
        $instructionsMark = p_get_instructions($markdown_text);
        $dokuXhtml = p_render('xhtml', $instructionsDoku, $info);
        $markXhtml = p_render('xhtml', $instructionsMark, $info);
        $this->assertEquals($dokuXhtml, $markXhtml);


    }

    public function test_h5()
    {

        $doku_text = '== Heading 5 =='.DOKU_LF."Content";
        $markdown_text = '##### Heading 5'.DOKU_LF."Content";

        $info = array();

        $instructionsDoku = p_get_instructions($doku_text);
        $instructionsMark = p_get_instructions($markdown_text);
        $dokuXhtml = p_render('xhtml', $instructionsDoku, $info);
        $markXhtml = p_render('xhtml', $instructionsMark, $info);
        $this->assertEquals($dokuXhtml, $markXhtml);


    }



}
