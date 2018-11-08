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
 * File:     block.admin
 * Type:     block
 * Name:     logged
 * Purpose:  output a content only if user is logged
 * -------------------------------------------------------------
 * {logged}Hello, World!{/logged}
 */

function smarty_block_logged($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    if (!\team\User::isLogged($params)) {
        $repeat = false;
        return '';
    }

    return $content;
}
