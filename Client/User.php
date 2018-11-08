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

namespace Team\Client;

\Team\Loader\Classes::load('\Team\Predefined\Member', '/Predefined/Member.php', _TEAM_);

class User
{
    /** Definimos la visibilidad */
    const
        ROOT = 3/** Access to private area and admin area with restricted access */,
        ADMIN = 2/** Access to private area and admin area. This also is logged */,
        USER = 1/** Access to private area. Meaning the same: active but not admin.  */,
        GUEST = 0 /* Only access to public area.  This cannot logged */
    ;

    /**
     * logged when a user or admin login
     */

    private static $current = null;

    public static function __callStatic($func, $args)
    {
        return call_user_func_array([self::$current, $func], $args);
    }

    public static function getCurrent()
    {
        if (!isset(self:: $current)) {
            self::__initialize();
        }

        return self::$current;
    }

    public static function setCurrent($user)
    {
        self::$current = $user;
    }

    /**
     * Preparamos el sistema de sesiones
     * y mantenemos activa la sesión si ya se había activado anteriormente.
     * Así ahorramos que se inicie sesión para un visitante que no haga falta( ej: bots )
     *
     */
    public static function __initialize()
    {
        if (isset(self::$current)) {
            return;
        }

        class_alias('\Team\Client\User', '\Team\User', false);

        $user_class = \Team\System\Context::get('\Team\User', '\Team\Predefined\Member');

        if (isset($user_class) && class_exists($user_class)) {
            self::$current = new  $user_class();
        }
    }

    /** *************** Comprobaciones de seguridad  *************** */
    public static function mustBeRoot()
    {
        if (!self:: $current->isRoot()) {
            self:: $current->notValidUser();
        }
    }

    //

    //Métodos obligatorios:
    //notValidUser

    public static function mustBeAdmin()
    {
        if (!self:: $current->isAdmin()) {
            self:: $current->notValidUser();
        }
    }

    public static function mustBeLogged()
    {
        if (!self:: $current->isLogged()) {
            self:: $current->notValidUser();
        }
    }

    /** *************** getters y setters  generales   *************** */
    public static function & set($field, $value)
    {
        return self::$current->set($field, $value);
    }

    public static function & get($field = 'level', $default = null)
    {
        return self::$current->get($field, $default);
    }

    public static function levels()
    {
        return ['All the Internet', 'Users who can login', 'Admins'];
    }

    /**
     * Devuelve la ip del cliente que ha hecho la petición contra team-framework
     */
    public static function getIP()
    {
        static $ip = null;

        if (isset($ip)) {
            return $ip;
        }

        $sources = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($sources as $source) {
            if (isset($_SERVER[$source]) && \Team\Data\Check::ip($_SERVER[$source])) {
                return $ip = $_SERVER[$source];
            }
        }
    }

    /* ***************** Helpers útiles **************** */

    /**
     * Test if the current user( or device )  has the capability to upload files.
     *
     * @return bool Whether the device is able to upload files.
     */
    public static function canUpload()
    {
        if (\Team\Client\Http::checkUserAgent('desktop')) {
            return true;
        }

        $ua = $_SERVER['HTTP_USER_AGENT'];

        if (strpos($ua, 'iPhone') !== false
            || strpos($ua, 'iPad') !== false
            || strpos($ua, 'iPod') !== false) {
            return preg_match('#OS ([\d_]+) like Mac OS X#', $ua, $version) && version_compare($version[1], '6', '>=');
        }

        return true;
    }

    public static function debug()
    {
        self::$current->debug();
    }

    public function __call($func, $args)
    {
        return call_user_func_array([self::$current, $func], $args);
    }
}

