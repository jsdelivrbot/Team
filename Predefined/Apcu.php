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

namespace Team\Predefined;

class Apcu
{

    //Borramos un elemento de la caché
    function delete($key)
    {
        return apcu_delete($key);
    }

    function clear()
    {
        return apcu_clear_cache();
    }

    function overwrite($key, $value, $time = 0)
    {
        return apcu_store($key, $value, $time);
    }

    function save($key, $value, $time = 0)
    {
        return apcu_add($key, $value, $time);
    }

    function exists($key)
    {
        return apcu_exists($key);
    }

    function get($key, $default = null)
    {
        return apcu_fetch($key) ?? $default;
    }

    function debug($msg = null)
    {
        \Debug::me(apcu_cache_info('user'), $msg);
    }
}
