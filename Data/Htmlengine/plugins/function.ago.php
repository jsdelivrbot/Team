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
 * File:     function.ago.php
 * Type:     function
 * Name:     ago
 * Purpose:  show diff between current date and a date
 * -------------------------------------------------------------
 */

function smarty_function_ago($params, &$smarty)
{
    //Procesamos parámetros
    if (!isset($params['date'])) {
        return '';
    }
    $date = $params['date'];
    $type = $params['type'] ?? null;
    $depth = $params['depth'] ?? 2;
    $ago = $params['ago'] ?? 'Hace';
    $in = $params['in'] ?? 'Dentro de';
    $and = $params['and'] ?? 'y';

    //Procesamos la hora
    $diff = \Team\System\Date::diff($date, $type);

    //Si no ha diff, salimos sin más
    if (empty($diff['units']) || empty($diff['diff'])) {
        return '';
    }

    //procesamos la salida
    $out = ($diff['diff'] > 0) ? $in : $ago;

    foreach ($diff['units'] as $label => $count) {
        $out .= " {$count} {$label}";
        $depth--;
        if ($depth <= 0) {
            break;
        }

        //Si sólo hay una unidad no tiene sentido que se añada una conjunción
        if (count($diff['units']) > 1) {
            $out .= ($depth == 1) ? ' ' . $and : ', ';
        }
    }

    return $out;
}
