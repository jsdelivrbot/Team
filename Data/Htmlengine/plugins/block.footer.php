<?php
/**
 * This file is part of TEAM.
 *
 * TEAM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, in version 2 of the License.
 *
 * TEAM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TEAM.  If not, see <https://www.gnu.org/licenses/>.
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:      block.footer
 * Type:     block
 * Name:     footer
 * Purpose:  output a footer tag
 * -------------------------------------------------------------
 */

function smarty_block_footer($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    if ($repeat) { //open tag
        $out = '<footer';

        foreach ($params as $attr => $value) {
            $out .= " {$attr}='{$value}'";
        }
        $out .= '>';
    } else {//close tag
        $content = \Team\Data\Filter::apply('\team\tag\footer', $content);

        $out = trim($content) . '</footer>';
    }

    return $out;
}
