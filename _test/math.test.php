<?php

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');

/**
 * Test the component plugin
 *
 * @group plugin_combo
 * @group plugins
 */
class plugin_combo_math_test extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::$PLUGIN_BASE_NAME;
        $this->pluginsEnabled[] = 'sqlite';
        parent::setUp();
    }

    public static function getTextFromNode($Node, $Text = "") {
        if ($Node->tagName == null)
            return $Text.$Node->textContent;

        $Node = $Node->firstChild;
        if ($Node != null)
            $Text = self::getTextFromNode($Node, $Text);

        while($Node->nextSibling != null) {
            $Text = self::getTextFromNode($Node->nextSibling, $Text);
            $Node = $Node->nextSibling;
        }
        return $Text;
    }




    public function test_component_name()
    {

        $componentName = syntax_plugin_combo_math::getComponentName();

        $this->assertEquals('math', $componentName);

    }

    /**
     * Do we protect the math syntax fully
     */
    public function test_syntax_base()
    {

        $elements = syntax_plugin_combo_math::getTags();
        // The element is protecting, therefore a dokuwiki link should not be converted to a <a> Html element
        $content = '[[link]]';
        $info = array();
        foreach ($elements as $element) {
            $doku_text = '<' . $element . '>' . $content . '</' . $element . '>';
            $instructions = p_get_instructions($doku_text);
            $xhtml = p_render('xhtml', $instructions, $info);
            $expected = '&lt;'.$element.'&gt;[[link]]&lt;/'.$element.'&gt;';
            $position = strpos($xhtml,$expected);
            $this->assertNotFalse($position,"The math expression should be present for the tag ".$element);
        }

    }


    /**
     * Test if the MathJs library were not added
     */
    public function test_library_not_added()
    {

        // IO_WIKIPAGE_WRITE for removing their metadata...
        // https://www.dokuwiki.org/devel:event:io_wikipage_write

        global $conf;
        $conf['template'] = 'strap';

        $pageId = PluginUtility::getNameSpace() . 'test_library_base';

        // For the first run, there is no metadata ?
        $meta = p_get_metadata($pageId);
        $this->assertEquals(0, sizeof($meta),"No metadata anymore");

        // saving the page
        $doku_text = 'whatever without math element';
        saveWikiText($pageId, $doku_text, 'test_indexer test library base');
        idx_addPage($pageId);

        // We got meta
        $meta = p_get_metadata($pageId);
        $this->assertEquals(true, sizeof($meta)>0,"Metadata present");

        // The request shows that there is no Mathjax library
        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $pageId));
        $divId = syntax_plugin_combo_math::MATH_JAX_DIV_ID;
        $mathJaxDivCount = $testResponse->queryHTML('#' . $divId)->count();
        $this->assertEquals(0, $mathJaxDivCount);


    }

    /**
     * Test if the MathJs library were added
     */
    public function test_library_added()
    {

        $pageId = PluginUtility::getNameSpace() . 'test_library_added';

        // With math
        $doku_text = '<math>x^2</math><math>x^2</math>';
        saveWikiText($pageId, $doku_text, 'test_indexer test library added');
        idx_addPage($pageId);
        $testRequest = new TestRequest();
        $testRequest->setServer('REQUEST_TIME',time());
        $testResponse = $testRequest->get(array('id' => $pageId));
        $mathJaxDivCount = $testResponse->queryHTML('#' . syntax_plugin_combo_math::MATH_JAX_DIV_ID)->count();
        $this->assertEquals(1, $mathJaxDivCount,"The library was added only once");

        // Without math
        $doku_text = 'without math';
        saveWikiText($pageId, $doku_text, 'test_indexer test library added');
        idx_addPage($pageId);
        $testRequest = new TestRequest();
        $testRequest->setServer('REQUEST_TIME',time());
        $testResponse = $testRequest->get(array('id' => $pageId));
        $mathJaxDivCount = $testResponse->queryHTML('#' . syntax_plugin_combo_math::MATH_JAX_DIV_ID)->count();
        $this->assertEquals(0, $mathJaxDivCount,"The library was not added ");

        // With math again
        $doku_text = '<math>x^2</math><math>x^2</math>';
        saveWikiText($pageId, $doku_text, 'test_indexer test library added');
        idx_addPage($pageId);
        $testRequest = new TestRequest();
        sleep ( 1); // To be sure to have not the same timestamp
        $testRequest->setServer('REQUEST_TIME',time());
        $testResponse = $testRequest->get(array('id' => $pageId));
        $mathJaxDivCount = $testResponse->queryHTML('#' . syntax_plugin_combo_math::MATH_JAX_DIV_ID)->count();
        $this->assertEquals(1, $mathJaxDivCount,"The library was added only once");

    }




}
