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
 * File:     block.highlight
 * Type:     block
 * Name:     highlight
 * Purpose:  output a content with highlight
 * -------------------------------------------------------------
 * {hightlight}echo "Hola mundo";{/hightlight}
 */

function smarty_block_highlight($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    if ($repeat) {
        return $content;
    }

    return highlight_string('<?php ' . $content, true);
}
