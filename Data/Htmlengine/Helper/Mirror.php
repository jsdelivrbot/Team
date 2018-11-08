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

namespace Team\Data\Htmlengine\Helper;

/**
 * Se encarga de llamar a un método del Controller actual.
 */
class Mirror
{

    static function __callStatic($name, $params)
    {
        if (0 === strpos($name, 'mirror_')) {
            $name = substr($name, 7);
        }

        $namespace = \Team\System\Context::get('NAMESPACE');
        \team\Debug::me("[{$namespace}][Template]:Not found a replacement for '{$name}'  ");
    }
}
