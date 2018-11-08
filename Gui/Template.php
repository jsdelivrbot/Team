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
 * Date: 13/01/17
 * Time: 18:29
 */

namespace Team\Gui;

class Template extends \Team\Data\Type\Type
{
    use \Team\Gui\View;

    protected $contexts = [];

    public function initialize($view = null, array $contexts = [])
    {
        $this->contexts = $contexts;
        $this->setContext('VIEW', $view);
    }

    public function fromString($content = null)
    {
        $this->setContext('VIEW', $content ?? $this->getContext('VIEW'));
        $this->setContext('LAYOUT', 'string');
    }

    public function getHtml($isolate = true, array $options = [])
    {
        return $this->out("html", $options, $isolate);
    }
}