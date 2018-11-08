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
 * Date: 11/01/17
 * Time: 9:11
 */

namespace Team\Data\Type;

abstract class Type extends Base
{

    /** Debería de ser al reves */
    public function __construct($_origin = null, array $_options = [], $_defaults = [])
    {
        //Check if implements Box instead
        if ($_defaults instanceof \Team\Data\Type\Base) {
            $this->data = $_defaults->get();
        } else {
            if (is_array($_defaults)) {
                $this->data = $_defaults;
            }
        }

        $this->initialize($_origin, $_options);
    }

    protected function initialize($_origin = null, array $_options = [])
    {
    }

}