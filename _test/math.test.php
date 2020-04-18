<?php

require_once(__DIR__ . '/../webcomponent.php');

/**
 * Test the component plugin
 *
 * @group plugin_webcomponent
 * @group plugins
 */
class plugin_webcomponent_math_test extends DokuWikiTest
{

    public static function getTextFromNode($Node, $Text = "") {
        if ($Node->tagName == null)
            return $Text.$Node->textContent;

        $Node = $Node->firstChild;
        if ($Node != null)
            $Text = getTextFromNode($Node, $Text);

        while($Node->nextSibling != null) {
            $Text = getTextFromNode($Node->nextSibling, $Text);
            $Node = $Node->nextSibling;
        }
        return $Text;
    }

    protected $pluginsEnabled = [webcomponent::PLUGIN_NAME];


    public function test_component_name()
    {

        $componentName = syntax_plugin_webcomponent_math::getComponentName();

        $this->assertEquals('math', $componentName);

    }

    /**
     * Do we protect the math syntax fully
     */
    public function test_syntax_base()
    {

        $elements = syntax_plugin_webcomponent_math::getElements();
        // The element is protecting, therefore a dokuwiki link should not be converted to a <a> Html element
        $content = '[[link]]';
        $info = array();
        foreach ($elements as $element) {
            $doku_text = '<' . $element . '>' . $content . '</' . $element . '>';
            $instructions = p_get_instructions($doku_text);
            $xhtml = p_render('xhtml', $instructions, $info);
            $expected = DOKU_LF.
                '<p>'.DOKU_LF.
                '&lt;'.$element.'&gt;[[link]]&lt;/'.$element.'&gt;'.DOKU_LF.
                '</p>'.DOKU_LF;
            $this->assertEquals($expected, $xhtml);
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
        $conf['template'] = 'bootie';

        $pageId = webcomponent::getNameSpace() . 'test_library_base';

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
        $divId = webcomponent::PLUGIN_NAME . '_' . syntax_plugin_webcomponent_math::getComponentName();
        $mathJaxDiv = $testResponse->queryHTML('#' . $divId)->text();
        // The comments are not returned
        // therefore we don't see "<!--No Math expression on the page found-->"
        $expected = "\n\n";
        $this->assertEquals($expected, $mathJaxDiv);


    }

    /**
     * Test if the MathJs library were added
     */
    public function test_library_added()
    {

        global $conf;
        $conf['template'] = 'bootie';

        $pageId = webcomponent::getNameSpace() . 'test_library_added';

        // For the first run, there is no metadata ?
        $meta = p_get_metadata($pageId);
        $this->assertEquals(0, sizeof($meta),"No metadata anymore");

        // Create a page with a math expression
        $doku_text = '<math>x^2</math>';
        saveWikiText($pageId, $doku_text, 'test_indexer test library added');
        idx_addPage($pageId);

        // We got meta
        $meta = p_get_metadata($pageId);
        $this->assertEquals(true, $meta[syntax_plugin_webcomponent_math::MATH_EXPRESSION],"Metadata present");

        // Request, MathJax should be in the page
        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $pageId));
        $divId = webcomponent::PLUGIN_NAME . '_' . syntax_plugin_webcomponent_math::getComponentName();
        $mathJaxDiv = $testResponse->queryHTML('#' . $divId)->text();
        $expected = strpos($mathJaxDiv,'MathJax.Hub.Config');
        $this->assertEquals(true, $expected > 0, "Mathjax library added");

        // Does not work, I don't know where I can delete meta
        // Create the same page without math
        $doku_text = 'without math';
        saveWikiText($pageId, $doku_text, 'test_indexer test library added');
        idx_addPage($pageId);

        // We got no meta anymore
        $meta = p_get_metadata($pageId);
        print_r($meta);
        $this->assertEquals(false, $meta[syntax_plugin_webcomponent_math::MATH_EXPRESSION],"Metadata no more present");

        // No MathJax Anymore
        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $pageId));
        $divId = webcomponent::PLUGIN_NAME . '_' . syntax_plugin_webcomponent_math::getComponentName();
        $mathJaxDiv = $testResponse->queryHTML('#' . $divId)->text();
        $expected = strpos($mathJaxDiv, 'MathJax.Hub.Config');
        $this->assertEquals(0, $expected, "No library anymore");


    }




}
