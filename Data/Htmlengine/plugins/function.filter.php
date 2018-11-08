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
 * File:     function.filter.php
 * Type:     function
 * Name:     filter
 * Purpose:  Filtro hooks para filtrar contenido. Ejemplos:
{filter name='\gui\breadscrumb' value=[] assign='breadscrumb'}
{filter name='\gui\menu' value=[] assign='menu'}
{filter name='\gui\title' value='Web Title' assign='title'}

	Se diferencia a place, en que place es un evento( y los awaiters pueden generar contenido o hacer cualquier tipo de operación ) y filter es un filtro( con lo que puede ser usado para generar contenido o para asignar valores a variables )
 * -------------------------------------------------------------
 */

function smarty_function_filter($params, &$smarty)
{
    if (isset($params['name'])) {
        $name = $params["name"];

        $value = null;
        if (isset($params['value'])) {
            $value = $params['value'];
            unset($params['value']);
        }

        $assign = null;
        if (isset($params['assign'])) {
            $assign = $params['assign'];
            unset($params['assign']);
        }

        $out = \Team\Data\Filter::apply($name, $value, $params, $smarty);

        if (isset($assign)) {
            $smarty->assign($assign, $out);
        } else {
            return $out;
        }
    }
    return "";
}
