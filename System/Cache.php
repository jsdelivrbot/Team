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

namespace Team\System;

\Team\Loader\Classes::load('\Team\Predefined\Apcu', '/Predefined/Apcu.php', _TEAM_);

class Cache
{
    /** Current cache system */
    private static $current = null;

    /**
     * Preparamos el sistema de caché
     */
    public static function __initialize()
    {
        if (isset(self::$current)) {
            return;
        }

        $cache_class = \Team\System\Context::get('\team\Cache', '\Team\Predefined\Apcu');

        if (isset($cache_class) && class_exists($cache_class)) {
            self::$current = new  $cache_class();
        }
    }

    /**
     * Va verificando uno por uno todos los ids pasados y devuelve el primero de ellos
     * que sea valido como identificador de caché.
     *
     * @param  $ids una lista de identificadores de cachés
     * @return mixed|null|string
     *
     */
    public static function checkIds(...$ids)
    {
        if (empty($ids)) {
            return null;
        }

        foreach ($ids as $cache_id) {
            if (!is_string($cache_id)) {
                continue;
            }

            $new_id = \Team\Data\Sanitize::identifier($cache_id);
            $new_id = trim(trim($new_id, '_'));

            if (!empty($new_id)) {
                return $new_id;
            }
        }

        return null;
    }

    public static function delete($cache_id)
    {
        $cache_id = self::wrapperId($cache_id);

        return self::$current->delete($cache_id);
    }

    public static function wrapperId($cache_id)
    {
        $script_id = \Team\System\Context::get('SCRIPT_ID');
        return $script_id . '_' . $cache_id;
    }

    //Borramos un elemento de la caché

    public static function clear()
    {
        return self::$current->clear();
    }

    public static function save($cache_id, $value, $time = 0)
    {
        $cache_id = self::wrapperId($cache_id);

        return self::$current->save($cache_id, $value, self::checkTime($time, $cache_id));
    }

    /**
     * Comprueba que time sea una duración de tiempo valida.
     * @param $time Es la duración de tiempo del cache. Esta duración puede ser::
     * - 0: el cache estará habilitado de forma indefinida
     * - [int]: número de segundos que durará.
     * - [string]: cadena con tiempo en forma humana: 3 hours, 1 week, 10 minutes, ...
     * - null: se toma por filtro el valor por defecto de tiempo
     * @param $cache_id : Es el identificador de caché sobre el que se aplicará la duración de tiempo( $time )
     * @return array|int|string
     */
    public static function checkTime($time, $cache_id)
    {
        $cache_id = self::wrapperId($cache_id);

        $time = \Team\System\Date::strToTime($time);

        if (is_null($time)) {
            return \Team\Data\Filter::apply('\team\cache\default_time', \Team\System\Date::AN_HOUR, $cache_id);
        }

        return $time;
    }

    public static function overwrite($cache_id, $value, $time = 0)
    {
        $cache_id = self::wrapperId($cache_id);

        return self::$current->overwrite($cache_id, $value, self::checkTime($time, $cache_id));
    }

    public static function exists($cache_id)
    {
        $cache_id = self::wrapperId($cache_id);

        return self::$current->exists($cache_id);
    }

    public static function get($cache_id, $default = null)
    {
        $cache_id = self::wrapperId($cache_id);

        return \Team\Data\Filter::apply('\team\cache\\' . $cache_id, self::$current->get($cache_id, $default));
    }

    public static function debug($msg = null)
    {
        self::$current->debug($msg);
    }

    public static function __callStatic($func, $args)
    {
        return call_user_func_array([self::$current, $func], $args);
    }

    function __call($func, $args)
    {
        return call_user_func_array([self::$current, $func], $args);
    }

}
