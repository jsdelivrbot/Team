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

require_once(_TEAM_ . '/Controller/Api.php');

class Api extends Builder
{

    public function checkParent($class)
    {
        return is_subclass_of($this->controller, '\team\controller\Api');
    }

    /**
     * Este método permite al Builder saber la clase de respuesta o Controller
     * que debe de instanciar
     */
    public function getTypeController()
    {
        return 'Api';
    }

    public function checkErrors(\team\data\Data $_data)
    {
        //-------Gestion de errores-----------
        $_data->ok = !\Team::getResult();
        $_data->nok = !$_data->ok;

        //------ NOTIFICACIONES -----------
        $_data->notices = array(
            "result" => \Team::getResult(),
            "code" => \Team::getCode(),
            "msg" => \Team::getMsg(),
            "details" => \Team::getDetails()
        );
    }

    public function transform(\team\data\Data &$_data, & $_controller, $_result)
    {
        if (!empty($_result)) {
            //Si lo que se devuelve es un string. Lo consideramos una salida en bruto
            if (is_string($_result)) {
                return $_result;
            }

            //Si es una operacion y se devuelve un array. Se considera ese el resultado
            if (is_array($_result)) {
                $_data->set($_result);
            }
        }

        //	Event("Pre_Out", '\team\actions')->ocurred($_data);
        $_data->out = $_data->out($this->out, [], $isolate = false);
        //	Event("Post_Out", '\team\actions')->ocurred($_data);

        return $_data->out;
    }

    /**
     * Se devuelve un error( para caso de critical )
     */
    public function getCriticalError($SE = null)
    {
        $msg = \Team\Config::get('CRITICAL_MESSAGE', 'We are in maintenance, sorry');

        $_data = new \Team\Data\Data();

        //-------Gestion de errores-----------
        $_data->nok = true;
        $_data->ok = !$_data->nok;

        $result = '';
        $details = '';
        $type = 'system';
        $code = 'critical';
        if (!isset($SE)) {
            $result = \Team::getResult();
            $type = \Team::getType();
            $details = \Team::getDetails();
        }

        //------ NOTIFICACIONES -----------
        $_data->notices = array(
            "result" => $result,
            "msg" => $msg,
            "details" => $details,
            "type" => $type,
            "code" => $code
        );

        return $_data->out($this->out);
    }

    /**
     * Mandamos al navegador los header necesarios
     */
    function sendHeader()
    {
        //header("Content-Type: application/x-www-form-urlencoded;charset=".CHARSET);
        //setlocale(LC_ALL,"es_ES",  "es_ES.UTF-8", "es", "spanish");

        $this->sendHeaderHTTP('application/' . $this->out);
    }

}
