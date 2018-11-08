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

trait DataIterator
{
    protected $iteratorIndex = 0;

    public function rewind()
    {
        $this->iteratorIndex = 0;
    }

    public function current()
    {
        $key = $this->keyOf($this->iteratorIndex);
        return $this->get($key);
    }

    public function keyOf($index = null)
    {
        if (!isset($index)) {
            $index = $this->iteratorIndex;
        }

        $keys = array_keys($this->data);
        if (isset($keys[$index])) {
            return $keys[$index];
        } else {
            return false;
        }
    }

    public function key()
    {
        return $this->keyOf($this->iteratorIndex);
    }

    public function next()
    {
        $this->iteratorIndex++;

        return $this->current();
    }

    public function valid()
    {
        return (bool)$this->key();
    }

}

