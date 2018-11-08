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
 * Date: 11/01/17
 * Time: 9:11
 */

namespace Team\Data\Type;

abstract class Base implements \ArrayAccess
{
    use \Team\Data\Box;

    protected $contexts = [];

    /**** FORMATS ****/
    public function __toString()
    {
        return self::out('Html');
    }

    public function out($type = null, $options = [], $isolate = true)
    {
        \Team\System\Context::open($isolate);

        \Team\System\Context::set($this->contexts);

        $type = \Team\Data\Check::key($type);
        if (!isset($type) && isset($this->out)) {
            $type = \Team\Data\Check::key($this->get("out"), "Array");

            unset($this->out);
        }

        $out = \Team\Data\Filter::apply('\team\data\format\\' . $type, $this->data, $options);

        \Team\System\Context::close();

        return $out;
    }

    public function getContext($context, $default = null)
    {
        return $this->contexts[$context] ?? $default;
    }

    public function setContext($context, $value)
    {
        $this->contexts[$context] = $value;
        return $this;
    }

}