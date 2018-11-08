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
 * File:     block.desktop
 * Type:     block
 * Name:     display
 * Purpose:  output a content only if browser match with selected user agent thought params
 * -------------------------------------------------------------
 * {display desktop=true mobile=true tablet=false}Hello, World!{/display}
 * showing Hello World in desktop and mobiles but not in tablet
 */

function smarty_block_display($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    $params += ['desktop' => false, 'mobile' => false, 'tablet' => false];

    extract($params, EXTR_SKIP);

    extract(\team\client\Http::checkUserAgent(), EXTR_PREFIX_ALL, 'is');

    if (($desktop && $is_desktop) || ($mobile && $is_mobile) || ($tablet && $is_tablet)) {
        return $content;
    } else {
        $repeat = false;
        return '';
    }
}
