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
 * Date: 5/01/17
 * Time: 16:51
 */

namespace Team\Data\Htmlengine\Helper;

class Config implements \ArrayAccess
{
    /* ------------------- ArrayAccess  ---------------------- */

    public function offsetUnset($offset)
    {
        return \Team\System\Context::delete($offset);
    }

    public function offsetExists($offset)
    {
        return true;
    }

    public function offsetGet($offset)
    {
        return \Team\System\Context::get($offset, '');
    }

    public function offsetSet($offset, $valor)
    {
        return \Team\System\Context::get($offset, $valor);
    }

}