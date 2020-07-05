<?php

if (!defined('DOKU_INC')) die('meh.');


require_once DOKU_INC . 'inc/parser/xhtml.php';

/**
 * Class renderer_plugin_combo_renderer
 */
class  renderer_plugin_combo_renderer extends Doku_Renderer_xhtml
{

    /**
     * @var array that hold the position of the parent
     */
    protected $nodeParentPosition = [];

    /**
     * @var array that hold the current position of an header for a level
     * $headerNum[level]=position
     */
    protected $header = [];

    /**
     * @var array that will contains the whole doc but by section
     */
    protected $sections = [];

    /**
     * @var the section number
     */
    protected $sectionNumber = 0;

    /**
     * @var variable that permits to carry the header text of a previous section
     */
    protected $previousSectionTextHeader = '';


    /**
     * @var variable that permits to carry the position of a previous section
     */
    protected $previousNodePosition = 0;

    /**
     * @var variable that permits to carry the position of a previous section
     */
    protected $previousNodeLevel = 0;

    /**
     * @var variable that permits to carry the number of words
     */
    protected $lineCounter = 0;


    function getFormat()
    {
        return 'xhtml';
    }

    /*
     * Function that enable to list the plugin in the options for config:renderer_xhtml
     * http://www.dokuwiki.org/config:renderer_xhtml
     * setting in its Configuration Manager.
     */
    public function canRender($format)
    {
        return ($format == 'xhtml');
    }


    /**
     * Render a heading
     *
     * The rendering of the heading is done through the parent
     * The function just:
     *   - save the rendering between each header in the class variable $this->sections
     * This variblae is used in the function document_end to recreate the whole doc.
     *   - add the numbering to the header text
     *
     * @param string $text the text to display
     * @param int $level header level
     * @param int $pos byte position in the original source
     */
    function header($text, $level, $pos)
    {


        // We are going from 2 to 3
        // The parent is 2
        if ($level > $this->previousNodeLevel) {
            $nodePosition = 1;
            // Keep the position of the parent
            $this->nodeParentPosition[$this->previousNodeLevel] = $this->previousNodePosition;
        } elseif
            // We are going from 3 to 2
            // The parent is 1
        ($level < $this->previousNodeLevel
        ) {
            $nodePosition = $this->nodeParentPosition[$level] + 1;
        } else {
            $nodePosition = $this->previousNodePosition + 1;
        }

        // Grab the doc from the previous section
        $this->sections[$this->sectionNumber] = array(
            'level' => $this->previousNodeLevel,
            'position' => $this->previousNodePosition,
            'content' => $this->doc,
            'text' => $this->previousSectionTextHeader);

        // And reset it
        $this->doc = '';
        // Set the looping variable
        $this->sectionNumber = $this->sectionNumber + 1;
        $this->previousNodeLevel = $level;
        $this->previousNodePosition = $nodePosition;
        $this->previousSectionTextHeader = $text;

        $numbering = "";
        if ($level == 2) {
            $numbering = $nodePosition;
        }
        if ($level == 3) {
            $numbering = $this->nodeParentPosition[$level - 1] . "." . $nodePosition;
        }
        if ($level == 4) {
            $numbering = $this->nodeParentPosition[$level - 2] . "." . $this->nodeParentPosition[$level - 1] . "." . $nodePosition;
        }
        if ($level == 5) {
            $numbering = $this->nodeParentPosition[$level - 3] . "." . $this->nodeParentPosition[$level - 2] . "." . $this->nodeParentPosition[$level - 1] . "." . $nodePosition;
        }
        if ($numbering <> "") {
            $textWithLocalization = $numbering . " - " . $text;
        } else {
            $textWithLocalization = $text;
        }

        // Rendering is done by the parent
        parent::header($textWithLocalization, $level, $pos);


        // Add the page detail after the first header
        if ($level == 1 and $nodePosition == 1) {

            $this->doc .= $this->breadcrumb();

        }


    }


