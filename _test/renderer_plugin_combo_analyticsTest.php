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


use ComboStrap\PluginUtility;
use ComboStrap\Analytics;


require_once(__DIR__ . '/../../combo/class/'.'PluginUtility.php');
require_once(__DIR__ . '/TestUtility.php');
require_once(__DIR__ . '/../../combo/class/'.'Analytics.php');

class renderer_plugin_combo_analyticsTest extends DokuWikiTest
{

    public function setUp()
    {
        $this->pluginsEnabled[] = PluginUtility::PLUGIN_BASE_NAME;
        $this->pluginsEnabled[] = 'sqlite';
        parent::setUp();
    }

    public function testLowLevel()
    {
        // Save a test page
        $pageId = "stats";
        TestUtility::addPage($pageId, "bla", 'Page creation');
        $json = Analytics::getDataAsJson($pageId);
        $this->assertEquals($pageId, $json->id);
        $this->assertEquals(true, $json->quality->low);
        $this->assertEquals(1, $json->statistics->words);

    }

    public function testBase()
    {
        // Save a test page
        $pageId = "stats";
        $content = "==== bla ====\n".
            "one two three four"
        ;
        TestUtility::addPage($pageId, $content, 'Page creation');
        $json = Analytics::getDataAsJson($pageId);
        $this->assertEquals($pageId, $json->id);
        $this->assertEquals(true, $json->quality->low);
        $this->assertEquals(5, $json->statistics->words);

        // Run it twice to test the upsert
        Analytics::getDataAsJson($pageId);

    }

    public function testWordCount()
    {
        // Save a test page
        $pageId = "realWordCount";
        $content = <<<EOD
====== ComboStrap UI - Table ======


===== About =====
''Table'' is a component that shows data in a list of record.




''ComboStrap'' renders the [[doku>wiki:syntax#tables|Dokuwiki table]]
  * as a [[https://getbootstrap.com/docs/4.0/content/tables/|Bootstrap table]]
  * and make it responsives

===== Articles Related =====
{{backlinks>.}}





===== Example =====

<webcode name="Tables" >
<code dw>
^ Heading 1      ^ Heading 2       ^ Heading 3          ^
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
| Row 2 Col 1    | some colspan (note the double pipe) ||
| Row 3 Col 1    | Row 3 Col 2     | Row 3 Col 3        |
</code>
</webcode>

===== Configuration =====
==== Enable renderer ====
The table rendering is a feature of the [[renderer|renderer]] and should be then selected. See [[renderer#configuration|renderer configuration]].
EOD;
        TestUtility::addPage($pageId, $content, 'Page creation');
        $json = Analytics::getDataAsJson($pageId);
        $this->assertEquals($pageId, $json->id);
        $this->assertEquals(true, $json->quality->low);
        $this->assertEquals(96, $json->statistics->words);

    }

    /**
     * Just a utility function to call the export class
     * The export will exit, this should be only used
     * to debug and before committing the call to the export should be commented
     */
//    public function testExport()
//    {
//        // Save a test page
//        $pageId = "stats";
//        $content = "==== bla ====\n".
//            "one two three four"
//        ;
//        TestUtility::addPage($pageId, $content, 'Page creation');
//        $request = new TestRequest();
//        $request->get(array('id' => $pageId,'do'=>'export_combo_analysis'), '/doku.php');
//
//    }
}
