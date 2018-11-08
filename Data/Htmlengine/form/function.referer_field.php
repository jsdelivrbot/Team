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
 * Added an referer field in an form
 * -------------------------------------------------------------
 * File:     function.referer_field.php
 * Type:     function
 * Name:     Team
 * Purpose:   tools smarty
 * -------------------------------------------------------------
 */

function smarty_function_referer_field($params, &$smarty)
{
    $referer_url = \Team\System\Context::get('WEB');

    \Team\System\Context::debug();
    \Debug::out($referer_url);

    $referer_field = '<input type="hidden" name="_http_referer" value="' . base64_encode() . '" />';

    return $referer_field;
}
