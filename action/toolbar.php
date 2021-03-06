<?php
/**
 * Action Component
 * Add a button in the edit toolbar
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Nicolas GERARD
 */

if (!defined('DOKU_INC')) die();
require_once(__DIR__ . '/../webcomponent.php');


class action_plugin_webcomponent_toolbar extends DokuWiki_Action_Plugin {

    /**
     * register the event handlers
     *
     * @author Nicolas GERARD
     */
    function register(Doku_Event_Handler $controller){
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'handle_toolbar', array ());
    }

    function handle_toolbar(&$event, $param) {
        $unitShortcutKey = $this->getConf('UnitShortCutKey');

        $event->data[] = array(
            'type'   => 'format',
            'title'  => $this->getLang('DocBlockButtonTitle').' ('.$this->getLang('AccessKey').': '.$unitShortcutKey.')',
            'icon'   => '../../plugins/'.webcomponent::PLUGIN_NAME.'/images/unit-doc-block.png',
            'open'   => '<unit name="default">\n<file lang path>\n</file>\n\t<code lang>',
            'close'  => '\n\t</code>\n\tt<console>\n\t</console></unit>\n',
            'key'    => $unitShortcutKey
        );


    }

}

