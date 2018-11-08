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

require_once(_TEAM_ . '/Controller/Gui.php');

class Gui extends Builder
{

    public function checkParent($class)
    {
        return is_subclass_of($this->controller, '\team\controller\Gui');
    }

    /**
     * Este método permite al Builder saber la clase de respuesta o Controller
     * que debe de instanciar
     */
    public function getTypeController()
    {
        return 'Gui';
    }

    public function checkErrors(\team\data\Data $_data)
    {
        //-------Gestion de errores-----------
        $ok = !\Team::getResult();
        $nok = !$_data->ok;

        //------ NOTIFICACIONES -----------
        $notices = array(
            "result" => \Team::getResult(),
            "msg" => \Team::getMsg(),
            "details" => \Team::getDetails()
        );

        $_data['_']['ok'] = $ok;
        $_data['_']['nok'] = $nok;
        $_data['_']['NOTICES'] = $notices;
    }

    public function transform(\team\data\Data &$_data, $_controller, $_result = null)
    {
        $hubo_resultado_devuelto_por_response = isset($_result) && is_string($_result);

        //Si es una gui y se devuelve un valor string, se considera eso la salida html
        //Antes se renderizaba la salida, pero era un derroche tremendo de recursos
        //Por no hablar que un response podía devolver una salida ya renderizada(de, por ejemplo, un widget )
        //y entonces había un doble renderizado.
        if ($hubo_resultado_devuelto_por_response) {
            return $_result;
        }

        /** Añadimos las variables del sistema */
        $out = '';
        $params = $_controller->getParams();

        //Sólo para html añadimos los argumentos de la GUI
        $_data['_'] = $params;

        $out = $_data->out($this->out, [], $isolate = false);

        return $out;
    }

    /**
     * Se devuelve un valor por defecto.
     */
    public function getCriticalError($SE = null)
    {
        //Para las GUI no podemos mostrarle un aviso de error del sistema, hemos de enviar el error al programador cliente para que lo maneje.
        if (!$SE instanceof \Team\System\Exception\System_error) {
            $SE = new \Team\System\Exception\System_Error($SE->getMessage(), '\team\views\errors', $SE->getCode(),
                $SE->getFile(), $SE->getLine() /*, $SE->getFunction()*/);
            //Guardamos el namespace actual
            $SE->setNamespace(\Team\System\Context::get('NAMESPACE'));
        }
        \Team::systemException($SE);

        return '';
    }

    function sendHeader()
    {
        //header("Content-Type: application/x-www-form-urlencoded;charset=".CHARSET);
        //setlocale(LC_ALL,"es_ES",  "es_ES.UTF-8", "es", "spanish");

        $this->sendHeaderHTTP('text/html');
    }
}
