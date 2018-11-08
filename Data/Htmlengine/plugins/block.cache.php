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
 * File:     block.cache
 * Type:     block
 * Name:     cache
 * Purpose:  output a content cached or save in cache it
 * -------------------------------------------------------------
 * {cache id='hello_world' time='+1 Week'}Hello, World!{/cache}
 */

function smarty_block_cache($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    $cache_id = null;
    if (isset($params['id'])) {
        $cache_id = \Team\System\Cache::checkIds($params['id']);

        if (!$cache_id) {
            return $content;
        }
    } else {
        return $content;
    }

    if ($repeat) { //Al inicio de la etiqueta

        $cache = \Team\System\Cache::get($cache_id);

        if (!empty($cache)) {
            $repeat = false; //No hace falta que se proceso lo interior
            return $cache;
        }
        return $content;
    }

    $cache_time = $params['time'] ?? null;

    \Team\System\Cache::overwrite($cache_id, $content, $cache_time);

    return $content;
}
