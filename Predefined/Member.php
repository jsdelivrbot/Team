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

namespace Team\Predefined;

class Member implements \ArrayAccess
{
    use \Team\Data\Box;

    public function __construct()
    {
        $user_data = ['active' => 0, 'level' => \Team\User::GUEST];

        $this->data = \Team\System\Task('\team\member', function ($data) {
            return new \Team\Data\Type\Session($data, []);
        })->with($user_data);
    }

    /**
     * Permitimos que las variables de la sesión se puedan obtener o asignar como si fueran
     * métodos estáticos de esta clase. ej: Usuario::level() o Usuario::nombre('Manuel Canga');
     *
     * @param string $campo se refiere al nombre de la función llamada( o la variable de sessión a tratar )
     * @param array $argumentos ( el argumento 0 en caso de existir, será el valor de la variable de sesión
     * Si no existe es que sólo queremos obtener el valor de la variable.
     * @return mixed Retornamos el valor de la variable de sesión
     */
    public static function & __callStatic($func, $args)
    {
        if (!empty($args)) {
            return self::$current->set($func, $args[0]);
        } else {
            return self::$current->get($func);
        }
    }

    public function & set($field, $value)
    {
        return $this->data[$field] = $value;
    }

    public function id()
    {
        return (int)$this->get('id', 0);
    }

    public function & get($var, $default = null)
    {
        if (isset($this->data[$var])) {
            return $this->data[$var];
        } else {
            return $default;
        }
    }

    public function level()
    {
        if ($this->isRoot()) {
            return \Team\User::ROOT;
        } else {
            if ($this->isAdmin()) {
                return \Team\User::ADMIN;
            } else {
                if ($this->isLogged()) {
                    return \Team\User::USER;
                }
            }
        }

        return \Team\User::GUEST;
    }

    public function isRoot()
    {
        return $this->isLogged() && $this->hasRole('root');
    }

    public function isLogged()
    {
        return $this->get('active', false);
    }

    public function hasRole($role)
    {
        if (isset($this->data['roles'][$role]) || in_array($role, $this->data['roles'])) {
            return true;
        } else {
            return false;
        }
    }

    public function isAdmin()
    {
        return $this->isLogged() && $this->get('admin', false);
    }

    public function isGuest()
    {
        return !$this->isLogged();
    }

    public function isUser()
    {
        return $this->isLogged() && !$this->isAdmin();
    }

    public function notValidUser()
    {
        \Team::system('User not valid', '\team\user\notValid');
        exit();
    }

    /* *************** Operaciones relacionadas con comienzo y finalización de sessions *************** */

    public function doStart($defaults = [], $force_activation = false)
    {
        $this->data->activeSession($force_activation, $defaults);
    }

    /**
     *  Función que se encarga de validar el usuario contra la base de datos.
     * @param strng $correo_electronico es el email del usuario
     * @param string $clave es la clave que ha introducido el usuario (sin md5).
     */
    public function doLogin($email, $passwd, $others_data)
    {
        $data = \Team\System\Task('\team\login', function ($user, $passwd = null, $others_data = []) {
            $passwd = trim($passwd);
            $without_passwd = empty($passwd);

            if ($without_passwd) {
                return [];
            }

            $user_data = \Team\Data\Filter::apply('\team\session\login', [], $user);
            $user_not_found = empty($user_data);

            if ($user_not_found) {
                return [];
            }

            $hash_passwd = md5($passwd);
            $right_passwd = isset($user_data['password']) && $user_data['password'] === $hash_passwd;
            $right_passwd = \Team\Data\Filter::apply('\team\session\right_passwd', $right_passwd, $user_data, $passwd,
                $others_data);

            if (!$right_passwd) {
                return [];
            }

            $user_can_login = \Team\Data\Check::id($user_data['active'], 0) > 0;
            $user_can_login = \Team\Data\Filter::apply('\team\session\user_can_login', $user_can_login, $user_data,
                $others_data);

            if (!$user_can_login) {
                return [];
            }

            return $user_data;
        })->with($email, $passwd, $others_data);

        $this->data = new \Team\Session($data);

        return !empty($this->data);
    }

    /**
     * Función que cierra la sessión del usuario activo
     */
    public function doLogout()
    {
        if (!empty($this->data)) {
            $this->data->close();
        }
    }

    /* *************** ÚTILES  *************** */
    public function debug()
    {
        \Team\Debug::me($this->data, '\team\user\Member');
    }
}
