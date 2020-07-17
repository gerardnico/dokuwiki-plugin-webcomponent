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


class ComponentNode
{
    private $calls;
    private $attributes;
    private $name;


    /**
     * ComponentTree constructor.
     * @param $name
     * @param $attributes
     * @param $calls
     */
    public function __construct($name, $attributes, $calls)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->calls = $calls;
    }

    /**
     * From a call to a node
     * @param $call
     * @param $position - the position in the call stack
     * @return ComponentNode
     */
    private function call2Node($call, $position)
    {
        $attributes = $call[1][1][PluginUtility::ATTRIBUTES];
        $name = self::getTagName($call);
        $calls = array_slice($this->calls, 0, $position);
        return new ComponentNode($name, $attributes, $calls);
    }

    /**
     * The parser state
     * @param $call
     * @return mixed
     */
    private static function getState($call)
    {
        return $call[1][2];
    }

    public function isChildOf($tag)
    {
        return $this->getParent()->getName() === $tag;
    }

    /**
     * To determine if there is no content
     * between the child and the parent
     * @return bool
     */
    public function hasSiblings()
    {
        $counter = sizeof($this->calls);
        while ($counter>0) {

            $call = $this->calls[$counter - 1];
            if ($call[0]=="eol"){
                $counter = $counter -1;
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
     * @return bool|ComponentNode
     */
    public function getParent()
    {
        $counter = sizeof($this->calls);
        $treeLevel = 0;
        while ($counter > 0) {

            $parentCall = $this->calls[$counter - 1];
            $parentName = $parentCall[0];
            $state = self::getState($parentCall);

            // No sibling
            if ($state == DOKU_LEXER_EXIT ){
                $treeLevel =+ 1;
            }

            if ($parentName == "eol" || $state != DOKU_LEXER_ENTER || $treeLevel != 0) {
                $counter = $counter - 1;
                unset($parentCall);
            } else {
                break;
            }

            // After the condition, otherwise a sibling would become a parent
            // on its enter state
            if ($state == DOKU_LEXER_ENTER ){
                $treeLevel = $treeLevel - 1;
            }

        }
        if (isset($parentCall)) {
            return $this->call2Node($parentCall,$counter);
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


    public function getType()
    {
        return $this->getAttribute("type");
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
            if ($parentCall[0] == "eol") {
                $counter = $counter - 1;
                unset($parentCall);
            } else {
                break;
            }
        }
        if (isset($parentCall)) {
            return self::call2Node($parentCall,$counter);
        } else {
            return false;
        }

    }

}
