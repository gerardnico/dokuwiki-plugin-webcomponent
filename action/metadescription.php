<?php

use ComboStrap\PluginUtility;
use ComboStrap\TestUtility;

/**
 * Take the metadata description
 *
 *
 */

require_once(__DIR__ . '/../class/PluginUtility.php');
require_once(__DIR__ . '/../class/TestUtility.php');


class action_plugin_combo_metadescription extends DokuWiki_Action_Plugin
{

    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'description_modification', array());
    }

    /**
     * Add a meta-data description
     */
    function description_modification(&$event, $param)
    {

        // if (empty($event->data) || empty($event->data['meta'])) return;

        global $ID;

        /**
         * Description
         * https://www.dokuwiki.org/devel:metadata
         */
        if (defined('DOKU_UNITTEST') ){
            if ($ID == null || $ID == ""){
                return;  // Admin call for instance in test
            }
            $dokuWikiDescription = TestUtility::getMeta($ID,'description');
        } else {
            $render = METADATA_RENDER_USING_CACHE;
            $dokuWikiDescription = p_get_metadata($ID, 'description',$render);
        }

        if (empty($dokuWikiDescription) || $dokuWikiDescription == "") {
            $this->sendDestInfo($ID);
            return;
        }

        // Get the abstract and suppress the carriage return
        $description = str_replace("\n", " ", $dokuWikiDescription['abstract']);
        if (empty($description)) {
            $this->sendDestInfo($ID);
            return;
        }

        // Suppress the title
        $title = p_get_metadata($ID, 'title');
        $description = str_replace($title, "", $description);
        // Suppress the star, the tab, About
        $description = preg_replace('/(\*|\t|About)/im', "", $description);
        // Suppress all double space and trim
        $description = trim(preg_replace('/  /m', " ", $description));

        // Add it to the meta
        $event->data['meta'][] = array("name" => "description", "content" => $description);


    }

    /**
     * Just send a test info
     * @param $ID
     */
    public function sendDestInfo($ID)
    {
        if (defined('DOKU_UNITTEST')) {
            // When you make a admin test call, the page ID = start and there is no meta
            // When there is only an icon, there is also no meta
            global $INPUT;
            $showActions = ["show", ""]; // Empty for the test
            if (in_array($INPUT->str("do"), $showActions)) {
                PluginUtility::msg("Page ($ID): The description should never be null when rendering the page", PluginUtility::LVL_MSG_INFO);
            }
        }
    }


}
