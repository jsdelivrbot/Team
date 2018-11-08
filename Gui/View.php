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
 * Time: 18:22
 */

namespace Team\Gui;

trait View
{

    public function getView($_file, $component = null, $app = null)
    {
        //Eliminamos la extensión( ya que eso depende del sistema de render escogido )
        $file = \Team\System\FileSystem::stripExtension($_file);

        //Es un resource
        if (strpos($_file, ':')) {
            return $file;
        }

        if (empty($file)) {
            $file = $this->getContext('RESPONSE');
        }

        $file = \Team\System\FileSystem::getPath("views", $component, $app) . "{$file}";

        if (\Team\System\FileSystem::filename('/' . $file)) {
            return $file;
        } else {
            if (\team\Config::get('SHOW_RESOURCES_WARNINGS', false)) {
                \Team\Debug::me("View {$file}[{$_file}] not found in {$app}/{$component}", 3);
                return null;
            }
        }
    }

    public function setView($view, $place = 'component')
    {
        $this->setContext('VIEW', $place . ":" . $view);

        return $view;
    }

    public function noLayout()
    {
        $this->setLayout();
    }

    public function setLayout($layout = null, $place = 'app')
    {
        if (!isset($layout)) {
            $this->setContext('LAYOUT', null);
        } else {
            $this->setContext('LAYOUT', $place . ":" . $layout);
        }
    }

}