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

class TestConstant
{

    /**
     * @var string
     */
    public static $DIR_RESOURCES;

    static function init()
    {
        TestConstant::$DIR_RESOURCES = __DIR__ . '/resources';
    }
}

TestConstant::init();

