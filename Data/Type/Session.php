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

namespace Team\Data\Type;

/** Seguridad antes que nada  */
ini_set('session.cookie_httponly', 1);
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);

class Session extends Base
{
    protected static $active = false;
    /** Identificador que se le dará a la cookie de sessión */
    protected $session_id = null;

    /**
     * Preparamos el sistema de sesiones
     * y mantenemos activa la sesión si ya se había activado anteriormente.
     * Así ahorramos que se inicie sesión para un visitante que no haga falta( ej: bots )
     *
     */
    public function __construct($data = null, Array $_options = [])
    {
        $this->session_id = \Team\System\Context::get('SCRIPT_ID');

        $this->initialize();

        $with_previous_session = isset($_COOKIE[$this->session_id]);
        $force_activation = isset($_options['force']) && $_options['force'];

        if ($with_previous_session || $force_activation) {
            $this->activeSession($force_activation, $data);
        } else {
            //Si está activa sólo necesitamos enganchar los datos de sessiona activo con los nuevos
            //si no estaba activa pero tampoco hay interes en activarlo, lo unico que tendremos es un almacen de datos temporal
            $this->data =& self::session($data, $_options['overwrite'] ?? false);
        }
    }

    protected function initialize()
    {
        if (!isset($_SESSION[$this->session_id])) {
            $_SESSION[$this->session_id] = [];
        }
    }

    /**
     * Iniciamos una session ( sólo si no se había activado anteriormente  ) o se especifico forzado
     *
     * @param boolean $forzar_activacion nos permite forzar el comienzo de una nueva sesión
     *
     */
    public function activeSession($force_activation = false, $defaults = [])
    {
        if ($force_activation) {
            $this->close();
        }

        if (!$this->isActive() || $force_activation) {
            $data = $_SESSION;
            session_name($this->session_id);
            session_start();
            static::$active = true;
            //Recolocamos cualquier dato que hubiera antes de iniciar sesión
            $_SESSION += $data;

            $this->initialize();
        }

        $this->data = &self::session($defaults);
    }

    public function close()
    {
        $this->data = array();
        if (isset($_SESSION[$this->session_id])) {
            unset($_SESSION[$this->session_id]);
        }

        static::$active = false;
        if (empty($_SESSION)) {
            $result = session_destroy();
        } else {
            $result = true;
        }

        session_commit();
        return $result;
    }

    /**
     * Check if session is already active
     * @return bool
     */
    protected function isActive()
    {
        //return PHP_SESSION_ACTIVE == session_status(); //No funciona corréctamente. Quizás un bug
        return static::$active;
    }

    /**
     * Esta función sirve para abstraernos de la variable sesión de PHP y del
     * id distintitivo usado por Team Framework.
     * Esto lo hacemos así porque puede pasar que haya librerías de tercero que quieran
     * usar $_SESSION y así nos quitamos de que nos pisen los datos.
     *
     * @return ref array devuelve el array que usaremos para almacenar los datos de sesión
     */
    protected function & session($defaults = [], $overwrite = false)
    {
        if ($overwrite) {
            $_SESSION[$this->session_id] = (array)$defaults;
        } else {
            if (!empty($defaults)) {
                $_SESSION[$this->session_id] = (array)$defaults + (array)$_SESSION[$this->session_id];
            }
        }

        return $_SESSION[$this->session_id];
    }

}
