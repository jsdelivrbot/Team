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
 * Name:     desktop
 * Purpose:  output a content only if browser is a desktop user agent( computer or tablet )
 * -------------------------------------------------------------
 * {desktop}Hello, World!{/desktop}
 */

function smarty_block_desktop($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    if (!\team\client\Http::checkUserAgent('desktop')) {
        $repeat = false;
        return '';
    }

    return $content;
}
