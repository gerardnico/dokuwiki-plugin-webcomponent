<?php


use dokuwiki\ChangeLog\PageChangeLog;

/**
 * A stats Renderer
 * You can export the data with
 * doku.php?id=somepage&do=export_combo_stats
 */
class renderer_plugin_combo_stats extends Doku_Renderer
{
    const HEADER_POSITION = 'header_id';
    const HEADER_COUNT = 'headers';
    const INTERNAL_LINK_DISTANCE = 'internal_links_distance';
    const DATE_CREATED = 'date_created';
    const DATE_MODIFIED = 'date_modified';
    const TITLE = 'title';
    const QUALITY = 'quality';
    const PLAINTEXT = 'formatted';
    const RESULT = "result";
    const SCORE = "score";
    const DESCRIPTION = "description";

    const RULE_BACKLINKS_MIN = 'backlinks_min';
    const PASSED = "Passed";
    const FAILED = "Failed";
    const RULE_FIXME = "fixme_min";
    const TOP_SCORE = 10;
    const RULE_TITLE = "title_present";
    const RULE_HEADERS_STRUCTURE = "headers_structure";
    const BACKLINKS = "backlinks";
    const FIXME = 'fixme';
    const WORDS = 'words';
    const RULE_WORDS_MINIMAL = 'words_min';
    /**
     * We store all our data in an array
     */
    public $stats = array(

        self::HEADER_COUNT => array(),
        self::HEADER_POSITION => array(),
        'linebreak' => 0,
        'quote_nest' => 0,
        'quote_count' => 0,
        'hr' => 0,
        self::PLAINTEXT => 0,
        self::DATE_CREATED => 0,
        self::DATE_MODIFIED => 0,
        'changes' => 0,
        'authors' => array(),
        'internal_links' => 0,
        'internal_medias' => 0,
        'broken_links' => 0,
        'external_links' => 0,
        'external_medias' => 0,
        self::INTERNAL_LINK_DISTANCE => array(),
        'chars' => 0,
        self::WORDS => 0,

    );

    protected $quotelevel = 0;
    protected $formattingBracket = 0;
    protected $tableopen = false;
    /**
     * @var int The id of the header on the pahe
     * 1 = first header
     * 2 = second header
     */
    protected $headerId = 0;
    private $plainTextId = 0;

    public function document_start() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        global $ID;
        $meta = p_get_metadata($ID);

        // get some dates from meta data
        $this->stats[self::DATE_CREATED] = date('Y-m-d h:i:s', $meta['date']['created']);
        $this->stats[self::DATE_MODIFIED] = date('Y-m-d h:i:s', $meta['date']['modified']);
        $this->stats[self::TITLE] = $meta['title'];

        // get author info
        $changelog = new PageChangeLog($ID);
        $revs = $changelog->getRevisions(0, 10000);
        array_push($revs, $meta['last_change']['date']);
        $this->stats['changes'] = count($revs);
        foreach ($revs as $rev) {
            $info = $changelog->getRevisionInfo($rev);
            if ($info['user']) {
                $this->stats['authors'][$info['user']] += 1;
            } else {
                $this->stats['authors']['*'] += 1;
            }
        }

