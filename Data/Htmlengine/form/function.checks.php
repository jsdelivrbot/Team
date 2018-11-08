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
 * Show check elements
 * -------------------------------------------------------------
 * File:     function.check.php
 * Type:     function
 * Name:     Team
 * Purpose:   tools smarty
 * Example:   {checks name='form[vehiculos][]' layout='<div class="form-control"><p><label>:INPUT :LABEL</label></p></div>' values=$transportista->PesosVehiculos}
 * -------------------------------------------------------------
 */

function smarty_function_checks($params, &$smarty)
{
    $name = $params['name'] ?? '';
    $class = $params['class'] ?? '';
    $values = $params['values'] ?? [];
    $checked = $params['checked'] ?? [];
    $layout = $params['layout'] ?? '';

    if (empty($values)) {
        return '';
    }

    $out = '';
    $i = 1;
    foreach ($values as $key => $label) {
        $is_checked = (in_array($key, $checked, $strict = true)) ? 'checked="checked"' : '';

        $input = '<input type="checkbox" name="' . $name . ' class="' . $class . '"  value="' . $key . '"  ' . $is_checked . ' />';

        $new_check = $layout;
        $new_check = str_replace(':ID', \Team\Data\Sanitize::identifier($label), $new_check);
        $new_check = str_replace(':POS', $i, $new_check);
        $new_check = str_replace(':LABEL', $label, $new_check);
        $new_check = str_replace(':INPUT', $input, $new_check);

        $out .= $new_check;
        $i++;
    }

    return $out;
}

