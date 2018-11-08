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

namespace Team\Db;

require_once(__DIR__ . '/DB.php');

class Query implements \ArrayAccess
{
    use \Team\Data\Box; //En data guardamos las sentencias sql

    use \Team\Db\Queries\Select;
    use \Team\Db\Queries\Update;
    use \Team\Db\Queries\Delete;
    use \Team\Db\Queries\Insert;

    /**
     * guarda los campos y valores a reemplazar. Es decir, los valores.
     * ej: "web" -> "http://trasweb.net"  insert into @table ( web ) values ( :web )
     *
     * Se puede asignar bien pasando un objeto Data o mediante funcion. ejemplo:
     * $query
     * ->web("http://trasweb.net")
     * ->title("Team");
     */
    protected $values = [];
    protected $database;

    function __construct($values = null, $database, array $sentences = [])
    {
        if ($values instanceof \Team\Data\Data) {
            $this->values = $values->get();
        } else {
            $this->values = (array)$values;
        }
        $this->database = $database;

        $this->set($sentences);
    }

    function getDatabase()
    {
        return $this->database;
    }

    /**
     * Necesarios para data: valores para las cadena de substitucion(:cadenaDeSubstitucion)
     */
    public function __call($_name, $_arguments = null)
    {
        $this->values[$_name] = $_arguments[0];
        return $this;
    }
} 
