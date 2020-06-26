<?php
// Default value in the configuration Manager

$conf['UnitShortCutKey'] = 'u';

$conf[syntax_plugin_webcomponent_related::MAX_LINKS_CONF] = 10;
$conf[syntax_plugin_webcomponent_related::EXTRA_PATTERN_CONF] = '{{backlinks>.}}';

/**
 * Disqus
 */
$conf[syntax_plugin_webcomponent_disqus::FORUM_SHORT_NAME] = '';

/**
 * Url Manager
 */
require_once(__DIR__ . '/../action/urlmanager.php');
$conf['ActionReaderFirst']  = action_plugin_webcomponent_urlmanager::GO_TO_BEST_END_PAGE_NAME;
$conf['ActionReaderSecond'] = action_plugin_webcomponent_urlmanager::GO_TO_BEST_PAGE_NAME;
$conf['ActionReaderThird']  = action_plugin_webcomponent_urlmanager::GO_TO_SEARCH_ENGINE;
$conf['GoToEditMode'] = 1;
$conf['ShowPageNameIsNotUnique'] = 1;
$conf['ShowMessageClassic'] = 1;
$conf['WeightFactorForSamePageName'] = 4;
$conf['WeightFactorForStartPage'] = 3;
// If the page has the same namespace in its path, it gets more weight
$conf['WeightFactorForSameNamespace'] = 5;

/*
 * Does automatic canonical processing is on
 */
$conf[action_plugin_webcomponent_metacanonical::CANONICAL_LAST_NAMES_COUNT_CONF] = 0;
