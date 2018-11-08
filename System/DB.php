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

/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 10/01/17
 * Time: 14:27
 */

namespace Team\System;

/**
 *
 * Class DB
 * @package team
 */
abstract class DB
{
    protected static $databases = [];

    public static function get($new_conection_name = null, $place = null)
    {
        $new_conection_name = self::getConnection($new_conection_name, $place);

        $DB_class = \Team\System\Context::get('\team\DB', '\Team\Db\DB', $new_conection_name);

        return new $DB_class($new_conection_name);
    }

    public static function getConnection($conname = null, $place = null)
    {
        return \Team\System\Context::get('CONNAME', $conname ?? 'main', $place);
    }

    public static function add($databaseid, array $options = null)
    {
        $databaseid_as_options = is_array($databaseid);

        if ($databaseid_as_options) {
            $options = $databaseid;
            $databaseid = null;
        }

        $databaseid = self::getConnection($databaseid, 'adding');

        $defaults = [
            'user' => 'my_user',
            'password' => 'my_password',
            'name' => 'my_db',
            'host' => 'localhost',
            'port' => '5432',
            'prefix' => '',
            'charset' => 'UTF8',
            'type' => 'mysql',
            'options' => [],
        ];

        self::$databases[$databaseid] = $options + $defaults;
    }

    public static function getConfig($conname = null)
    {
        $connection_data = self::$databases[$conname] ?? [];

        return \Team\Data\Filter::apply('\team\db\\' . $conname, $connection_data, $conname);
    }
}