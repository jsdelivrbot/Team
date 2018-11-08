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

namespace Team\System;

/**
 * Gestión de contextos de Team Framework
 * Un contexto es una colección de variables de configuración para un namespace especifico.
 * Los contextos se van abriendo por niveles de profundidad según se va cargando las acciones.
 * Cada contexto que se abre se añade a una pila. De manera, que mientras que las acciones se
 * van anidando el número de contextos aumenta en esa pila.
 * Diremos que el contexto es de mayor nivel cuando más alto esté en la pila ( o más anidada esté
 * la acción asociada ) y más bajo nivel cuanto más bajo esté en la pila ( o la acción esté menos profunda
 * en cuanto a anidamiento )
 * Los contextos sirve de substituto a las constantes y a las variables globales.
 */
abstract class Context
{
    use \Team\Data\Vars;

    protected static $vars = ['LEVEL' => 0, 'NAMESPACE' => '\\'];

    /**
     * Abrimos un contexto( es decir, se lanza un nuevo response )
     * @return array contexto nuevo
     */
    public static function open($isolate = true)
    {
        if ($isolate) {
            $vars = ['LEVEL' => 0, 'NAMESPACE' => '\\'];
        } else { //reuse parent vars
            $vars = self::$vars;
        }

        $vars['BEFORE'] = self::$vars ?: $vars; //Guardamos el contexto anterior( es decir, el que lanzó el response )
        $vars['LEVEL'] = $vars['BEFORE']['LEVEL'] + 1;
        $vars['LAST'] = []; //Aún no se ha lanzado un response desde el contexto actual

        self::$vars = $vars;

        return self::$vars;
    }

    /**
     * Se cierra el contexto actual y se vuelve al que lo llamó
     * @return array se devuelve los datos del contexto que se cierra
     */
    public static function close()
    {
        $namespace = self::$vars['NAMESPACE'];

        //Obtenemos el namespace del contexto que se va a cerrar
        \Team\Debug::trace("Context[{$namespace}]: Ending");

        if (self::getLevel() > 0) {
            $vars = self::$vars['BEFORE']; //El contexto que llamó al contexto actual pasa a ser el nuevo activo

            //Asignamos el contexto actual como el last del nuevo activo
            unset(self::$vars['BEFORE']);
            $vars['LAST'] = self::$vars;
            self::$vars = $vars;

            //Devolvemos el contexto que se ha cerrado
            return self::$vars['LAST'];
        }

        return self::$vars;
    }

    public static function getLevel()
    {
        return self::$vars['LEVEL'];
    }

    /**
     * Para dejar todo como al principio
     */
    public static function reset()
    {
        self::$vars = ['LEVEL' => 0, 'NAMESPACE' => '\\', 'BEFORE' => [], 'LAST' => []];
    }

    /* ------------------- GETTERS  ---------------------- */

    public static function isMain()
    {
        return 1 === self::getLevel();
    }

    public static function get($var, $default = null, $place = null)
    {
        return self::$vars[$var] ?? \Team\Config::get($var, $default, $place);
    }

    public static function getIndex()
    {
        return self::getLevel();
    }

    public static function & getContext()
    {
        return self::$vars;
    }

    /*
        Devolvemos el valor de una variable de configuración del contexto inferior( el que empezó el actual )
        @param String $name nombre de la variable de configuración de la que queremos devolver el valor.
        @param mixed $default valor a devolver en caso de no existir la variable de $name
    */

    public static function before($name = null, $default = null)
    {
        if (!isset($name)) {
            return self::$vars['BEFORE'] ?? [];
        }

        if (isset(self::$vars['BEFORE'][$name]) && array_key_exists($name, self::$vars['BEFORE'])) {
            return self::$vars['BEFORE'][$name];
        }
        return $default;
    }

    /*
        Devolvemos el valor de una variable de configuración del contexto ultimo cerrado.
        @param String $name nombre de la variable de configuración de la que queremos devolver el valor.
        @param mixed $default valor a devolver en caso de no existir la variable de $name
    */
    public static function last($name = null, $default = null)
    {
        if (!isset($name)) {
            return self::$vars['LAST'] ?? [];
        }

        if (isset(self::$vars['LAST'][$name]) || array_key_exists($name, self::$vars['LAST'])) {
            return self::$vars['LAST'][$name];
        }
        return $default;
    }

    /*
        Devolvemos el valor de una variable de configuración existente en el contexto de la acción main.
        @param String $name nombre de la variable de configuración de la que queremos devolver el valor.
        @param mixed $default valor a devolver en caso de no existir la variable de $name
    */
    public static function main($name = null, $default = null)
    {
        if (!isset($name)) {
            return self::$vars ?? [];
        }

        if (isset(self::$vars[$name]) || array_key_exists($name, self::$vars)) {
            return self::$vars[$name];
        }

        return $default;
    }

}

