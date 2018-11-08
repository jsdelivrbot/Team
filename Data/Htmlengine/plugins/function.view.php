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

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.view.php
 * Type:     function
 * Name:     view
 * Purpose:  Visualiza la plantilla principal
 * -------------------------------------------------------------
 */

function smarty_function_view($params, &$engine)
{
    $place = '';
    if (isset($params['place'])) {
        $place = $params['place'];
        unset($params['place']);
    }

    $father = $engine;
    $view = $params['name'] ?? \Team\System\Context::get('VIEW');
    $idView = \Team\Data\Sanitize::identifier($view);
    $template = $engine->createTemplate($view . '.tpl', $idView, $idView, $father);
    $template->assign($params);

    $content = $template->fetch();

    return \Team\Gui\Place::getHtml($place, $content, $params, $engine);
}
