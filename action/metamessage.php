<?php

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');


/**
 *
 * To show a message after redirection or rewriting
 *
 *
 *
 */

class action_plugin_combo_metamessage extends DokuWiki_Action_Plugin
{

    // a class can not start with a number then webcomponent is not a valid class name
    const META_MESSAGE_BOX_CLASS = "meta-message";


    function __construct()
    {
        // enable direct access to language strings
        // ie $this->lang
        $this->setupLocale();
    }


    function register(Doku_Event_Handler $controller)
    {

        /* This will call the function _displayMetaMessage */
        $controller->register_hook('TPL_ACT_RENDER','BEFORE',$this,'_displayMetaMessage',array());


    }


    /**
     * Main function; dispatches the visual comment actions
     * @param   $event Doku_Event
     */
    function _displayMetaMessage(&$event, $param)
    {

        if ($event->data == 'edit' || $event->data == 'preview') {

            $pluginInfo = $this->getInfo();

            ptln('<div class="alert alert-success ' . self::META_MESSAGE_BOX_CLASS . '" role="alert">');

            global $ID;
            $canonical = p_get_metadata($ID, action_plugin_combo_metacanonical::CANONICAL_PROPERTY);
            if ($canonical) {
                print $canonical;
            } else {
                print "No canonical";
            }

            print '<div class="managerreference">' . $this->lang['message_come_from'] . ' <a href="' . $pluginInfo['url'] . '" class="urlextern" title="' . $pluginInfo['desc'] . '"  rel="nofollow">' . $pluginInfo['name'] . '</a>.</div>';
            print('</div>');

        }

    }


}
