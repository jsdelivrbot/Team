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
 * Show select elements
 * -------------------------------------------------------------
 * File:     function.select.php
 * Type:     function
 * Name:     Team
 * Purpose:   tools smarty
 * -------------------------------------------------------------
 */

function smarty_function_select($params, &$smarty)
{
    $name = $params['name'] ?? '';
    $multiple = $params['multiple'] ?? '';
    $class = $params['class'] ?? '';
    $id = $params['id'] ?? '';
    $values = $params['values'] ?? [];
    $selected = $params['selected'] ?? '';

    $out = "<SELECT name='{$name}' ";
    if ($class) {
        $out .= "class='{$class}' ";
    }
    if ($id) {
        $out .= "id='{$id}' ";
    }
    if ($multiple) {
        $out .= "multiple='multiple'";
    }
    $out .= ">";

    if (!empty($values)) {
        foreach ($values as $key => $label) {
            $out .= "<option name='{$key}' ";
            if ($key == $selected) {
                $out .= "selected='selected'";
            }
            $out .= ">{$label}</option>";
        }
    }
    $out .= "</select>";

    return $out;
}

