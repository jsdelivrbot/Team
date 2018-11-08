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

namespace Team\Data;

/**
 * Remember using: implements \ArrayAccess  in your class definition
 */

trait DataArrayAccess
{

    public function offsetUnset($offset)
    {
        if (!isset($offset)) {
            $this->data = [];
        }
        if (array_key_exists($offset, $this->data)) {
            unset($this->data[$offset]);
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function &  offsetGet($offset)
    {
        if (!isset($this->data[$offset])) {
            $this->data[$offset] = null;
        }

        return $this->data[$offset];
    }

    public function & offsetSet($offset, $valor)
    {
        if (is_null($offset)) {
            return $this->data[] = $valor;
        } else {
            $this->data[$offset] = $valor;
        }

        return $this->data[$offset];
    }
}