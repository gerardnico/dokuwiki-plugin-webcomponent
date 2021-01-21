<?php
/**
 * Copyright (c) 2021. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */
if (!defined('DOKU_INC')) die();

use splitbrain\phpcli\Options;

/**
 * The memory of the server 128 is not enough
 */
ini_set('memory_limit', '256M');

/**
 * Class cli_plugin_combo
 *
 * This is a cli:
 * https://www.dokuwiki.org/devel:cli_plugins#example
 *
 * Usage:
 *
 * ```
 * docker exec -ti $(CONTAINER) /bin/bash
 * ./bin/plugin.php combo -o pages.csv
 * ```
 * or via the IDE
 *
 *
 * Example:
 * https://www.dokuwiki.org/tips:grapher
 *
 */
class cli_plugin_combo extends DokuWiki_CLI_Plugin
{

    /**
     * register options and arguments
     * @param Options $options
     */
    protected function setup(Options $options)
    {
        $options->setHelp('Extract statistics information');
        $options->registerOption('version', 'print version', 'v');
        $options->registerArgument(
            'namespaces',
            "If no namespace is given, the root namespace is assumed.",
            false);
        $options->registerOption(
            'output',
            "Where to store the output eg. a filename. If not given the output is written to STDOUT.",
            'o', 'file');

    }

    /**
     * The main entry
     * @param Options $options
     */
    protected function main(Options $options)
    {

        $namespaces = array_map('cleanID', $options->getArgs());
        if (!count($namespaces)) $namespaces = array(''); //import from top

        $output = $options->getOpt('output', '-');
        if ($output == '-') $output = 'php://stdout';

        $fileHandle = @fopen($output, 'w');
        if (!$fileHandle) $this->fatal("Failed to open $output");
        $this->process($namespaces, $fileHandle);
        fclose($fileHandle);

    }

    /**
     * @param $namespaces
     * @param $fileHandle
     * @param int $depth recursion depth. 0 for unlimited
     */
    private function process($namespaces, $fileHandle, $depth = 0)
    {
        global $conf;


        // find pages
        $pages = array();
        foreach ($namespaces as $ns) {

            search(
                $pages,
                $conf['datadir'],
                'search_universal',
                array(
                    'depth' => $depth,
                    'listfiles' => true,
                    'listdirs' => false,
                    'pagesonly' => true,
                    'skipacl' => true,
                    'firsthead' => false,
                    'meta' => false,
                ),
                str_replace(':', '/', $ns)
            );

            // add the ns start page
            if ($ns && page_exists($ns)) {
                $pages[] = array(
                    'id' => $ns,
                    'ns' => getNS($ns),
                    'title' => p_get_first_heading($ns, false),
                    'size' => filesize(wikiFN($ns)),
                    'mtime' => filemtime(wikiFN($ns)),
                    'perm' => 16,
                    'type' => 'f',
                    'level' => 0,
                    'open' => 1,
                );
            }

        }

        /** @var helper_plugin_qc $qc */
        $qc = plugin_load('helper', 'qc');

        $header = array(
            'id',
            'backlinks',
            'broken_links',
            'changes',
            'chars',
            'external_links',
            'external_medias',
            'formatted',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'internal_links',
            'internal_medias',
            'words',
            'score'
        );
        fwrite($fileHandle, implode(",", $header) . PHP_EOL);
        while ($page = array_shift($pages)) {
            $id=$page['id'];
            echo 'Processing the page '.$id . "\n";
            // $meta = p_get_metadata($id);
            $backlinks = sizeof(ft_backlinks($id, true));
            $qcData = $qc->getQCData($id);
            $row = array(
                'id' => $id,
                'backlinks' => $backlinks,
                'broken_links' => $qcData['broken_links'],
                'changes' => $qcData['changes'],
                'chars' => $qcData['chars'],
                'external_links' => $qcData['external_links'],
                'external_medias' => $qcData['external_medias'],
                'formatted' => $qcData['formatted'],
                'h1' => $qcData['header_count'][1],
                'h2' => $qcData['header_count'][2],
                'h3' => $qcData['header_count'][3],
                'h4' => $qcData['header_count'][4],
                'h5' => $qcData['header_count'][5],
                'internal_links' => $qcData['internal_links'],
                'internal_medias' => $qcData['internal_medias'],
                'words' => $qcData['words'],
                'score' => $qcData['score']
            );
            fwrite($fileHandle, implode(",", $row) . PHP_EOL);
        }

    }
}
