<?php

use ComboStrap\PluginUtility;

/**
 * Take the metadata description
 *
 *
 */

require_once(__DIR__ . '/../class/PluginUtility.php');


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
            $render = METADATA_RENDER_USING_SIMPLE_CACHE;
        } else {
            $render = METADATA_RENDER_USING_CACHE;
        }
        $dokuWikiDescription = p_get_metadata($ID, 'description',$render);
        if (empty($dokuWikiDescription)) {
            if (defined('DOKU_UNITTEST')) {
                global $INPUT;
                $showActions = ["show", ""]; // Empty for the test
                if (in_array($INPUT->str("do"), $showActions)) {
                    PluginUtility::msg("Page ($ID): The description should never be null when rendering the page", PluginUtility::LVL_MSG_INFO);
                }
            }
            return;
        }

        // Get the abstract and suppress the carriage return
        $description = str_replace("\n", " ", $dokuWikiDescription['abstract']);
        if (empty($description)) {
            PluginUtility::msg("Page ($ID): The dokuwiki abstract meta is null", PluginUtility::LVL_MSG_WARNING);
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


}
