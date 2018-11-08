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
 * Funciones útiles para manejo de namespaces
 */
class NS
{

    /**
     * Convierte un namespace en path
     * @param namespace $namespace cadena namespace que se quiere pasar a path
     * @example: \Team\System\NS::toPath("\Team\news\index")  -> /Team/news/index
     */
    public static function toPath($namespace)
    {
        $path = str_replace('\\', '/', $namespace);

        $path = \Team\Data\Sanitize::trim($path, '/');

        return $path;
    }

    /**
     * Transforma el path relativo de un archivo a namespace
     * @param $file nombre de archivo con su path
     * @example: \Team\System\NS::fileToNS("/Team/news/index.html")  -> \Team\news
     */
    public static function fileToNS($file)
    {
        $path = basename($file);
        return self::pathToNS($path);
    }

    /**
     * Convierte un path a namespace
     * @param path $path cadena path que se quiere transformar a string
     * @example: \Team\System\NS::toPath("/Team/news/index")  -> \Team\news\index
     */
    public static function pathToNS($path)
    {
        $path = trim($path, '/');
        return '\\' . str_replace('/', '\\', $path);
    }

    /**
     * Convierte una cadena(normalmente, namesapace o path) a uno más manejable/friendly
     * @param string $str cadena que se quiere transformar
     * @param char $separator carácter separador de elementos en $str ( \ para namespace, / para path )
     * @example: \Team\System\NS::friendly("\Team\news\Evento") ->  "team_news_Evento"
     */
    public static function friendly($str = '', $separator = '\\')
    {
        $str = trim($str, $separator);

        return str_replace($separator, '_', $str);
    }

    /**
     * Bajamos un nivel del namespace/path pasado.
     * @param string $str cadena a la que queremos bajar un nivel
     * @param char $separator carácter separador de elementos en $str ( \ para namespace, / para path )
     * @example NS::shift("\Team\news") -> "\Team";
     */
    public static function shift($str, $separator = '\\', &$level = null)
    {
        $str = trim($str, $separator);

        //Dividimos el array
        $str = explode($separator, $str);

        //Nos quitamos el último elemento del namespace
        array_pop($str);

        if ($level) {
            $level = count($str);
        }

        return '\\' . implode($separator, $str);
    }

    /**
     * Obtiene el último elemento de un namespace o un path ( muy util para extraer nombre de
     * clases, ideal para las Tasks )
     * @param string $str cadena de la que queremos bajar el último elemento.
     * @param char $separator carácter separador de elementos en $str ( \ para namespace, / para path )
     * @example NS::basename("\Team\news") -> "news";
     * @example NS::basename("/Team/news/data/Prueba.jpg") -> "prueba.jpg"
     */
    public static function basename($str, $separator = '\\')
    {
        $str = trim($str, $separator);

        //Dividimos el array
        $str = explode($separator, $str);

        return array_pop($str);
    }

    /**
     * Separa el namespace en sus paquetes
     * @param string $namespace cadena a la que extraeremos todos sus elementos
     * @param char $separator carácter separador de elementos en $namespace ( \ para namespace, / para path )
     * @example NS::explode("\Team\news\index") -> package="Team",component="news", response="main", others=array()
     */
    public static function explode($namespace, $separator = '\\')
    {
        //Si ya es un array, aprovechamos eso, sino hay que crearlo
        if (!is_array($namespace)) {
            $namespace = trim($namespace, $separator);
            $namespace = explode($separator, $namespace);
        }

        //Nos aseguramos que todos los valores son correctos
        $namespace = array_filter($namespace, function ($value) {
            return \Team\Data\Check::key($value, false);
        });

        //Obtenemos el paquete, componente y acción del namespace
        list($app, $component) = array_pad($namespace, 3, null);

        $others = array_slice($namespace, 2);

        $name = array_pop($others);
        if (empty($name)) {
            $name = null;
        }

        $path = '/' . implode('/', $namespace);

        return array(
            'namespace' => $namespace,
            'app' => $app,
            'component' => $component,
            'name' => $name,
            'others' => $others,
            'path' => $path
        );
    }
}
