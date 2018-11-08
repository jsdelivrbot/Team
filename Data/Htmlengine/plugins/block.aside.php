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
 * File:     block.aside
 * Type:     block
 * Name:     aside
 * Purpose:  output a aside tag
 * -------------------------------------------------------------
 */

function smarty_block_aside($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    $controller = \Team\System\Context::get('CONTROLLER');

    // por defecto los aside no se muestran en móviles. Si se quiere visualizar usar:   {aside mobile=true}..{/aside}
    $mobile = false;
    if (isset($params['mobile'])) {
        $mobile = (bool)$params['mobile'];
        unset($params['mobile']);
    }

    if (!$mobile && $controller) {
        $mobile = \Team\Client\Http::checkUserAgent('mobile');

        if ($mobile) {
            $repeat = false;
            return '';
        }
    }

    if ($repeat) { //open tag
        $out = '<aside';

        foreach ($params as $attr => $value) {
            $out .= " {$attr}='{$value}'";
        }
        $out .= '>';
    } else {//close tag
        $content = \Team\Data\Filter::apply('\team\tag\aside', $content);

        $out = trim($content) . '</aside>';
    }

    return $out;
}
