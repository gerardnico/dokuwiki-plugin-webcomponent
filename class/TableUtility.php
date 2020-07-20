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


class TableUtility
{

    static function render($renderer,$pos)
    {
        // initialize the row counter used for classes
        $renderer->_counter['row_counter'] = 0;
        $class = 'table';
        if ($pos !== null) {
            $sectionEditStartData = ['target' => 'table'];
            if (!defined('SEC_EDIT_PATTERN')) {
                // backwards-compatibility for Frusterick Manners (2017-02-19)
                $sectionEditStartData = 'table';
            }
            $class .= ' ' . $renderer->startSectionEdit($pos, $sectionEditStartData);
        }
        // table-responsive and
        $bootResponsiveClass = 'table-responsive';
        $bootTableClass = 'table table-hover table-striped';

        $renderer->doc .= '<div class="' . $class . ' ' . $bootResponsiveClass . '"><table class="inline ' . $bootTableClass . '">' . DOKU_LF;
    }

}
