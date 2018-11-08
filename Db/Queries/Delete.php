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

namespace Team\Db\Queries;

trait Delete
{

    /*
    
        Ejemplo1:
            $query = new \Team\Db\Query();
            $query->where = "idAccess > :idmenor AND idAccess < :idmayor";  //literal
            $query->idmenor(500)->idmayor(600); //datos
            $query->delete("@Access");  //Accion ( @ se reemplazara por el prefijo de las bases de datos )
       Ejemplo2:
               $query = new \Team\Db\Query(['idAccess' => 585]);
            $query->where = "idAccess = :idAccess"; //literal
            $query->delete("@Access");  //dato ( @ se reemplazara por el prefijo de las bases de datos )
        Ejemplo3:
    
            $query = new \Team\Db\Query();
            $query->where = "idAccess = 585"; //literal
            $query->delete("@Access"); //Accion ( @ se reemplazara por el prefijo de las bases de datos )
    
    */
    public function delete($_table, $secure = true)
    {
        //Obtenemos los datos que se han guardados
        $sentences = $this->get();

        if (!isset($sentences["where"])) {
            $sentences["where"] = '';
        }

        $values = $this->onlyWhereVars($sentences["where"], $this->values);

        return $this->database->delete($_table, $sentences["where"], $values, $secure);
    }

    /**
     * PDO genera un error si se manda más valores de los que se necesitan.
     * Es por eso que antes de lanzar la consulta nos quedamos sólo con aquellos valores
     * que se estén usando en el where
     */
    protected function onlyWhereVars($wheres = [], $old = [])
    {
        $values = [];
        if (!is_array($wheres)) {
            $wheres = [$wheres];
        }

        foreach ($wheres as $_where) {
            if (is_array($_where)) {
                //estamos en un caso tipo: ['id' => ':id' ]

                $keys = array_keys($_where);
                $key = $keys[0];
                if (array_key_exists($key, $old)) {
                    $values[$key] = $old[$key];
                }
            } else {
                //estamos ante un caso  id = ':id'
                $matches = array();
                $result = preg_match_all("/[:](.*?) /", $_where . " ", $matches);

                if (!empty($matches) && 2 == count($matches)) {
                    foreach ($matches[1] as $_index => $key) {
                        if (array_key_exists($key, $old)) {
                            $values[$key] = $old[$key];
                        }
                    }
                }
            }
        }

        return $values;
    }

}