    function document_end()
    {

        global $INFO;
        global $ID;
        // The id of the page (not of the sidebar)
        $id = $ID;
        $isSidebar = FALSE;
        if ($INFO != null) {
            $id = $INFO['id'];
            if ($ID != $id){
                $isSidebar = TRUE;
            }
        }



        // TOC init
        // Dow we need to show the toc ?
        $showToc = $this->getShowToc();
        global $TOC;
        // If the TOC is null (The toc may be initialized by a plugin)
        if (!is_array($TOC) or count($TOC) == 0) {
            $TOC = $this->toc;
        }

        // Pump the last doc
        $this->sections[$this->sectionNumber] = array('level' => $this->previousNodeLevel, 'position' => $this->previousNodePosition, 'content' => $this->doc, 'text' => $this->previousSectionTextHeader);

        // Recreate the doc
        $this->doc = '';
        $rollingLineCount = 0;
        $lineCounter = 0;
        $adsCounter = 0;
        foreach ($this->sections as $sectionNumber => $section) {

            $sectionContent = $section['content'];


            if ($section['level'] == 1 and $section['position'] == 1) {

                if ($showToc) {
                    $toc = tpl_toc($return = true);
                    global $ACT;
                    switch ($ACT){
                        case 'admin':
                            $sectionContent .= $toc;
                            break;
                        default:
                            global $conf;
                            if (count($TOC) > $conf['tocminheads']) {
                                $sectionContent .= $toc;
                            }
                            break;
                    }
                }

            }

            # Split by element line
            # element p, h, br, tr, li, pre (one line for pre)
            $localCount = count(preg_split("/<\/p>|<\/h[1-9]{1}>|<br|<\/tr>|<\/li>|<\/pre>/",$sectionContent)) - 1;
            $lineCounter += $localCount;
            $rollingLineCount += $localCount;

            // The content
            if ($this->getConf('ShowCount') == 1 && $isSidebar == FALSE ){
                $this->doc .= "<p>Section ".$sectionNumber.": (".$localCount."|".$lineCounter."|".$rollingLineCount.")</p>";
            }
            $this->doc .= $sectionContent;

            // No ads on private page


            global $ACT;

            if (
                $isSidebar == FALSE && // No display on the sidebar
                $ACT != 'admin' && // Not in the admin page
                isHiddenPage($id) == FALSE && // No ads on hidden pages
                (
                    (
                    $localCount > $this->getConf('AdsMinLocalLine') && // Doesn't show any ad if the section does not contains this minimun number of line
                    $lineCounter > $this->getConf('AdsLineBetween') && // Every N line,
                    $sectionNumber > $this->getConf('AdsMinSectionNumber') // Doesn't show any ad before
                    )
                    or
                    // Show always an ad after a number of section
                    (
                    $adsCounter == 0 && // Still not ads
                    $sectionNumber > $this->getConf('AdsMinSectionNumber') && // Above the mininum number of section
                    $localCount > $this->getConf('AdsMinLocalLine') // Minimum line in the current section (to avoid a pub below a header)
                    )
                    or
                    // Sometimes the last section (reference) has not so much line and it avoids to show an ads at the end
                    // even if the number of line (space) was enough
                    (
                    $sectionNumber == count($this->sections) - 1 && // The last section
                    $lineCounter > $this->getConf('AdsLineBetween')  // Every N line,
                    )
                )
               ){

                // Counter
                $adsCounter += 1;
                $lineCounter = 0;

                if ($this->getConf('ShowPlaceholder') == 1 ){
                    $this->doc .= '<div align="center" style="border:1px solid;padding:30px;height:90px">Placeholder'.$adsCounter.'</div>';
                } else {
                    if ( $adsCounter <= 6){
                        $this->doc .= $this->getConf('Ads'.$adsCounter);
                    }
                }

            }


        }

        parent::document_end();

    }

