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

trait Select
{
    /**
     * Es como getAll pero devuelve sólo los valores de una columna $field.
     * Si se añade el campo $index. Entonces el array resultante tendrá de keys el campo especificado en $index
     */
    public function getVars($field, $from = null, $select = [], $index = null)
    {
        $result = $this->getAll($from, $select);

        if (empty($result)) {
            return [];
        }

        if (isset($index)) {
            return array_column($result, $field, $index);
        } else {
            return array_column($result, $field);
        }
    }

    /** Devuelve todos los registros para la consulta realizada
     * Recupera datos de una base de datos
     * Ejemplo 1:
     * $query = new \Team\Db\Query();
     * $result = $query->getAll("@Access");  /
     *
     * Ejemplo 2:
     *
     * query = new \Team\Db\Query();
     * $query->limit = 10;
     * $result = $query->getAll("@Access");
     *
     * Ejemplo 3:
     * $query = new \Team\Db\Query(['idmenor' => 3);
     * $query->idmayor(5); //2º forma de pasar datos
     * $query->where = " idAccess > :idmenor && idAccess < :idmayor ";
     * $result = $query->getAll("@Access");
     *
     * Ejemplo 3 alternativo:
     * $data = new \Team\Data\Data();
     * $data->idmenor = 3;
     * $data->idmayor = 5;
     * $result = new \Team\Db\Query($data)->getAll("@Access");
     *
     *
     * Ejemplo 4:
     * $query = new \Team\Db\Query();
     * $query->select = " name ";
     * $query->from = "@Access"
     * $result = $query->getAll();
     */
    public function getAll($from = null, $select = [], $limit = -1)
    {
        //Si existe la sentencia from, damos prioridad a esta
        if (isset($this->from)) {
            $from = $this->from;
        }

        if (isset($from)) {
            if (!is_array($from)) {
                $from = (array)$from;
            }

            $this->from = $from;
        }

        //Si existe la sentencia select, damos prioridad a esta
        if (isset($this->select)) {
            $select = $this->select;
        }

        if ($select) {
            if (!is_array($select)) {
                $select = (array)$select;
            }
            $this->select = $select;
        }

        //Si existe la sentencia limit, damos prioridad a esta
        if (isset($this->limit)) {
            $limit = $this->limit;
        }

        if (-1 != $limit) {
            $this->limit = $limit;
        }

        $sentences = $this->get();
        $values = $this->values;

        return $this->database->get($sentences, $values);
    }

    /**
     * Es como getRow pero retorna sólo un valor de esa fila.
     *
     * @param $field campo del que se quiere obtener el valor
     * @param null $from tabla asociada
     * @param string $select select avanzado que se quiere( por ejemplo, para consultas con agregados )
     * @return el valor pedido o false si no se encuentra
     */
    public function getVar($field, $from = null, $select = null, $where = null)
    {
        //Esta función es un método directo para obtener un valor
        //Si se adelanto el usuario para meter un select, no tiene sentido añadirlo de nuevo
        if (!isset($this->select)) {
            $select = ($select) ?: $field;
        }

        if (isset($where)) {
            if (is_array($this->where)) {
                $this->where[] = $where;
            } else {
                $this->where = $where;
            }
        }

        $row = $this->getRow($from, [$select]);

        if (isset($row[$field])) {
            return $row[$field];
        } else {
            return false;
        }
    }

    /**
     * Es como getAll pero retorna sólo una fila
     *
     * @param null $from
     * @param array $select
     * @return mixedE
     */
    public function getRow($from = null, $select = [], $where = null)
    {
        if (isset($where)) {
            if (is_array($this->where)) {
                $this->where[] = $where;
            } else {
                $this->where = $where;
            }
        }

        $rows = $this->getAll($from, $select, 1);
        if (1 === count($rows)) {
            return $rows[0];
        }
    }
}