        // work on raw text
        $text = rawWiki($ID);
        $this->stats['chars'] = strlen($text);
        $this->stats[self::WORDS] = count(array_filter(preg_split('/[^\w\-_]/u', $text)));
    }


    /**
     * Here the score is calculated
     */
    public function document_end() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        global $ID;

        /**
         * The exported object
         */
        $statExport = $this->stats;

        /**
         * Internal link distance summary calculation
         */
        $linkLengths = $statExport[self::INTERNAL_LINK_DISTANCE];
        unset($statExport[self::INTERNAL_LINK_DISTANCE]);
        $countBacklinks = count($linkLengths);
        $statExport[self::INTERNAL_LINK_DISTANCE]['avg'] = null;
        $statExport[self::INTERNAL_LINK_DISTANCE]['max'] = null;
        $statExport[self::INTERNAL_LINK_DISTANCE]['min'] = null;
        if ($countBacklinks > 0) {
            $statExport[self::INTERNAL_LINK_DISTANCE]['avg'] = array_sum($linkLengths) / $countBacklinks;
            $statExport[self::INTERNAL_LINK_DISTANCE]['max'] = max($linkLengths);
            $statExport[self::INTERNAL_LINK_DISTANCE]['min'] = min($linkLengths);
        }

        /**
         * Quality Report / Rules
         */
        /**
         * backlinks are an
         */
        $ruleResults = array();
        $ruleResults[self::RULE_BACKLINKS_MIN][self::DESCRIPTION] = "A page should have at minimum one backlink";
        $errorPoint = 0;
        $countBacklinks = count(ft_backlinks($ID));
        $statExport[self::BACKLINKS] = $countBacklinks;
        if ($countBacklinks == 0) {
            $errorPoint += 2;
            $ruleResults[self::RULE_BACKLINKS_MIN][self::RESULT] = self::FAILED;
        } else {
            $ruleResults[self::RULE_BACKLINKS_MIN][self::RESULT] = self::PASSED;
        }


        /**
         * No fixme
         */
        $ruleResults[self::RULE_FIXME][self::DESCRIPTION] = "A page should have no fixme";
        $fixmeCount = $this->stats[self::FIXME];
        $statExport[self::FIXME] = $fixmeCount == null ? 0 : $fixmeCount;
        if ($fixmeCount != 0) {
            $errorPoint += $fixmeCount;
            $ruleResults[self::RULE_FIXME][self::RESULT] = self::FAILED;
        } else {
            $ruleResults[self::RULE_FIXME][self::RESULT] = self::PASSED;
        }

        /**
         * No title
         */
        $ruleResults[self::RULE_TITLE][self::DESCRIPTION] = "A title should be present";
        if (empty($this->stats[self::TITLE])) {
            $errorPoint += 5;
            $ruleResults[self::RULE_TITLE][self::RESULT] = self::FAILED;
        } else {
            $ruleResults[self::RULE_TITLE][self::RESULT] = self::PASSED;
        }

        /**
         * header structure
         */
        $ruleResults[self::RULE_HEADERS_STRUCTURE][self::DESCRIPTION] = "The headers should have a tree structure";
        $cnt = count($this->stats[self::HEADER_POSITION]);
        unset($statExport[self::HEADER_POSITION]);
        $treeError = 0;
        for ($i = 1; $i < $cnt; $i++) {
            $currentHeaderLevel = $this->stats['header_struct'][$i];
            $previousHeaderLevel = $this->stats['header_struct'][$i - 1];
            if ($currentHeaderLevel - $previousHeaderLevel > 1) {
                $treeError += 1;
                $ruleResults[self::RULE_HEADERS_STRUCTURE][self::RESULT][self::DESCRIPTION][] = "The " . $i . " header (h" . $currentHeaderLevel . ") has a level bigger than its precedent (" . $previousHeaderLevel . ")";
            }
        }
        if ($treeError > 0) {
            $ruleResults[self::RULE_HEADERS_STRUCTURE][self::RESULT] = self::FAILED;
        } else {
            $ruleResults[self::RULE_HEADERS_STRUCTURE][self::RESULT] = self::PASSED;
        }

        /**
         * Small document
         */
        $minimalWordCount = 150;
        $ruleResults[self::RULE_WORDS_MINIMAL][self::DESCRIPTION] = "A page should have at minimal " . $minimalWordCount . " words.";
        if ($this->stats[self::WORDS] < $minimalWordCount) {
            $ruleResults[self::RULE_WORDS_MINIMAL][self::RESULT] = self::FAILED;
        } else {
            $ruleResults[self::RULE_WORDS_MINIMAL][self::RESULT] = self::PASSED;
        }

        // 1 point for too large document
        if ($this->stats['chars'] > 100000) {
            $ruleResults['toolarge'] = 1;
        }

        // header to text ratio
        $hc = $this->stats[self::HEADER_COUNT][1] +
            $this->stats[self::HEADER_COUNT][2] +
            $this->stats[self::HEADER_COUNT][3] +
            $this->stats[self::HEADER_COUNT][4] +
            $this->stats[self::HEADER_COUNT][5];
        $hc--; //we expect at least 1
        if ($hc > 0) {
            $hr = $this->stats['chars'] / $hc;

            // 1 point for too many headers
            if ($hr < 200) {
                $ruleResults['manyheaders'] = 1;
            }

            // 1 point for too few headers
            if ($hr > 2000) {
                $ruleResults['fewheaders'] = 1;
            }
        }

        // 1 point when no link at all
        if (!$this->stats['internal_links']) {
            $ruleResults['nolink'] = 1;
        }

        // 0.5 for broken links when too many
        if ($this->stats['broken_links'] > 2) {
            $ruleResults['brokenlink'] = $this->stats['broken_links'] * 0.5;
        }

        // 2 points for lot's of formatting
        if ($this->stats[self::PLAINTEXT] && $this->stats['chars'] / $this->stats[self::PLAINTEXT] < 3) {
            $ruleResults['manyformat'] = 2;
        }

        /**
         * Rules comes from the qc plugin
         * They stay for doc
         */
        // 1/2 points for deeply nested quotations
        if ($this->stats['quote_nest'] > 2) {
            $ruleResults['deepquote'] += $this->stats['quote_nest'] / 2;
        }

        // 1/2 points for too many hr
        if ($this->stats['hr'] > 2) {
            $ruleResults['manyhr'] = ($this->stats['hr'] - 2) / 2;
        }

        // 1 point for too many line breaks
        if ($this->stats['linebreak'] > 2) {
            $ruleResults['manybr'] = $this->stats['linebreak'] - 2;
        }

        // 1 point for single author only
        if (!$this->getConf('single_author_only') && count($this->stats['authors']) == 1) {
            $ruleResults['singleauthor'] = 1;
        }

        // Too much cdata (plaintext), see cdata
        // if ($len > 500) $statExport[self::QUALITY][self::ERROR]['plaintext']++;
        // if ($len > 500) $statExport[self::QUALITY][self::ERROR]['plaintext']++;
        //
        // // 1 point for formattings longer than 500 chars
        // $statExport[self::QUALITY][self::ERROR]['multiformat']

        /**
         * Quality Score
         */
        $quality = array();
        $qualityScore = self::TOP_SCORE - $errorPoint;
        if ($qualityScore < 0) {
            $qualityScore = 0;
        }
        $quality[self::SCORE] = $qualityScore;
        if ($qualityScore != self::TOP_SCORE) {
            $error = 0;
            foreach ($ruleResults as $ruleResult) {
                if ($ruleResult == self::FAILED) {
                    $error++;
                }
            }
            $quality[self::RESULT] = $error . " quality rules errors";
        } else {
            $quality[self::RESULT] = "All quality rules passed";
        }
        $quality["rules"] = $ruleResults;

        global $ID;
        $statExport["id"] = $ID;
        ksort($statExport);

        /**
         * Quality after the sort to get them at the end
         */
        $statExport[self::QUALITY] = $quality;


        // doku.php?id=somepage&do=export_combo_stats
        header('Content-Type: application/json');
        $this->doc .= json_encode($statExport, JSON_PRETTY_PRINT);

    }

    /**
     */
    public function getFormat()
    {
        return 'json';
    }

    public function internallink($id, $name = null, $search = null, $returnonly = false, $linktype = 'content')
    {

        /**
         * Stats
         */
        global $ID;
        resolve_pageid(getNS($ID), $id, $exists);
        $this->stats['internal_links']++;
        if (!$exists) $this->stats['broken_links']++;


        /**
         * Calculate link distance
         */
        $a = explode(':', getNS($ID));
        $b = explode(':', getNS($id));
        while (isset($a[0]) && $a[0] == $b[0]) {
            array_shift($a);
            array_shift($b);
        }
        $length = count($a) + count($b);
        $this->stats[self::INTERNAL_LINK_DISTANCE][] = $length;


    }

    public function externallink($url, $name = null)
    {
        $this->stats['external_links']++;
    }

    public function header($text, $level, $pos)
    {
        $this->stats[self::HEADER_COUNT]['h' . $level]++;
        $this->headerId++;
        $this->stats[self::HEADER_POSITION][$this->headerId] = 'h' . $level;
    }

    public function smiley($smiley)
    {
        if ($smiley == 'FIXME') $this->stats[self::FIXME]++;
    }

    public function linebreak()
    {
        if (!$this->tableopen) {
            $this->stats['linebreak']++;
        }
    }

    public function table_open($maxcols = null, $numrows = null, $pos = null) // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $this->tableopen = true;
    }

    public function table_close($pos = null) // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $this->tableopen = false;
    }

    public function hr()
    {
        $this->stats['hr']++;
    }

    public function quote_open() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $this->stats['quote_count']++;
        $this->quotelevel++;
        $this->stats['quote_nest'] = max($this->quotelevel, $this->stats['quote_nest']);
    }

    public function quote_close() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $this->quotelevel--;
    }

    public function strong_open() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $this->formattingBracket++;
    }

    public function strong_close() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $this->formattingBracket--;
    }

    public function emphasis_open() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $this->formattingBracket++;
    }

    public function emphasis_close() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $this->formattingBracket--;
    }

    public function underline_open() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $this->formattingBracket++;
    }

    public function underline_close() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $this->formattingBracket--;
    }

    public function cdata($text)
    {

        /**
         * It seems that you receive cdata
         * when emphasis_open / underline_open / strong_open
         * Stats are not for them
         */
        if (!$this->formattingBracket) return;

        $this->plainTextId++;

        /**
         * Length
         */
        $len = strlen($text);
        $this->stats[self::PLAINTEXT][$this->plainTextId]['len'] = $len;


        /**
         * Multi-formatting
         */
        if ($this->formattingBracket > 1) {
            $numberOfFormats = 1 * ($this->formattingBracket - 1);
            $this->stats[self::PLAINTEXT][$this->plainTextId]['multiformat'] += $numberOfFormats;
        }

        /**
         * Total
         */
        $this->stats[self::PLAINTEXT][0] += $len;
    }

    public function internalmedia($src, $title = null, $align = null, $width = null, $height = null, $cache = null, $linking = null)
    {
        $this->stats['internal_medias']++;
    }

    public function externalmedia($src, $title = null, $align = null, $width = null, $height = null, $cache = null, $linking = null)
    {
        $this->stats['external_medias']++;
    }


}

//Setup VIM: ex: et ts=4 enc=utf-8 :
