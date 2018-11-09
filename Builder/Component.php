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

namespace Team\Builder;

require(_TEAM_ . '/Builder/Builder.php');

/**
 * Es la base para crear componentes virtuales.
 * Es decir, un componente corresponderia con una clase que se crea al vuelo.
 * Al llamar a un método de ese componente virtual lo que hace es llamar a un response
 * del controlador( Gui, Actions ) asociado.
 */
class Component implements \ArrayAccess
{
    use \Team\Data\Box;

    /**
     * Desde el contructor nos toca averiguar si se ha instanciado directamente Component
     * ( este es el caso para las responses main ) o bien forma parte, como padre, de un componente virtual.
     * Ademśa, realizamos las tareas rutinarias de inicialización del componente( se esté abriendo como main o no ).
     */
    function __construct($params = [])
    {
        if ($params instanceof \Team\Data\Type\Base) {
            $params = $params->get();
        }

        $this->set($params);

        /*
            Los controladores virtuales tienen como padre a Component, mientras que en el caso de las respuestas main
            se usara la clase Component como principal. Con lo que deducimos, que si tiene padre es un componente virtual
            y sino tiene padre es Componente tal cual.
            Este trozo de código habría que reemplazarlo si alguna vez hacemos que component herede de otra clase
        */

        $parent = get_parent_class($this);
        $is_component = empty($parent);
        if (!$is_component) {
            //Queremos la clase ( si \Team\users -> Team\users, si \Component -> component )
            $this->namespace = trim(strtolower(get_class($this)), '/');
            list($this->app, $this->component) = explode('\\', trim($this->namespace, '\\'));
        } else {
            $this->namespace = "\\{$this->app}\\{$this->component}";
        }

        $this->path = str_replace("\\", "/", $this->namespace);

        $this->embedded = (bool)\Team\System\Context::getIndex();
        $this->is_main = !$this->embedded;
    }

    static function call($widget_name, $params, $cache = null)
    {
        $cache_id = null;
        if (isset($cache)) {
            $cache_id = \Team\System\Cache::checkIds($cache, $widget_name);

            $cache = \Team\System\Cache::get($cache_id);

            if (!empty($cache)) {
                return $cache;
            }
        }

        //A partir del nombre tenemos que obtener el paquete y el componente al que pertenece el widget
        $namespace = \Team\System\NS::explode($widget_name);

        if (isset($namespace['name'])) {
            $namespace['response'] = $namespace['name'];
            unset($namespace['name']);
        }

        if (empty($namespace['namespace'])) {
            \Team::warning("Review widget name or change \" to ' in your widget name param, please ", 'WIDGET_NAME');
        }

        $params = $namespace + $params;

        //No es una llamada main
        $params['is_main'] = false;
        $params['widget'] = true;

        if (!isset($params['out'])) {
            $params['out'] = 'html';
        }

        $class_name = '\\' . $params['app'] . '\\' . $params['component'];

        if (!class_exists($class_name)) {
            \Team::warning("widget class $class_name not found.  Review widget name or change \" to \' in your widget name param, please ",
                'NO_WIDGET');

            return '';
        }

        $controller = new $class_name($params);
        $widget_content = trim($controller->retrieveResponse());

        if (isset($cache_id)) {
            $cache_time = $params['cachetime'] ?? null;

            \Team\System\Cache::overwrite($cache_id, $widget_content, $cache_time);
        }

        return $widget_content;
    }

    /**
     * El metodo toString llamara al método por defecto
     */
    public function toString()
    {
        return $this->retrieveResponse();
    }

    /**
     * Llamamos a una response que se hapa cargo de las necesidades del llamante
     */
    final function retrieveResponse($response_name = null, $arguments = [])
    {
        \Team\System\Context::open(); //Abrimos un contexto que encapsule la response

        $this->addData($arguments);

        $this->response = \Team\Data\Check::key($response_name, $this->response);

        //Llamamos a un contructor de response, para que se encargue de hacer todo lo necesario para que la petición
        //llegue a este la response adecuado
        $data = $this->getDataObj();
        $response = \Team\System\Task('\team\builders\get_builder', array($this, "_getBuilder"))->with($data);

        $result = $response->buildResponse();

        //Acabamos la encapsulación del contexto de response
        \Team\System\Context::close();

        return $result;
    }

    /**
     * Cualquier llamada a un método de una clase componente(virtual o no) es como una llamada
     * a una response( sea stage, action )  */
    public function __call($response_name, $arguments = null)
    {
        //Si ha habido argumentos, utilizamos sólo el primero.
        if (!empty($arguments)) {
            $arguments = $arguments[0];
        }
        return $this->retrieveResponse($response_name, $arguments);
    }

    /**
     * Factoría que se encargaría de obtener un constructor de respuesta
     * @param $params son los parámetros de construcción de la response
     * @remember:
     * //Si se lanza de fuera, el formato por defecto es html
     * //Si se lanza desde otra response, el formato por defecto es array
     * //Si se lanza desde plantilla, el formato por defecto es html
     * //Si se lanza desde un terminal, el formato será por defecto, terminal
     */
    public function _getBuilder($params)
    {
        $builders = array(
            'html' => '\Team\Builder\Gui',
            'action' => '\Team\Builder\Api'
        );

        //Filtramos por el tipo de salida
        if ($params->is_main) {
            $params->out = \Team\Data\Check::key($params->out, 'html');
        } else {
            $params->out = \Team\Data\Check::key($params->out, 'array');
        }

        if ($params->is_main) {
            //Cogemos dependiendo del tipo de salida. Sino el predeterminado será el de acciones
            $builder = isset($builders[$params->out]) ? $builders[$params->out] : $builders['action'];
        } else {
            $builder = isset($builders[$params->out]) ? $builders[$params->out] : $builders['action'];
        }

        \Team\System\Context::set("out", $params->out);
        \Team\System\Context::set("AJAX", $params->out != 'html' && $params->out != 'array');

        if (class_exists($builder)) {
            \team\Debug::trace("Se usará el siguiente Builder para crear una respuesta con salida {$params->out} ",
                $builder);
            return new $builder($params);
        } else {
            \Team::error("Not found Builder {$builder}", '\team\builders\__get_builder');
            \team\Debug::me("Not found Builder {$builder}", '\team\builders\__get_builder');
            return;
        }
    }
}

