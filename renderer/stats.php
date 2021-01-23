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
    const HEADER_COUNT = 'header_count';
    const LINK_LENGTHS = 'link_lengths';
    const DATE_CREATED = 'date_created';
    const DATE_MODIFIED = 'date_modified';
    const REPORT = 'report';
    const TITLE = 'title';
    const QUALITY = 'quality';
    const PLAINTEXT = 'formatted';
    const RESULT = "result";
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
        self::LINK_LENGTHS => array(),
        'chars' => 0,
        'words' => 0,

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
        $this->stats[self::DATE_CREATED] = date('Y-m-d h:i:s',$meta['date']['created']);
        $this->stats[self::DATE_MODIFIED] = date('Y-m-d h:i:s',$meta['date']['modified']);
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
        $this->stats['words'] = count(array_filter(preg_split('/[^\w\-_]/u', $text)));
    }


    /**
     * Here the score is calculated
     */
    public function document_end() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        global $ID;

        // The exported object
        $statExport = $this->stats;
        $linkLengths = $statExport[self::LINK_LENGTHS];
        unset($statExport[self::LINK_LENGTHS]);
        $statExport['link_distance']['avg'] = array_sum($linkLengths) / count($linkLengths);
        $statExport['link_distance']['max'] = max($linkLengths);
        $statExport['link_distance']['min'] = min($linkLengths);

        /**
         * Quality Report
         */
        // 2 points for missing backlinks
        $errors = array();
        if (!count(ft_backlinks($ID))) {
            $errors['nobacklink'] += 2;
        }

        // 1 point for each FIXME
        if ($this->stats['fixme']!=0) {
            $errors['fixme'] += $this->stats['fixme'];
        }

        // 5 points for missing H1 / normally title
        if ($this->stats[self::HEADER_COUNT]['h1'] == 0) {
            $errors['noh1'] += 5;
        }
        // 1 point for each H1 too much
        if ($this->stats[self::HEADER_COUNT][1] > 1) {
            $errors['manyh1'] += $this->stats['header'][1];
        }

        // 1 point for each incorrectly nested headline
        $cnt = count($this->stats[self::HEADER_POSITION]);
        for ($i = 1; $i < $cnt; $i++) {
            $currentHeader = $this->stats['header_struct'][$i];
            $previousHeader = $this->stats['header_struct'][$i - 1];
            if ($currentHeader - $previousHeader > 1) {
                $errors['headernest'] += 1;
            }
        }

        // 1/2 points for deeply nested quotations
        if ($this->stats['quote_nest'] > 2) {
            $errors['deepquote'] += $this->stats['quote_nest'] / 2;
        }

        // FIXME points for many quotes?

        // 1/2 points for too many hr
        if ($this->stats['hr'] > 2) {
            $errors['manyhr'] = ($this->stats['hr'] - 2) / 2;
        }

        // 1 point for too many line breaks
        if ($this->stats['linebreak'] > 2) {
            $errors['manybr'] = $this->stats['linebreak'] - 2;
        }

        // 1 point for single author only
        if (!$this->getConf('single_author_only') && count($this->stats['authors']) == 1) {
            $errors['singleauthor'] = 1;
        }

        // 1 point for too small document
        if ($this->stats['chars'] < 150) {
            $errors['toosmall'] = 1;
        }

        // 1 point for too large document
        if ($this->stats['chars'] > 100000) {
            $errors['toolarge'] = 1;
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
                $errors['manyheaders'] = 1;
            }

            // 1 point for too few headers
            if ($hr > 2000) {
                $errors['fewheaders'] = 1;
            }
        }

        // 1 point when no link at all
        if (!$this->stats['internal_links']) {
            $errors['nolink'] = 1;
        }

        // 0.5 for broken links when too many
        if ($this->stats['broken_links'] > 2) {
            $errors['brokenlink'] = $this->stats['broken_links'] * 0.5;
        }

        // 2 points for lot's of formatting
        if ($this->stats[self::PLAINTEXT] && $this->stats['chars'] / $this->stats[self::PLAINTEXT] < 3) {
            $errors['manyformat'] = 2;
        }

        /**
         * Quality Score
         */
        $score = 10;
        if (sizeof($errors)>0) {
            foreach ($errors as $err => $val) {
                $score -= $val;
            }
            $statExport[self::QUALITY][self::REPORT] = $errors;
            $statExport[self::QUALITY][self::RESULT] = sizeof($errors)." quality rules errors";
        } else {
            $statExport[self::QUALITY][self::RESULT] = "All quality rules passed";
        }

        if ($score<0){
            $score = 0;
        }
        $statExport[self::QUALITY]['score']=$score;

        ksort($statExport);
        // doku.php?id=somepage&do=export_combo_stats
        header('Content-Type: application/json');
        $this->doc .= json_encode($statExport, JSON_PRETTY_PRINT);

    }

    /**
     * the format we produce
     */
    public function getFormat()
    {
        return 'json';
    }

    public function internallink($id, $name = null, $search = null, $returnonly = false, $linktype = 'content')
    {
        global $ID;
        resolve_pageid(getNS($ID), $id, $exists);

        // calculate link width
        $a = explode(':', getNS($ID));
        $b = explode(':', getNS($id));
        while (isset($a[0]) && $a[0] == $b[0]) {
            array_shift($a);
            array_shift($b);
        }
        $length = count($a) + count($b);
        $this->stats[self::LINK_LENGTHS][] = $length;

        $this->stats['internal_links']++;
        if (!$exists) $this->stats['broken_links']++;
    }

    public function externallink($url, $name = null)
    {
        $this->stats['external_links']++;
    }

    public function header($text, $level, $pos)
    {
        $this->stats[self::HEADER_COUNT]['h'.$level]++;
        $this->headerId++;
        $this->stats[self::HEADER_POSITION][$this->headerId] = 'h'.$level;
    }

    public function smiley($smiley)
    {
        if ($smiley == 'FIXME') $this->stats['fixme']++;
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
        $this->stats[self::PLAINTEXT][$this->plainTextId]['len']=$len;


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
