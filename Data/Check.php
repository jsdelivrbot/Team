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

namespace Team\Data;

/** **********************************************************************************
 * Esta clase sirve para la validación de tipos de datos de entrada en Team
 ************************************************************************************* */
abstract class Check
{
    private function __construct()
    { /* prohibido instanciar un objeto */
    }

    /**
     * Validamos si un $number es un número no nulo entero
     *
     * @param mixed $number es la cadena  ( que se presume que es un número ) a validar
     * @param mixed|false $default es el valor a devolver en caso de que $number no sea válido
     * @example \Team\Data\Check::number('hola mundo')        => devuelve false
     * @example \Team\Data\Check::number('hola mundo', 10) => devuelve 10
     * @example \Team\Data\Check::number(-50)                    => devuelve -50
     * @example \Team\Data\Check::number(10, 5)                => devuelve 10
     *
     * @return devuelve $number si es un número natural positivo sino devuelve $default
     */
    static public function number($number, int $default = null) /* :?int */
    {
        if (isset($number) && ctype_digit(ltrim($number, '-')) && (!isset($number[2]) || '-' != $number[2])) {
            return (int)$number;
        } else {
            return $default;
        }
    }

    /**
     * Validamos si un $number es un número no nulo real
     *
     * @param mixed $number es la cadena  ( que se presume que es un número real ) a validar
     * @param mixed|false $default es el valor a devolver en caso de que $number no sea válido
     * @example \Team\Data\Check::number('hola mundo')        => devuelve false
     * @example \Team\Data\Check::number('hola mundo', 10) => devuelve 10
     * @example \Team\Data\Check::number(-50)                    => devuelve -50
     * @example \Team\Data\Check::number(10, 5)                => devuelve 10
     * @example \Team\Data\Check::number(10.5, 5)                => devuelve 10.5
     *
     * @return devuelve $number si es un número natural positivo sino devuelve $default
     */
    static public function real($number, float $default = null) /* :?float */
    {
        if (isset($number) && is_numeric($number)) {
            return (float)$number;
        } else {
            return $default;
        }
    }

