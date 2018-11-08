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

/**
 * Clase que proporciona metodos utiles para asuntos de seguridad
 */

namespace Team\System;

class Security
{

    /**
     * Devuelve una salt aleatoria de la longitud querida
     * incluye valores alfanuméricos( mayúsculas y minúsculas ) y carácteres especiales
     * Muy útil también para generar passwords
     *
     * @param int $length Longitud para la nueva sal
     * @example \Team\System\Security::getSalt() => devuelve jbjltmnlp ( salt al azar )
     *
     * @return devuelve la salt generada del tamaño especificado
     */
    public static function getSalt(int $length = 32)
    {
        $min_char = ord('!');
        $max_char = ord('}');

        $salt = '';
        for ($i = 0; $i < $length; $i++) {
            $index = random_int($min_char, $max_char);
            $salt .= chr($index);
        }
        return $salt;
    }

    /**
     * Devuelve un password semántico
     *
     * @param int $length longitud de la parte variable( no perteneciente a palabras )
     * @return string
     */
    public static function getPassword($length = 6)
    {
        $words = [
            'acro',
            'anti',
            'auto',
            'cycle',
            'kinesis',
            'less',
            'counter',
            'cosmo',
            'demo',
            'dynam',
            'extra',
            'hyper',
            'mega',
            'mania',
            'maxi',
            'maxi',
            'kilo',
            'milli',
            'multi',
            'ultra',
            'scope',
            'phone',
            'wise',
            'onomy',
            'ology',
            'osis',
            'zoo'
        ];

        $words = \Team\Data\Filter::apply('\team\security\words', $words);

        $prefix = array_rand($words);
        $postfix = array_rand($words);

        $password = $words[$prefix] . self::getToken($length) . $words[$postfix];

        return $password;
    }

    /**
     * Devuelve un token generado aleatoriamente
     * incluye valores  alfanuméricos( mayúsculas y minúsculas )
     *
     * @param int $length Longitud para la nueva sal
     */
    public static function getToken(int $length = 10)
    {
        $token_values = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTVWYXZ";
        $token_values = \Team\Data\Filter::apply('\team\security\token_values', $token_values);

        $min_number = 0;
        $max_number = strlen($token_values) - 1;

        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $index = random_int($min_number, $max_number);
            $token .= $token_values[$index];
        }
        return $token;
    }

}