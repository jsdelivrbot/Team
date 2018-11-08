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
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 5/01/17
 * Time: 16:27
 */

namespace Team\System;

abstract class I18N
{
    public static function setTimezone($timezone = null)
    {
        $timezone = \Team\System\Context::get('TIMEZONE', $timezone, '\Team\System\I18N');

        date_default_timezone_set($timezone);
        ini_set('date.timezone', $timezone);
    }

    public static function setLocale($locale = null)
    {
        $lang = null;
        $charset = null;

        if (isset($locale)) {
            list($lang, $charset) = explode('.', $locale);
        }

        $lang = \Team\System\Context::get('LANG', $lang, '\Team\System\I18N');
        $charset = \Team\System\Context::get('CHARSET', $charset, '\Team\System\I18N');

        $locale = $lang . '.' . $charset;

        setlocale(LC_ALL, $locale);
        putenv('LANG=' . $locale);
        putenv('LANGUAGE=' . $locale);
    }

    static function length($string)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($string, \Team\Config::get('CHARSET'));
        }

        return strlen($string);
    }

}