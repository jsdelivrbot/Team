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
 * File:     function.notices.php
 * Type:     function
 * Name:     widget
 * Purpose:  output notices
 * -------------------------------------------------------------
 */

function smarty_function_notices($params, &$smarty)
{
    if (\Team::checkOk()) {
        $type = "success";
        $more = \Team::getInfos();
        $msg = \Team::getSuccess();
    } else {
        $type = "error";
        $more = \Team::getWarnings();
        $msg = \Team::getError();
    }

    echo "<div class='notices {$type}'>";
    echo "<strong>{$smsg}</strong>" .
	echo '<div class="details">';
		echo '<ul>';
		foreach ($more as $notice => $msg) {
            echo '<li>' . $msg . '</li>';
        }
		echo '</ul>';
	echo '</div>';

	echo '</div>';	

}
