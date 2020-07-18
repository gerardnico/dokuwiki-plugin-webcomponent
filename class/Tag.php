<?php
/**
 * Copyright (c) 2020. ComboStrap, Inc. and its affiliates. All Rights Reserved.
 *
 * This source code is licensed under the GPL license found in the
 * COPYING  file in the root directory of this source tree.
 *
 * @license  GPL 3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 * @author   ComboStrap <support@combostrap.com>
 *
 */

namespace ComboStrap;


use Doku_Handler;

class Tag
{
    private $calls;
    private $attributes;
    private $name;
    private $state;


    /**
     * Token constructor
     * A token represent a call of {@link \Doku_Handler}
     * It can be seen as a the counter part of the HTML tag.
     *
     * It has a state of:
     *   * {@link DOKU_LEXER_ENTER} (open),
     *   * {@link DOKU_LEXER_UNMATCHED} (unmatched content),
     *   * {@link DOKU_LEXER_EXIT} (closed)
     *
     * @param $name
     * @param $attributes
     * @param $state
     * @param $calls - The dokuwiki call stack - ie {@link Doku_Handler->calls} or a subset
     */
    public function __construct($name, $attributes, $state, $calls)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->state = $state;
        $this->calls = $calls;
    }

    private static function getMatchFromCall($call)
    {
        return $call[1][3];
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * From a call to a node
     * @param $call
     * @param $position - the position in the call stack
     * @return Tag
     */
    private function call2Node($call, $position)
    {

        $attributes = $call[1][1][PluginUtility::ATTRIBUTES];
        /**
         * If we don't have already the attributes
         * in the returned array of the handler,
         * (ie the full HTML was given for instance)
         * we parse the match again
         */
        if ($attributes == null) {
            $match = self::getMatchFromCall($call);
            $attributes = PluginUtility::getTagAttributes($match);
        }
        $name = self::getTagName($call);
        $calls = array_slice($this->calls, 0, $position); // reduce the call stack
        $state = self::getStateFromCall($call);
        return new Tag($name, $attributes, $state, $calls);
    }

    /**
     * The parser state
     * @param $call
     * @return mixed
     */
    private static function getStateFromCall($call)
    {
        return $call[1][2];
    }

    public function isChildOf($tag)
    {
        $componentNode = $this->getParent();
        return $componentNode !== false ? $componentNode->getName() === $tag : false;
    }

    /**
     * To determine if there is no content
     * between the child and the parent
     * @return bool
     */
    public function hasSiblings()
    {
        $counter = sizeof($this->calls);
        while ($counter > 0) {

            $call = $this->calls[$counter - 1];
            if ($call[0] == "eol") {
                $counter = $counter - 1;
                continue;
            } else {
                break;
            }

        }
        if (isset($call)) {
            if ($call[1][2] == DOKU_LEXER_ENTER) {
                return false;
            } else {
                return true;
            }
        }
        return false;

    }

    /**
     * Return the parent node or false if root
     * @return bool|Tag
     */
    public function getParent()
    {
        $descendantCounter = sizeof($this->calls) - 1;
        $treeLevel = 0;
        $i = 0;
        while ($descendantCounter > 0) {

            $parentCall = $this->calls[$descendantCounter];

            $parentCallName = $parentCall[0];
            $state = self::getStateFromCall($parentCall);

            /**
             * Case when we start from the same element
             * We put -1 in the level to not get the start tag
             */
            $i++;
            if ($i == 1 && self::getTagName($parentCall) == $this->name) {
                $treeLevel = +1;
            } else {
                // No sibling
                if ($state == DOKU_LEXER_EXIT) {
                    $treeLevel = +1;
                }
            }

            if ($parentCallName == "eol" || $state != DOKU_LEXER_ENTER || $treeLevel != 0) {
                $descendantCounter = $descendantCounter - 1;
                unset($parentCall);
            } else {
                break;
            }

            // After the condition, otherwise a sibling would become a parent
            // on its enter state
            if ($state == DOKU_LEXER_ENTER) {
                $treeLevel = $treeLevel - 1;
            }

        }
        if (isset($parentCall)) {
            return $this->call2Node($parentCall, $descendantCounter);
        } else {
            return false;
        }
    }

    /**
     * Return an attribute of the node
     * @param string $name
     * @return string the attribute value
     */
    public function getAttribute($name)
    {
        return $this->attributes[$name];
    }

    /**
     * @return mixed - the name of the element (ie the opening tag)
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the tag name from a call array
     * @param $call
     * @return mixed|string
     */
    static function getTagName($call)
    {
        $component = $call[1][0];
        $componentNames = explode("_", $component);
        return $componentNames[sizeof($componentNames) - 1];
    }


    /**
     * @return string - the type attribute of the opening tag
     */
    public function getType()
    {
        if ($this->getState() == DOKU_LEXER_UNMATCHED) {
            return $this->getOpeningTag()->getType();
        } else {
            return $this->getAttribute("type");
        }
    }

    /**
     * @param $tag
     * @return int
     */
    public function isDescendantOf($tag)
    {

        for ($i = sizeof($this->calls) - 1; $i >= 0; $i--) {
            if (self::getTagName($this->calls[$i]) == "$tag") {
                return true;
            }

        }
        return false;

    }

    public function getFirstSibling()
    {
        $counter = sizeof($this->calls);
        while ($counter > 0) {
            $parentCall = $this->calls[$counter - 1];
            if ($parentCall[0] == "eol" ||
                $this->getName() == self::getTagName($parentCall) // In case of an unmatched tag, we may be still in the same element
            ) {
                $counter = $counter - 1;
                unset($parentCall);
            } else {
                break;
            }
        }
        if (isset($parentCall)) {
            return self::call2Node($parentCall, $counter);
        } else {
            return false;
        }

    }

    public function hasParent()
    {
        return $this->getParent() !== false;
    }

    public function getOpeningTag()
    {
        $descendantCounter = sizeof($this->calls) - 1;
        while ($descendantCounter > 0) {

            $parentCall = $this->calls[$descendantCounter];
            $parentTagName = self::getTagName($parentCall);
            $state = self::getStateFromCall($parentCall);
            if ($state === DOKU_LEXER_ENTER && $parentTagName === $this->getName()) {
                break;
            } else {
                $descendantCounter = $descendantCounter - 1;
                unset($parentCall);
            }

        }
        if (isset($parentCall)) {
            return $this->call2Node($parentCall, $descendantCounter);
        } else {
            return false;
        }
    }

}
