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
 * File:     function.widget.php
 * Type:     function
 * Name:     widget
 * Purpose:  output content of an response ( if 'assign' exists then put content in it )
 * -------------------------------------------------------------
 * IMPORTANMTE:Ojo, usar comillas simples en la plantilla para el name. Sino podría haber colisión con las secuencias de escape. Ejemplo:
 * {widget name="\widgets\news"} -> se interpretan \w y \n como secuencias de escape, mejor poner:  {widget name='\widgets\news'}
 */

function smarty_function_widget($params = [], &$smarty)
{
    //Si no existe name, salimos.
    if (!isset($params['name'])) {
        return '';
    }

    $params['embedded'] = true;
    $params['engine'] = $smarty;
    $params['cache'] = $params['cache'] ?? null;

    $widget_content = \Team\Builder\Component::call($params['name'], $params, $params['cache']);

    //Si se paso un parametro assign, se le asigna el resultado ahi
    if (isset($params['assign']) && !empty($params['assign'])) {
        $var = $params['assign'];
        $smarty->assign($var, $widget_content);
        return '';
    } else {
        return $widget_content;
    }
}
