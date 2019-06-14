<?php

if (!defined('DOKU_INC')) die();
require_once(__DIR__ . '/../webcomponent.php');

class action_plugin_webcomponent_blockquote extends DokuWiki_Action_Plugin
{

    /**
     * Toolbar
     */
    function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'blockquote_button', array());
    }

    /**
     * Inserts a toolbar button
     */
    function blockquote_button(Doku_Event $event, $param)
    {
        $event->data[] = array(
            'type' => 'format',
            'title' => 'blockquote',
            'icon' => '../../plugins/' . webcomponent::PLUGIN_NAME . '/images/blockquote-icon.png',
            'open' => '<blockquote>',
            'close' => '</blockquote>',

        );
        return true;
    }
}