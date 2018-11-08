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
 * File:     smarty_block_layer
 * Type:     block
 * Name:     layer
 * Purpose:  output a layer "tag"
 * -------------------------------------------------------------
 */

function smarty_block_layer($params, $content, Smarty_Internal_Template $engine, &$repeat)
{
    $place = '';
    if (isset($params['place'])) {
        $place = $params['place'];
        unset($params['place']);
    }

    $out = '';
    if ($repeat) { //open tag
        $out = '<div';

        $params['class'] = $params['class'] ?? '';

        $params['class'] = \Team\Gui\Place::getClasses($place, $params['class']);

        if (empty($params['class'])) {
            unset($params['class']);
        }

        foreach ($params as $attr => $value) {
            $out .= " {$attr}='{$value}'";
        }

        $out .= '>';
    } else {//close tag
        $content = \Team\Gui\Place::getHtml($place, $content, $params, $engine);
        $out = $content . '</div>';
    }

    return $out;
}
