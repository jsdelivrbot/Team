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

/** **************************************************************************************
 * Sistema de notificaciones/Avisos/Eventos/Alertas. Muy útil para devolver mensajes fácilmente al usuario
 * de la web después de que este haya realizado alguna operación.
 * Se basa en proceso, un proceso puede tener éxito o pudo tener un error crítico.
 * A su vez, los pasos del proceso, pudieron tener avisos informátivos o avisos de errores(normales).
 * También, pudo haber otro tipo de errores, ocasionados por el sistema(fallo de acceso a la bd, un archivo que no se encuentra, ... )
 *************************************************************************************** */

require(_TEAM_ . "/System/Exception/System_Error.php");

class Team
{

    static public $notices = array();

    /* Almacen de todos los listeners */
    /** Avisos actuales */
    static public $current = array();
    /** Aviso anterior */
    static public $last = array();
    /** Manejo de errores del sistema */
    static public $errors = null;
    /**  indice de la cola de mensajes. */
    static private $index = -1;
    private static $listeners = array();

    /** Inicializador de la clase */
    public static function __initialize()
    {
        ini_set('display_errors', 0);

        // Report all PHP errors
        error_reporting(\team\Config::get('GENERAL_ERROR_LEVEL', E_ALL));

        $errors = new \Team\Notices\Errors();

        set_error_handler(array($errors, 'PHPError'), \Team\Config::get('GENERAL_ERROR_LEVEL', E_ALL));

        register_shutdown_function(array($errors, 'critical'));

        self::$errors = $errors;

        self::up();
    }

    /** Empezamos la captura de avisos, dentro del bloque. */
    public static function up()
    {
        self::$index++;
        self::$notices[self::$index] = new \Team\Notices\Notice();
        self::$current = self::$notices[self::$index];
    }

    public static function last()
    {
        return self::$last;
    }

    public static function index($index)
    {
        return self::$notices[$index];
    }

    public static function getCurrent()
    {
        return self::$current->get();
    }

    public static function current()
    {
        return self::$current;
    }

    /** Terminamos la captura de avisos, dentro del bloque en el que estamos */
    public static function down()
    {
        //Guardamos el listado de avisos del nivel actual.
        //  \Level::setNotices(self::$notices[self::$index]);

        self::$last = self::$current;

        //Borramos los avisos del nivel actual
        //ya que hemos hecho una copia
        //No haria falta, pero por si las moscas
        unset(self::$notices[self::$index]);

        self::$index = max(self::$index - 1, 0); //Bajamos la cola
        self::$current = self::$notices[self::$index];
    }

    /**
     * Pasamos cualquier petición a esta clase al notice actual
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::$current, $name], $arguments);
    }

    public static function critical($e = null)
    {
        if (!isset(self::$errors)) {
            error_log(print_r($e, true));
        }

        self::$errors->critical($e);
    }

    /**
     * Añadimos un listener a la espera de un evento
     * @param namespace $event Evento a esperar
     * @param callable $listeners listener que se queda a la escucha
     * @param int $order Posición en la llamada de eventos
     */
    public static function addListener($event, $listener, $order = 65)
    {
        $event = rtrim($event, '\\');

        $order = \Team\Data\Check::id($order);

        //Si no habia listeners asociados al evento, ahora si
        self::$listeners[$event] = self::$listeners[$event] ?? [];

        //Vamos buscando un hueco libre para el trabajador a partir del orden que pidió
        for ($max_order = 100; isset(self::$listeners[$event][$order]) && $order < $max_order; $order++) {
            ;
        }

        //Guardamos el listener
        self::$listeners[$event][$order] = $listener;
    }

    public static function systemException(\Exception $exception)
    {
        $error_type = "SYSTEM";
        $result = \Team::event($exception->getCode(), $exception->get(), $error_type);
        if ($result) {
            return $result;
        }

        throw $exception;
    }

    //Procesa una excepción en el sistema.

    /**
     * Aviso de evento. Es una notificación de tipo neutro.
     * Se recorre todos los listeners hasta que uno devuelva true. En ese momento se para el barrido.
     * Los argumentos, si los hubiera, son pasados por referencia
     * @param namespace $code es el código o namespace del evento ocurrido.
     * @param $data es un dato que se quiere transmitir con el evento.
     *
     * @return boolean devuelve si algún listener cancelo o no el evento( retornando true: cancela, false/null: no)
     */
    public static function event($code, &...$data)
    {
        $namespace = rtrim($code, '\\');

        if (isset(self::$listeners[$namespace])) {
            $data[] = $namespace;

            foreach (self::$listeners[$namespace] as $listener) {
                //Si el listener es una ruta a un archivo, entonces se carga ese archivo si existe
                $not_file = !is_string($listener) || '/' != $listener[0] || !\Team\System\FileSystem::load($listener);

                if ($not_file && is_callable($listener, $syntax_only = true)) {
                    //mandamos el trabajo al listener
                    $result = $listener(...$data);
                    if ($result) {
                        return $result;
                    }
                } else {
                    \Team\Debug::me('You are adding a listener to event ' . $namespace . ' which isn\'t neither a callback nor file( with linux path )');
                }
            }
        }

        return false;
    }

    public static function debug()
    {
        $backtrace = debug_backtrace();
        $file = $backtrace[0]["file"];
        $line = $backtrace[0]["line"];

        \team\Debug::me(self::$notices, "Notice Log", $file, $line);
    }

}
