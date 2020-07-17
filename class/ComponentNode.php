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
     * @param $attributes
     * @param $calls
     */
    public function __construct($name, $attributes, $calls)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->calls = $calls;
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

        $call = $this->calls[sizeof($this->calls) - 1];
        if ($call[1][2] == DOKU_LEXER_ENTER) {
            return 0;
        } else {
            return 1;
        }

    }

    /**
     * Return the parent node or false if root
     * @return bool|ComponentNode
     */
    public function getParent()
    {
        if (sizeof($this->calls) > 0) {
            $parentCall = $this->calls[sizeof($this->calls) - 1];
            $attributes = $parentCall[1][1][PluginUtility::ATTRIBUTES];
            $name = self::getTagName($parentCall);
            $calls = array_slice($this->calls, 0, sizeof($this->calls) - 1);
            return new ComponentNode($name, $attributes, $calls);
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

    public static function getParentTag(\Doku_Handler $handler)
    {
        $callsCount = sizeof($handler->calls);

        for ($i = $callsCount - 1; $i >= 0; $i--) {
            $call = $handler->calls[$i];
            if ($call[0] == "plugin") {
                if ($call[1][2] == DOKU_LEXER_ENTER) {
                    $parent = $call;
                    break;
                }
            }
        }

    }

    public function getType()
    {
        return $this->attributes["type"];
    }

}
