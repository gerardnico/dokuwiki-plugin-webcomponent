<?php

use ComboStrap\PluginUtility;

require_once(__DIR__ . '/../class/PluginUtility.php');

/**
 * Disqus integration
 */
class syntax_plugin_combo_disqus extends DokuWiki_Syntax_Plugin
{

    const FORUM_SHORT_NAME = 'forumShortName';

    const TAG = 'disqus';

    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see https://www.dokuwiki.org/devel:syntax_plugins#syntax_types
     */
    function getType()
    {
        return 'substition';
    }

    /**
     * Syntax Type.
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     * @see DokuWiki_Syntax_Plugin::getType()
     */
    function getPType()
    {
        return 'block';
    }

    /**
     * Plugin priority
     *
     * @see Doku_Parser_Mode::getSort()
     *
     * the mode with the lowest sort number will win out
     */
    function getSort()
    {
        return 160;
    }

    /**
     * Create a pattern that will called this plugin
     *
     * @param string $mode
     * @see Doku_Parser_Mode::connectTo()
     */
    function connectTo($mode)
    {
        $pattern = PluginUtility::getLeafTagPattern(self::TAG);
        $this->Lexer->addSpecialPattern($pattern, $mode, PluginUtility::getModeForComponent($this->getPluginComponent()));
    }

    /**
     *
     * The handle function goal is to parse the matched syntax through the pattern function
     * and to return the result for use in the renderer
     * This result is always cached until the page is modified.
     * @param string $match
     * @param int $state
     * @param int $pos
     * @param Doku_Handler $handler
     * @return array|bool
     * @see DokuWiki_Syntax_Plugin::handle()
     *
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {


        $attributes = PluginUtility::getAttributes($match);
        return array($state, $attributes);


    }

    /**
     * Render the output
     * @param string $format
     * @param Doku_Renderer $renderer
     * @param array $data - what the function handle() return'ed
     * @return boolean - rendered correctly? (however, returned value is not used at the moment)
     * @see DokuWiki_Syntax_Plugin::render()
     *
     */
    function render($format, Doku_Renderer $renderer, $data)
    {
        switch ($format) {

            case 'xhtml':

                /** @var Doku_Renderer_xhtml $renderer */


                /**
                 * Disqus configuration
                 * https://help.disqus.com/en/articles/1717084-javascript-configuration-variables
                 */
                $disqusForumShortName = $this->getConf(self::FORUM_SHORT_NAME);
                if ($disqusForumShortName == "") {
                    return false;
                }
                $disqusHscForumShortName = hsc($disqusForumShortName);
                $disqusIdentifier = "disqus-test";

                /**
                 * The javascript
                 */
                $renderer->doc .= <<<EOD
<script charset="utf-8" type="text/javascript">

    // Configuration

    // The disqus_config should be a var to give it the global scope
    // Otherwise, disqus will see no config
    // noinspection ES6ConvertVarToLetConst
    var disqus_config = function () {
        this.page.identifier = "$disqusIdentifier";
        this.callbacks.onNewComment = [function(comment) {
              alert(comment.id);
              alert(comment.text);
        }];
    };

    // Embed the library
    (function() {
        const d = document, s = d.createElement('script');
        s.src = 'https://$disqusHscForumShortName.disqus.com/embed.js';
        s.setAttribute('data-timestamp', (+new Date()).toString());
        (d.head || d.body).appendChild(s);
    })();

</script>
<noscript><a href="https://disqus.com/home/discussion/$disqusForumShortName/$disqusIdentifier/">View the discussion thread.</a></noscript>
EOD;
                // The tag
                $renderer->doc .= '<div id="disqus_thread"></div>';

                return true;
                break;
            case 'metadata':

        }
        return false;

    }


}

