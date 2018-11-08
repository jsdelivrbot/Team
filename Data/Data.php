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

namespace Team\Data;

require_once(__DIR__ . '/Type/Base.php');

class Data extends \Team\Data\Type\Base
{

    public function __construct($data = [])
    {
        //Check if implements Box instead
        if ($data instanceof \Team\Data\Type\Base) {
            $this->data = $data->get();
        } else {
            if (is_array($data)) {
                $this->data = $data;
            }
        }
    }

}
