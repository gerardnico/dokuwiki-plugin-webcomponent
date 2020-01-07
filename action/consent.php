<?php

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'action.php');

class action_plugin_webcomponent_consent extends DokuWiki_Action_Plugin
{


    /**
     * Constructor
     */
    function __construct()
    {
        // enable direct access to language strings
        $this->setupLocale();
    }

    function getInfo()
    {
        return confToHash(dirname(__FILE__) . '/plugin.info.txt');
    }

    function register(Doku_Event_Handler $controller)
    {


        $controller->register_hook('DOKUWIKI_STARTED',
            'AFTER',
            $this, '_setConsentPolicyConf');


    }

    /**
     * @param $event
     * @param $param
     * Set the JS Script configuration object for the consent box
     */
    function _setConsentPolicyConf(&$event, $param) {
        global $JSINFO;
        $data = array();
        $data['message']=$this->getConf('ConsentMessage');
        $JSINFO['consent_conf'] = $data;
    }



}