    /**
     * Start a table
     *
     * @param int $maxcols maximum number of columns
     * @param int $numrows NOT IMPLEMENTED
     * @param int $pos byte position in the original source
     * @param string|string[]  classes - have to be valid, do not pass unfiltered user input
     */
    function table_open($maxcols = null, $numrows = null, $pos = null, $classes = NULL)
    {
        // initialize the row counter used for classes
        $this->_counter['row_counter'] = 0;
        $class = 'table';
        if ($pos !== null) {
            $sectionEditStartData = ['target' => 'table'];
            if (!defined('SEC_EDIT_PATTERN')) {
                // backwards-compatibility for Frusterick Manners (2017-02-19)
                $sectionEditStartData = 'table';
            }
            $class .= ' ' . $this->startSectionEdit($pos, $sectionEditStartData);
        }
        // table-responsive and
        $bootResponsiveClass = 'table-responsive';
        $bootTableClass = 'table table-hover table-striped';

        $this->doc .= '<div class="' . $class . ' ' . $bootResponsiveClass . '"><table class="inline ' . $bootTableClass . '">' . DOKU_LF;

    }


    /**
     * Hierarchical breadcrumbs (you are here)
     *
     * This will return the Hierarchical breadcrumbs.
     *
     * Config:
     *    - $conf['youarehere'] must be true
     *    - add $lang['youarehere'] if $printPrefix is true
     *
     * Metadata comes from here
     * https://developers.google.com/search/docs/data-types/breadcrumb
     *
     * @return string
     */
    function breadcrumb()
    {

        global $conf;

        // check if enabled
        if (!$conf['youarehere']) return;

        // print intermediate namespace links
        $htmlOutput = '<p class="branch rplus">' . PHP_EOL;

        // Print the home page
        $htmlOutput .= '<span>' . PHP_EOL;
        $page = $conf['start'];
        $pageTitle = tpl_pagetitle($page, true);
        $htmlOutput .= tpl_link(wl($page), '<span class="nicon_home" aria-hidden="true"></span>', 'title="' . $pageTitle . '"', $return = true);
        $htmlOutput .= '</span>' . PHP_EOL;

        // Print the parts if there is more than one
        global $ID;
        $idParts = explode(':', $ID);
        $countPart = count($idParts);
        if ($countPart > 1) {

            // Print the parts without the last one ($count -1)
            $pagePart = "";
            for ($i = 0; $i < $countPart - 1; $i++) {

                $pagePart .= $idParts[$i] . ':';

                // We pass the value to the page variable
                // because the resolve part will change it
                $page = $pagePart;
                $exist = null;
                resolve_pageid(getNS($ID), $page, $exist, "", true);

                $pageTitle = tpl_pagetitle($page, true);
                $linkContent = $pageTitle;
                if ($i < $countPart - 1) {
                    $linkContent = " > " . $linkContent;
                }
                $htmlOutput .= '<span>';
                // html_wikilink because the page has the form pagename: and not pagename:pagename
                $htmlOutput .= tpl_link(wl($page), $linkContent, 'title="' . $pageTitle . '" class="navlink"', $return = true);
                $htmlOutput .= '</span>' . PHP_EOL;

            }
        }


        // print current page
        //    print '<li>';
        //    tpl_link(wl($page), tpl_pagetitle($page,true), 'title="' . $page . '"');
        //$htmlOutput .= '</li>' . PHP_EOL;

        // close the breadcrumb
        $htmlOutput .= '</p>' . PHP_EOL;
        return $htmlOutput;

    }

    /**
     * @return bool if the toc need to be shown
     */
    private function getShowToc()
    {
        // No TOC or bar for an admin page
        global $ACT;
        $showToc = null;

        if ($ACT == 'search') {

            $showToc = false;

        }

        if ($ACT == 'admin' and $showToc == null) {

            global $INPUT;
            $plugin = null;
            $class = $INPUT->str('page');
            if (!empty($class)) {

                $pluginlist = plugin_list('admin');

                if (in_array($class, $pluginlist)) {
                    // attempt to load the plugin
                    /** @var $plugin DokuWiki_Admin_Plugin */
                    $plugin = plugin_load('admin', $class);
                }

                if ($plugin !== null) {
                    global $TOC;
                    if (!is_array($TOC)) $TOC = $plugin->getTOC(); //if TOC wasn't requested yet
                    if (!is_array($TOC)) {
                        $showToc = false;
                    } else {
                        $showToc = true;
                    }

                }

            }

        }

        // Default True
        if ($showToc == null) {
            $showToc = true;
        }


        return $showToc;

    }


}
