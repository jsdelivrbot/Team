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
 * File:     function.place.php
 * Type:     function
 * Name:     place
 * Purpose:  Evento hooks para colocar en lugares especificos. Ejemplo: {place name="pie"} {place name="pie2"} {place name="top"}
 * A los place se le puede incrustar vistas, widgets, contenidos, ... e incluso envolverlo en un wrapper.
 * Sólo usable desde las vistas pero se puede añadir elementos desde cualquier sitio a través de la clase \Team\Gui\Place
 * -------------------------------------------------------------
 */

function smarty_function_place($params, &$engine)
{
    $content = '';
    if (isset($params['name']) && is_string($params['name']) && !empty($params['name'])) {
        $place = $params['name'];
        unset($params['name']);

        //¿ Está el contenido de este place disponible desde la caché ?
        $cache_id = null;
        if (isset($params['cache'])) {
            $cache_id = \Team\System\Cache::checkIds($params['cache'], $place);

            $cache = \Team\System\Cache::get($cache_id);

            if (!empty($cache)) {
                return $cache;
            }
        }

        $items = \Team\Gui\Place::getItems($place);

        if (!empty($items)) {
            foreach ($items as $order => $target) {
                $func = $target['item'];
                $content = $func($content, $params, $engine);
            }
        }

        //Guardamos el contendio del place en la caché para otra vez
        if (isset($cache_id)) {
            $cache_time = $params['cachetime'] ?? null;

            \Team\System\Cache::overwrite($cache_id, $content, $cache_time);
        }
    }

    return $content;
}
