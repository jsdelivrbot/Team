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
 * Filesystem absolute path until root directory of Team Framework
 * ( always without / end )
 * @since 0.1
 */
define('_TEAM_', __DIR__);

//Utilidades sobre el sistema de archivos
require(\_TEAM_ . '/System/FileSystem.php');
//Cargamos la clase Filter que se encarga de las validaciones
require(\_TEAM_ . '/Data/Check.php');
//Filter, permite el filtrado de datos de modo desacoplado.
require(\_TEAM_ . '/Data/Filter.php');
//Trait para las clases que manejan variables de configuración: Config y Context
require(\_TEAM_ . '/Data/Vars.php');
//La clase que gestiona opciones de configuración
require(\_TEAM_ . '/Config.php');

//Classes se encarga de la autocarga y manejo de clases
require(\_TEAM_ . '/Loader/Classes.php');
//Manejo de configuración de locales
require(\_TEAM_ . '/System/I18N.php');
//Plantilla para la gestión fáci de datos de una clase
require(\_TEAM_ . '/Data/Storage.php');
//La clase que gestiona caché
require(\_TEAM_ . '/System/Cache.php');
//La clase Context nos sirve para tener un control de variables de configuracion en funcion del contento
require(\_TEAM_ . '/System/Context.php');
//La clase Team, Notice y Erros llevan un control de las notificaciones de  avisos y errores del sistema
require(\_TEAM_ . '/Notices/Errors.php');
require(\_TEAM_ . '/Notices/Notice.php');
require(\_TEAM_ . '/Team.php');
//La gran clase Data es un gestor de datos y su representación en distintos formatos
require(\_TEAM_ . '/Data/Data.php');
//Para el manejo fácil de namespaces
require(\_TEAM_ . '/System/NS.php');
//Task permite la delegación de tareas
require(\_TEAM_ . '/System/Task.php');
//Cargamos la clase Debug y Log para todo ayudar al programador/maquetador en su tarea.
require(\_TEAM_ . '/Notices/Log.php');
require(\_TEAM_ . '/Debug.php');

if (!class_exists('Debug', false)) {
    class_alias('\team\Debug', 'Debug', false);
}

//Añadimos la clase para gestionar componentes virtualmente
require(\_TEAM_ . '/Builder/Component.php');
//Clase que sirve de clase base para los controladores
require(\_TEAM_ . '/Controller/Controller.php');
//Clase que hace funciones de limpieza
require(\_TEAM_ . '/Data/Sanitize.php');
//Clase que maneja cabeceras http
require(\_TEAM_ . '/Client/Http.php');
//Clase que maneja base de datos
require(\_TEAM_ . '/System/DB.php');

try {
    require \_TEAM_ . '/Predefined/config.inc.php';
    require \_TEAM_ . '/Predefined/tasks.inc.php';
    require \_TEAM_ . '/Predefined/filters.inc.php';

    //Llamamos para que el proyecto inicie sus config, tasks, filters, ...
    \Team\System\FileSystem::load('/config/setup.php', \Team\_SERVER_);
    \Team\System\FileSystem::load('/' . \Team\Config::get('ENVIRONMENT') . '/setup.php', \Team\_CONFIG_);

    require \_TEAM_ . '/Predefined/system.inc.php';
//Evitamos a toda costa que se quede congelado el sistema
} catch (\Throwable $e) {
    \Team::critical($e);
}

function up()
{
    \Team\Debug::trace();
    try {
        \Team\Debug::trace("Se inicializo el contexto. Ya podemos empezar a inicializar todo el framwork");

        /**
         * 6. Se levanta el sistema MVC
         */
        \Team::event('\team\start');

        /**
         * 7. Se parsea los parámetros de entrada
         */
        $REQUEST_URI = \Team\Data\Filter::apply('\team\request_uri', $_SERVER["REQUEST_URI"]);
        $args = \Team\System\Task('\team\url', array())->with($REQUEST_URI);

        /**
         * 8. Se llama al encargado( un componente o función __main ) de procesar el primer response o main
         */
        $result = \Team\System\Task('\team\main', '')->with($args);

        \Team\Debug::trace("Se acabó, ya hemos realizado todas las operaciones pedidas. Bye!");

        /**
         * 9. Se acaba de procesar y se devuelve la respuesta
         */
        \Team::event('\team\end', $result);

        return $result;
        //Evitamos a toda costa que se quede congelado el sistema
    } catch (\Throwable $e) {
        \Team::critical($e);
    }
}