    /**
     * Comprueba un CIF español
     *
     * @param $cif
     *
     */
    static function CIF($cif, $default = null)
    {
        $cif = strtoupper($cif);

        if (self::DNI($cif)) {
            return $cif;
        }

        if (!is_string($cif) || strlen($cif) != 9) {
            return $default;
        }

        //first letter must be an allow letter
        if (0 === strspn($cif[0], "ABCDEFGHJNPQRSUVW")) {
            return $default;
        }

        //Si la parte numérica no es numérica
        $numeric_part = substr($cif, 1, -1);
        if (!self::id($numeric_part)) {
            return $default;
        }

        $plus_digits_of_a_string = function (string $string, ...$digits) {
            $total = 0;

            foreach ($digits as $digit) {
                /* -1 is because strings/arrays starts with 0. */
                $new_value = $string[$digit - 1] ?? 0;
                $total = $total + (int)$new_value;
            }

            return $total;
        };

        $amount_even = $plus_digits_of_a_string($numeric_part, 2, 4, 6);
        $amount_odd = $plus_digits_of_a_string($numeric_part[0] * 2, 1, 2);
        $amount_odd += $plus_digits_of_a_string($numeric_part[2] * 2, 1, 2);
        $amount_odd += $plus_digits_of_a_string($numeric_part[4] * 2, 1, 2);
        $amount_odd += $plus_digits_of_a_string($numeric_part[6] * 2, 1, 2);

        $partial_amount = (string)($amount_even + $amount_odd);
        $one_from_digit = $partial_amount[-1];
        $check_remainder = (0 === $one_from_digit) ? 0 : 10 - $one_from_digit;
        $check_letters = ['J', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        $check_digit = $cif[-1];

        if ($check_letters[$check_remainder] != $check_digit && $check_remainder != $check_digit) {
            return $default;
        }

        return $cif;
    }

    /**
     * Comprueba un DNI español tanto de la forma: LNNNNNNNN como NNNNNNNNL
     *
     * @param $dni
     *
     */
    static function DNI($dni, $default = null)
    {
        $letter = substr($dni, strspn($dni, "1234567890"), 1);
        $numbers = \Team\Data\Sanitize::natural($dni);

        if (empty($letter) || \Team\System\I18N::length($numbers) != 8) {
            return $default;
        }

        $index = $numbers % 23;
        $letters = "TRWAGMYFPDXBNJZSQVHLCKE";

        if ($letters[$index] === strtoupper($letter)) {
            return $dni;
        }

        return $default;
    }

    /**
     * Validamos si $number es un número no nulo natural válido. muy util para: idnoticia, idelemento, ...
     *
     * @param mixed $number es la cadena ( que se presume que es un número ) a validar
     * @param mixed|false $default es lo que se devuelve si $number no es válido.
     * @example \Team\Data\Check::id('hola mundo')        => devuelve false
     * @example \Team\Data\Check::id('hola mundo', 10)    => devuelve 10
     * @example \Team\Data\Check::id(-50)                    => devuelve false
     * @example \Team\Data\Check::id(10, 5)                => devuelve 10
     *
     * @return devuelve $number si es un número natural positivo sino devuelve $default
     */
    static public function id($number, int $default = null) /* :?int */
    {
        if (isset($number) && ctype_digit("$number") && $number >= 0) {
            return (int)$number;
        } else {
            return $default;
        }
    }

    /**
     * Validamos si $alphameric es un alfanumérico
     *
     * @param mixed $alphameric es la cadena  ( que se presume que es alfanumerica ) a validar
     * @param mixed|false $default es el valor a devolver en caso de que $alphameric no sea válido
     * @example \Team\Data\Check::key('hola mundo')        => devuelve false
     * @example \Team\Data\Check::key('hola mundo', 10)    => devuelve 10
     * @example \Team\Data\Check::key(-50)                    => devuelve false
     * @example \Team\Data\Check::key('prueba10', 10)        => devuelve 'prueba10'
     *
     * @return devuelve $alphameric si es una cadena alfanumérica[/[a-z0-9\.\_\-]+$/i] sino devuelve $default
     */
    static public function key($alphameric, $default = null, $others = '')
    {
        //Comprobamos si es una alfanumérico más ._-
        if (isset($alphameric) && preg_match('/^[a-z0-9\.\_\-' . $others . ']+$/i', $alphameric)) {
            return $alphameric;
        } else {
            return $default;
        }
    }

    /**
     * Validamos si $word es una palabra ( cadena de carácteres alfabético y sin espacio )
     *
     * @param mixed $word es la cadena  ( que se presume que es una palabra ) a validar
     * @param mixed|false $default es el valor a devolver en caso de que $word no sea válido
     * @example \Team\Data\Check::word('hola mundo')        => devuelve false
     * @example \Team\Data\Check::word('hola mundo', 10)    => devuelve 10
     * @example \Team\Data\Check::word(-50)                    => devuelve false
     * @example \Team\Data\Check::word('prueba10', 10)        => devuelve 10
     * @example \Team\Data\Check::word('HolaMundo', 10)    => devuelve HolaMundo
     *
     * @return devuelve $word si es una palabra sino devuelve $default
     */
    static public function word($word, $default = null)
    {
        if (isset($word) && preg_match('/^[a-z]+$/i', $word)) {
            return $word;
        } else {
            return $default;
        }
    }

    /**
     * Validamos si $text es una cadena con información relevante( nombre, apellidos, etc )
     * Para ello tiene que ser mayor de 3 carácteres y puede tener cualquier letra( incluido acentos )
     */
    static public function information($text, $default = null, $others = '')
    {
        if (isset($text) && preg_match('/^[A-Za-z0-9À-ÿ\.\-\s' . $others . ']{3,}/i', $text)) {
            return $text;
        } else {
            return $default;
        }
    }

    /**
     * Validamos si answer es una respuesta positiva o no
     * @param mixed answer es la cadena  que se quiere comprobar si es positiva
     * @return boolean devuelve un boolean indicando si $_answer es un respuesta positiva(true) o no(false)
     */
    static public function choice($_answer)
    {
        $answer = strtolower($_answer);

        if ('1' === $answer || 1 === $answer || 'on' === $answer || 'y' === $_answer) {
            return true;
        }

        return false;
    }

    /**
     * Validamos si $url es una url ( dirección web ) válida
     *
     * @param mixed $url es la cadena  ( que se presume que es una url ) a validar
     * @param mixed|false $default es el valor a devolver en caso de que $url no sea válido
     * @example \Team\Data\Check::url('http')                    => devuelve false
     * @example \Team\Data\Check::url('wwwteamnet', 10)    => devuelve 10
     * @example \Team\Data\Check::url('http://trasweb.net')    => devuelve http://trasweb.net
     *
     * @params int flags:
     *
     * FILTER_FLAG_SCHEME_REQUIRED – Require the scheme (eg, http://, ftp:// etc) within the URL.
     * FILTER_FLAG_HOST_REQUIRED – Require host of the URL (eg, www.trasweb.net)
     * FILTER_FLAG_PATH_REQUIRED – Require a path after the host of the URL. ( eg, /folder1/folder2/item.html)
     * FILTER_FLAG_QUERY_REQUIRED – Require a query string  at the end of the URL (eg, ?key=value)
     *
     *
     * @return devuelve $url si es una url sino devuelve $default
     */
    static public function url($url, $default = null, $flags = FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED)
    {
        if (isset($url) && filter_var($url, FILTER_VALIDATE_URL, $flags) !== false) {
            return $url;
        } else {
            return $default;
        }
    }

    /**
     * Comprueba si es una $string está entre el tamaño $length_min y $length_max de carácteres
     *
     * @param mixed $string Cadena que se quiere validar
     * @param mixed|false $default es el valor a devolver en caso de que $string no sea válido
     * @param int $length_max número máximo de carácteres que puede tener la cadena
     * @param int $length_min número mínimo de carácteres que puede tener la cadena
     * @example \Team\Data\Check::length('hola mundo', 'adios', 4);       => devuelve 'adios'
     * @emample \Team\Data\Check::length('hola mundo', 'adios', 50);       => devuelve 'hola mundo';
     * @emample \Team\Data\Check::length('hola mundo', 'adios', 50, 10); => devuelve 'adios'
     *
     * @return devuelve o el $string pasado si es válido o el valor $default sino es válido
     */
    static public function length($string, $default = null, $length_max = 200, $length_min = 0)
    {
        $length_max = (int)$length_max;
        $length_min = (int)$length_min;
        $chars = strlen($string);

        if (isset($string) && $chars <= $length_max && $chars >= $length_min) {
            return $string;
        } else {
            return $default;
        }
    }

    /**
     * Validamos si $email es un correo electrónico válido
     *
     * @param mixed $email es la cadena  ( que se presume que es un correo elecotrónico ) a validar
     * @param mixed|false $default es el valor a devolver en caso de que $email no sea válido
     * @param true|false    establece si se hace comprobación dns de existencia del dominio pertenciente al email.
     * @example \Team\Data\Check::email('miemail')                                                    => devuelve false
     * @example \Team\Data\Check::email('miemail@esteno.es', 10)                                => devuelve 10
     * @example \Team\Data\Check::email('miemail@rayosycentellassss.es', false, true)    => devuelve false
     *
     * @return devuelve $email si es un correo electrónico válido sino devuelve $default
     */
    static public function email($email, $default = null, $strict = false)
    {
        if (isset($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            //Comprobamos que sea un dominio mx correcto. Muy útil para altas de usuarios
            $domain = explode('@', $email);
            if (!$strict || Checkdnsrr($domain)) {
                return $email;
            }
        }

        return $default;
    }

    /**
     * Comprobamos si una cadena es una ip es correcta
     * @param mixed $ip Elemento que se quiere comprobar si es una ip válida
     * @param mixed $default valor a devolver si $ip no es una ip válida
     *
     */
    static public function ip($ip, $default = null)
    {
        if (!isset($ip)) {
            return $default;
        }

        return (false !== filter_var($ip, FILTER_VALIDATE_IP)) ? $ip : false;
    }

    /**
     *  Checks if values of $array is in $keys
     *  This is useful for checkbox validations in forms
     * @param $array array
     * @param $keys array
     */
    static function valuesInKeys($values, $keys, $default = null)
    {
        if (!is_array($values) || !is_array($keys)) {
            return $default;
        }

        $keys = array_keys($keys);

        $common_array = array_intersect($values, $keys);

        if ($common_array == $values) {
            return $values;
        }

        return $default;
    }

    static function __callStatic($name, $arguments)
    {
        return \Team\Data\Filter::apply('\team\data\Check\\' . $name, ...$arguments);
    }

    /**
     * Determines if the variable is a numeric-indexed array.
     *
     *
     * @param mixed $array Variable to check.
     * @return bool Whether the variable is a list.
     */
    function numericArray($array, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }

        $keys = array_keys($array);
        $string_keys = array_filter($keys, 'is_string');
        (count($string_keys) === 0) ?: $default;
    }
}
