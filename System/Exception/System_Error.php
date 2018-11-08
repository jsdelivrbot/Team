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

namespace Team\System\Exception;

/**
 * Error de sistema.
 * Se usa para poder recuperar Team de errores graves cometidos por el programador
 */
class System_Error extends \Exception
{
    protected $backtrace = array();
    protected $line;
    protected $file;
    protected $function;
    protected $class;
    protected $namespace = "team";
    protected $type = "SYSTEM";
    protected $codeName = "";
    protected $level = null;

    public function __construct(
        $_msg = null,
        $_code_name = null,
        $_code = null,
        $_file = null,
        $line = null,
        $function = null,
        $class = null
    ) {
        $backtrace = debug_backtrace();
        if (count($backtrace) > 3) {
            $this->backtrace = array_slice(debug_backtrace(), 3);
        } else {
            $this->backtrace = array_slice(debug_backtrace(), 1);
        }

        //Si existe file es proque se ha lanzado directamente desde \team::system y no hace falta asignar nada más
        if (!$this->file) {
            if ($_file) {
                $this->file = $_file;
                $this->line = $_line;
                $this->function = $function;
                $this->class = $class;
            } else {
                $this->file = $this->backtrace[0]["file"];
                $this->line = $this->backtrace[0]["line"];
                $this->function = $this->backtrace[1]["function"];
                if (isset($this->backtrace[1]["class"])) {
                    $this->class = $this->backtrace[1]["class"];
                }
            }
        }

        $this->codeName = $_code_name;
        if (!isset($_code_name)) {
            if (isset(\Team\Notices\Errors::$php_errors_code[$_code])) {
                $this->codeName = \Team\Notices\Errors::$php_errors_code[$_code];
            } else {
                $this->codename = 'E_SYSTEM';
            }
        }

        $this->level = \Team\System\Context::getLevel(); //estado actual
        $this->namespace = \Team\System\Context::get('NAMESPACE');

        parent::__construct($_msg, $_code);
    }

    public function getBacktrace()
    {
        return $this->backtrace();
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($_namespace)
    {
        $this->namespace = $_namespace;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setLine($line)
    {
        $this->line = $line;
    }

    public function setState($state)
    {
        $this->level = $state;
    }

    public function & get()
    {
        $info = new \Team\Data\Data();
        $info->msg = $this->getMessage();
        $info->line = $this->getLine();
        $info->file = $this->getFile();
        $info->function = $this->getFunction();
        $info->class = $this->getClass();
        $info->codeName = $this->getCodeName();
        $info->code = $this->getCode();
        $info->state = $this->getState();

        return $info;
    }

    public function getFunction()
    {
        return $this->function;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getCodeName()
    {
        return $this->codeName;
    }

    public function getState()
    {
        return $this->level;
    }

    public function debug($msg = "System_Error")
    {
        \Team\Debug::me("[{$this->namespace}][{$this->codeName}]: {$this->message}", $msg, $this->file, $this->line);
    }
}
