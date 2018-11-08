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

namespace Team\Notices;

class Notice
{
    /** Tipos de avisos finales. Además de los finales están los intermedios: Warnings, Infos y Events */
    const SUCCESS = 0;
    const ERROR = 1;
    const SYSTEM = 2;

    /**
     * listado de avisos(  array("result" => 0, "msg" => "", 0 => array(Infos) , 1 => array(Notices) ) )
     * result: es el resultado de la operacion: 0  ( success ) , 1 ( Error ), 2 ( ErrorSystem )
     * msg: Es el mensaje de éxito o fracaso
     */
    private $result = 0;
    private $type = 0;
    private $code = '';
    private $msg = '';
    private $details = [];
    private $INFOS = [];
    private $WARNINGS = [];

    /**
     * Se acabó el proceso todo OK
     */
    public function success($msg, $code = null, $data = null)
    {
        $this->addNotice(self::SUCCESS, 'SUCCESS', $data, $code, $msg, $this->INFOS);

        return true;
    }

    /**
     * Adding a notice
     */
    private function addNotice($result, $type, $data, $code, $msg, $details)
    {
        // Avisamos del error
        $canceled = null;

        if ($code) {
            if (isset($code)) {
                $canceled = \Team::event($code, $type, $data, $msg);
            }
        }

        $canceled = ($canceled) ?: \Team::event('\team\\' . strtolower($type), $code, $data, $msg);
        if ($canceled) {
            return $canceled;
        }

        $this->result = $result;
        $this->type = $type;
        $this->code = $code;
        $this->msg = $msg;
        $this->details = $details;

        return false;
    }

    /**
     * Aviso informativo. Es una notificación de tipo intermedio de carácter postiivo
     */
    public function info($msg)
    {
        $this->INFOS[] = $msg;
    }

    /**
     * Aviso alerta. Es una notificación de tipo intermedio de carácter negativo
     */
    public function warning($msg, $code, $data = null)
    {
        \Team\Debug::me($msg, $code, null, null, 2);
        $this->WARNINGS[$code] = $msg;
    }

    /**
     * Se acabó el proceso con fallo
     */
    public function error($msg, $code = null, $data = null, $file = null, $line = null)
    {
        \Team\Debug::me($msg, $code, $file, $line, 2);
        return $this->addNotice(self::ERROR, 'ERROR', $data, $code, $msg, $this->WARNINGS);
    }


    /** ------------------------------ SETTERS ------------------------------- */

    /**
     * Hubo un error de sistema.
     */
    public function system($msg, $code = null, $data = null, $level = 2, $file = null, $line = null)
    {
        \Team\Debug::me($msg, $code, $file, $line, $level);

        $canceled = $this->addNotice(self::SYSTEM, 'SYSTEM', $data, $code, $msg, [$code => $msg]);

        if ($canceled) {
            return true;
        }

        if ($file === null || $line === null) {
            \Team\Debug::getFileLine($file, $line, $level);
        }

        $e = new \Team\System\Exception\System_Error($this->msg, $code);
        $e->setFile($file);
        $e->setLine($line);
        $e->setType('SYSTEM');
        throw $e;
    }

    /** ---------------------------- GETTERS --------------------------------- */

    public function get()
    {
        return [
            'result' => $this->result,
            'type' => $this->type,
            'code' => $this->code,
            'msg' => $this->msg,
            'details' => $this->details,
        ];
    }

    public function getMsg()
    {
        return $this->msg;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result)
    {
        return $this->result = $result;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getDetails()
    {
        return $this->details;
    }

    /** ---------------------------- CHECKERS --------------------------------- */

    public function check()
    {
        return !$this->result;
    }

    public function ok()
    {
        return !$this->result;
    }

    public function nok()
    {
        return $this->result;
    }

    public function clear($code)
    {
        if ($this->had($code)) {
            unset($this->WARNINGS[$code]);
        }
    }

    public function had($code)
    {
        return isset($this->WARNINGS[$code]);
    }

    public function getInfos()
    {
        return $this->INFOS;
    }

    public function getWarnings()
    {
        return $this->WARNINGS;
    }

    public function warnings()
    {
        return !empty($this->WARNINGS);
    }

    public function infos()
    {
        return !empty($this->INFOS);
    }

}