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


class PipelineUtility
{

    /**
     * @param $input
     * @return string
     */
    static public function execute($input){
        $commands = preg_split("/\|/",$input);

        /**
         * Get the value
         */
        $value = trim($commands[0]," \"");
        unset($commands[0]);

        foreach ($commands as $command){
            $command = trim($command, " )");
            $leftParenthesis = strpos($command, "(");
            $commandName = substr($command, 0, $leftParenthesis);
            $signature = substr($command, $leftParenthesis+1);
            $commandArgs = preg_split("/,/",$signature);
            $commandArgs = array_map(
                'trim',
                $commandArgs,
                array_fill(0,sizeof($commandArgs),"\"")
            );
            switch ($commandName){
                case "replace":
                    $value = self::replace($commandArgs,$value);
                    break;
                default:
                    LogUtility::msg("command ($commandName) is unknown",LogUtility::LVL_MSG_ERROR,"pipeline");
            }
        }
        return $value;
    }

    private static function replace(array $commandArgs, $value)
    {
        $search = $commandArgs[0];
        $replace = $commandArgs[1];
        return str_replace($search,$replace,$value);
    }

}
