<?php

use ComboStrap\UrlManagerBestEndPage;

include_once (__DIR__.'/../syntax/related.php');

$meta['UnitShortCutKey'] = array('string');

// https://www.dokuwiki.org/devel:configuration
$meta[syntax_plugin_combo_related::MAX_LINKS_CONF] = array('numeric');
$meta[syntax_plugin_combo_related::EXTRA_PATTERN_CONF] = array('string');

/**
 * Disqus
 */
include_once (__DIR__.'/../syntax/disqus.php');
$meta[syntax_plugin_combo_disqus::FORUM_SHORT_NAME] = array('string');


/**
 * Url Manager
 */
$meta['GoToEditMode'] = array('onoff');
$meta['ShowPageNameIsNotUnique'] = array('onoff');
$meta['ShowMessageClassic'] = array('onoff');

include_once (__DIR__.'/../action/urlmanager.php');
$actionChoices = array('multichoice', '_choices' => array(
    action_plugin_combo_urlmanager::NOTHING,
    action_plugin_combo_urlmanager::GO_TO_BEST_END_PAGE_NAME,
    action_plugin_combo_urlmanager::GO_TO_NS_START_PAGE,
    action_plugin_combo_urlmanager::GO_TO_BEST_PAGE_NAME,
    action_plugin_combo_urlmanager::GO_TO_BEST_NAMESPACE,
    action_plugin_combo_urlmanager::GO_TO_SEARCH_ENGINE
));

$meta['ActionReaderFirst']  = $actionChoices;
$meta['ActionReaderSecond'] = $actionChoices;
$meta['ActionReaderThird']  = $actionChoices;
$meta['WeightFactorForSamePageName'] = array('string');
$meta['WeightFactorForStartPage'] = array('string');
$meta['WeightFactorForSameNamespace'] = array('string');
require_once(__DIR__ . '/../class/UrlManagerBestEndPage.php');
$meta[UrlManagerBestEndPage::CONF_MINIMAL_SCORE_FOR_REDIRECT] = array('string');

$meta[action_plugin_combo_metacanonical::CANONICAL_LAST_NAMES_COUNT_CONF]= array('string');

/**
 * Icon namespace where the downloaded icon are stored
 */
require_once(__DIR__ . '/../syntax/icon.php');
$meta[syntax_plugin_combo_icon::CONF_ICONS_MEDIA_NAMESPACE]=array('string');

/**
 * Css optimization
 */
$meta[action_plugin_combo_css::CONF_ENABLE_MINIMAL_FRONTEND_STYLESHEET]=array('onoff');
$meta[action_plugin_combo_css::CONF_DISABLE_DOKUWIKI_STYLESHEET]=array('onoff');
