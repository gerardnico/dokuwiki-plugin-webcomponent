<?php

use ComboStrap\PluginUtility;

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');


/**
 *
 * To show a message after redirection or rewriting
 *
 *
 *
 */

class action_plugin_combo_metaviewer extends DokuWiki_Action_Plugin
{

    const META_MESSAGE_BOX_ID = "metadata-viewer";


    function __construct()
    {
        // enable direct access to language strings
        // ie $this->lang
        $this->setupLocale();
    }


    function register(Doku_Event_Handler $controller)
    {

        /* This will call the function _displayMetaMessage */
        $controller->register_hook('TPL_ACT_RENDER','BEFORE',$this,'_displayMetaViewer',array());


    }


    /**
     * Main function; dispatches the visual comment actions
     * @param   $event Doku_Event
     */
    function _displayMetaViewer(&$event, $param)
    {

        if ($event->data == 'edit' || $event->data == 'preview') {


            ptln('<div id="'. self::META_MESSAGE_BOX_ID . '" class="alert alert-success " role="note">');

            global $ID;
            $metadata = p_read_metadata($ID);
            $persistentMetas = $metadata['persistent'];
//            foreach ($persistentMetas as $key => $value){
//                if ($key=="date"){
//                    $dates = $persistentMetas["date"];
//                    $date_created = "";
//                    date_modified;
//                }
//
//                //description;
//                //last_change;
//            }


            if (!array_key_exists("canonical", $persistentMetas)) {
                print "No canonical";
            } else {
                print "canonical:".$persistentMetas["canonical"];
            }

            $referenceStyle = array(
                "font-size"=> "95%",
                "clear"=>"both",
                "bottom"=>"5px",
                "right"=>"15px",
                "position"=> "absolute",
                "font-style"=>"italic"
            );

            print '<div style="'.PluginUtility::array2InlineStyle($referenceStyle).'">' . $this->lang['message_come_from'] . ' <a href="' . PluginUtility::$URL_BASE . '/metadata/viewer" class="urlextern" title="ComboStrap Metdata Viewer" >ComboStrap Metdata Viewer</a>.</div>';
            print('</div>');

        }

    }


}
