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

namespace Team\Db;

trait Database
{

    static function getNewQuery($values = null, array $sentences = [], $name_new_conection = null)
    {
        return new Query($values, \Team\System\DB::get($name_new_conection, static::class), $sentences);
    }

    protected function getDatabase($name_new_conection = null)
    {
        return \Team\System\DB::get($name_new_conection, get_class($this));
    }

    protected function newQuery($values = null, array $sentences = [], $name_new_conection = null)
    {
        return new Query($values, $this->getDatabase($name_new_conection), $sentences);
    }
}
