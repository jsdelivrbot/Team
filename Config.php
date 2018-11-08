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

namespace Team;

/**
 * Clase para gestionar variables de configuracion
 *
 */
abstract class Config
{
    use \Team\Data\Vars;

    protected static $vars = [];
    protected static $modifiers = [];
    protected static $sanitizers = [];
    protected static $constructors = [];

    public static function setup()
    {
        \Team::event('\team\setup', self::$vars);
    }

    public static function get(string $var_name, $default = null, $place = null)
    {
        return self::applyModifiers($var_name, self::$vars[$var_name] ?? self::applyConstructors($var_name, $default),
            $place);
    }

    protected static function applyModifiers($config_var, $value, $place)
    {
        if ( ! isset(self::$modifiers[$config_var])) {
            return $value;
        }

        $modifiers =&self::$modifiers[$config_var];

        ksort($modifiers);

        foreach ($modifiers as $modifier) {
            if ( ! is_callable($config_var, $syntax_only = true)) {
                \Team\Debug::me('You are adding a modifier to ' . $config_var . ' which isn\'t a callback');
                return false;
            }

            $value = $modifier($value, $place);
        }
        return $value;
    }

    protected static function applyConstructors($config_var, $default)
    {
        if ( ! isset(self::$constructors[$config_var])) {
            return $default;
        }

        $constructors =&self::$constructors[$config_var];

        ksort($constructors);

        $value = $default;
        foreach ($constructors as $constructor) {
            if ( ! is_callable($config_var, $syntax_only = true)) {
                \Team\Debug::me('You are adding a constructor to ' . $config_var . ' which isn\'t a callback');
                return false;
            }

            $value = $constructor($default);
        }
        return $value;
    }

    public static function set($var, $value = null)
    {
        $old_value = null;

        if (is_array($var)) {
            self::$vars = $var + self::$vars;
        } else {
            if (is_string($var)) {
                $old_value = self::$vars[$var] ?? $old_value;

                self::$vars[$var] = static::applySanitizers($var, $value);
            }
        }

        return $old_value;
    }

    protected static function applySanitizers($config_var, $value)
    {
        if ( ! isset(self::$sanitizers[$config_var])) {
            return $value;
        }

        $sanitizers =&self::$sanitizers[$config_var];

        ksort($sanitizers);

        foreach ($sanitizers as $sanitizer) {
            if ( ! is_callable($config_var, $syntax_only = true)) {
                \Team\Debug::me('You are adding a sanitizier to ' . $config_var . ' which isn\'t a callback');
                return false;
            }

            $value = $sanitizer($value);
        }
        return $value;
    }

    public static function addSanitizer($config_var, $function, int $order = 50)
    {
        self::$sanitizers[$config_var] = self::$sanitizers[$config_var] ?? [];

        // Vamos buscando un hueco libre para el sanitizer a partir del orden que pidió
        for ($max_order = 100; isset(self::$sanitizers[$config_var][$order]) && $order < $max_order; $order++) {
            ;
        }

        // Lo almacemanos todo para luego poder usarlo
        self::$sanitizers[$config_var][$order] = $function;

        return false;
    }

    public static function addConstructor($config_var, $function, int $order = 50)
    {
        self::$constructors[$config_var] = self::$constructors[$config_var] ?? [];

        // Vamos buscando un hueco libre para el constructor a partir del orden que pidió
        for ($max_order = 100; isset(self::$constructors[$config_var][$order]) && $order < $max_order; $order++) {
            ;
        }

        // Lo almacemanos todo para luego poder usarlo
        self::$constructors[$config_var][$order] = $function;

        return false;
    }

    public static function addModifier($config_var, $function, int $order = 50)
    {
        self::$modifiers[$config_var] = self::$modifiers[$config_var] ?? [];

        // Vamos buscando un hueco libre para el modificador a partir del orden que pidió
        for ($max_order = 100; isset(self::$modifiers[$config_var][$order]) && $order < $max_order; $order++) {
            ;
        }

        // Lo almacemanos todo para luego poder usarlo
        self::$modifiers[$config_var][$order] = $function;

        return false;
    }

}