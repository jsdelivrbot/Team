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

/**
 * Simple  Iterator for Models( y ActiveRecords )
 */
class Collection implements \Iterator, \Countable
{
    protected $model = null;
    protected $records = null;
    private $index = 0;
    private $defaults = [];

    /**
     * @param array $records elementos sobre los que se va a iterar
     * @param string|Model clase que se usará como base para los registros
     * @param array $defaults valores que se usaran por defecto a los valores del Record
     */
    function __construct(array $records = [], $model = null, array $defaults = [])
    {
        $this->records = $records;
        $this->defaults = $defaults;

        if (is_object($model)) {
            $this->model = get_class($model);
        } else {
            $this->model = $model;
        }
    }

    /**
     * Extract a slice of fields of current record, given a list of keys.
     *
     *
     * @param array $keys The list of keys.
     * @return array The array slice.
     */
    function fields($keys)
    {
        $array = $this->records[$this->index];

        $slice = [];
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $slice[$key] = $array[$key];
            }
        }

        return $slice;
    }

    /**
     * Sorting records according to a external sort function
     *
     * @param callable $sort_function function to use in order to sort records
     */
    function sort($sort_function = null)
    {
        if (!isset($sort_function) || !is_callable($sort_function)) {
            return null;
        }

        $records = $this->records;
        $_sorted_records = $sort_function($records);
        $this->records = is_array($_sorted_records) ? $_sorted_records : $records;
    }

    /**
     * Return a Collection with a filtereed list of records, based on a set of key => value arguments.
     *
     * @param array $args Optional. An array of key => value arguments to match
     *                         against each object. Default empty array.
     * @param string $operator Optional. The logical operation to perform. 'AND' means
     *                         all elements from the array must match. 'OR' means only
     *                         one element needs to match. 'NOT' means no elements may
     *                         match. Default 'AND'.
     * @return Collection Collection of found values.
     */
    function newCollection($args = array(), $operator = 'AND')
    {
        if (!empty($args)) {
            $filtered = $this->filter($args, $operator);
        } else {
            $filtered = $this->records;
        }

        return new $this($filtered, $this->model, $this->defaults);
    }

    /**
     * Filters the list of records, based on a set of key => value arguments.
     *
     * @param array $args Optional. An array of key => value arguments to match
     *                         against each object. Default empty array.
     * @param string $operator Optional. The logical operation to perform. 'AND' means
     *                         all elements from the array must match. 'OR' means only
     *                         one element needs to match. 'NOT' means no elements may
     *                         match. Default 'AND'.
     * @return Array Array of found values.
     */
    function filter($args = array(), $operator = 'AND', $field = null)
    {
        $list = $this->records;

        if (empty($args)) {
            return $list;
        }

        $operator = strtoupper($operator);
        $count = count($args);
        $filtered = array();

        foreach ($list as $key => $obj) {
            $to_match = (array)$obj;

            $matched = 0;
            foreach ($args as $m_key => $m_value) {
                if (array_key_exists($m_key, $to_match) && $m_value == $to_match[$m_key]) {
                    $matched++;
                }
            }

            if (('AND' == $operator && $matched == $count)
                || ('OR' == $operator && $matched > 0)
                || ('NOT' == $operator && 0 == $matched)) {
                $filtered[$key] = $obj;
            }
        }

        if (isset($field)) {
            $filtered = array_column($filtered, $field);
        }

        return $filtered;
    }


    /***** Filteres of records ******/

    /**
     * Return a sorted Collection according to a external sort function
     * @param callable $sort_function function to use in order to sort records
     * @return new collection with sorted records according to $sort_function
     */
    function sortedCollection($sort_function = null)
    {
        if (!isset($sort_function) || !is_callable($sort_function)) {
            return null;
        }

        $records = $this->records;
        $_sorted_records = $sort_function($records);
        $sorted_records = is_array($_sorted_records) ? $_sorted_records : $records;

        return new $this($sorted_records, $this->model, $this->defaults);
    }

    /** Retrieve records */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * Devolvemos sólo una columna de los records
     */
    public function getColumn($column, $key_column = null)
    {
        if (isset($key_column)) {
            return array_column($this->records, $column, $key_column);
        } else {
            return array_column($this->records, $column);
        }
    }


    /***** Checkings******/

    /** Counts of records */
    function count()
    {
        return count($this->records);
    }

    /** Check if iterator is empty */
    function isEmpty()
    {
        return !$this->__isset();
    }

    /** Check if iterator is empty */
    function __isset($index = 0)
    {
        return !empty($this->records) && isset($this->records[$index]);
    }

    /***** Positional extraction of records ******/

    /* extract first record */

    function shift()
    {
        $record = array_shift($this->records);

        return $this->toModel($record);
    }

    /***** Iterator implements ******/
    function toModel($data)
    {
        //data is an activerecord?
        if (!is_array($data)) {
            return $data;
        }

        $class = $this->model;

        $model = new $class();
        $model->onNewRecord((array)$data + (array)$this->defaults);

        return $model;
    }

    /* extract last record */

    /**
     * get first record
     */
    function first()
    {
        $record = $this->records[0];
        return $this->toModel($record);
    }

    function pop()
    {
        $record = array_pop($this->records);

        return $this->toModel($record);
    }

    /**
     * get last record
     */
    function last()
    {
        $record = end($this->records);
        return $this->toModel($record);
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function key()
    {
        $current = $this->current();
        return $current->getId();
    }

    public function current()
    {
        return $this->recordOf($this->index);
    }

    public function recordOf($index = null)
    {
        if (!isset($index)) {
            $index = $this->index;
        }

        if (isset($this->records[$index])) {
            $record = $this->records[$index];
            return $this->toModel($record);
        }

        return false;
    }

    public function next()
    {
        $this->index++;
        return $this->current();
    }

    public function valid()
    {
        return (bool)$this->current();
    }

} 

