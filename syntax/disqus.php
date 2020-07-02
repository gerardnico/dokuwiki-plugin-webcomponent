<?php

require_once(__DIR__ . '/../webcomponent.php');

/**
 * Disqus integration
 */
class syntax_plugin_webcomponent_disqus extends DokuWiki_Syntax_Plugin
{

    const FORUM_SHORT_NAME = 'forumShortName';

    /**
     *
     * @return mixed|string - The tag (ie disqus)
     */
    private static function getTag()
    {
        list(/* $t */, /* $p */, /* $n */, $c) = explode('_', get_called_class(), 4);
        return (isset($c) ? $c : '');
    }

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
        $pattern = webcomponent::getLeafTagPattern(self::getTag());
        $this->Lexer->addSpecialPattern($pattern, $mode, 'plugin_' . webcomponent::PLUGIN_NAME . '_' . $this->getPluginComponent());
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
        switch ($state) {

            case DOKU_LEXER_SPECIAL:

                // Suppress the </>
                $match = substr($match, 1, -2);
                // Suppress the tag name
                $match = str_replace(self::getTag(), "", $match);
                // Get the parameters
                $parameters = webcomponent::parseMatch($match);
                return array($state, $parameters);


        }

        return array();

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
        if ($format == 'xhtml') {

            /** @var Doku_Renderer_xhtml $renderer */
            list($state, $parameters) = $data;
            switch ($state) {

                case DOKU_LEXER_ENTER :

                    global $INFO;

                    /**
                     * Disqus configuration
                     * https://help.disqus.com/en/articles/1717084-javascript-configuration-variables
                     */
                    $disqusForumShortName = $this->getConf(self::FORUM_SHORT_NAME);
                    if ($disqusForumShortName == ""){
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
                    break;

                case DOKU_LEXER_EXIT :
                    $renderer->doc .= '' . DOKU_LF;
                    break;
            }
            return true;
        }
        return false;

    }


}

