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

class Errors
{

    public static $php_errors_code = array(
        E_ERROR => "E_ERROR",
        E_WARNING => "E_WARNING",
        E_PARSE => "E_PARSE",
        E_NOTICE => "E_NOTICE",
        E_CORE_ERROR => "E_CORE_ERROR",
        E_CORE_WARNING => "E_CORE_WARNING",
        E_COMPILE_ERROR => "E_COMPILE_ERROR",
        E_COMPILE_WARNING => "E_COMPILE_WARNING",
        E_USER_ERROR => "E_USER_ERROR",
        E_USER_WARNING => "E_USER_WARNING",
        E_USER_NOTICE => "E_USER_NOTICE",
        E_STRICT => "E_STRICT",
        E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
        E_DEPRECATED => "E_DEPRECATED",
        E_USER_DEPRECATED => "E_USER_DEPRECATED"
    );
    /** Errores posibles en php. Si tiene asociado un 1 es que puede continuar a pesar del error. 0 es que no es posible continuar */
    public $php_errors = array(
        E_ERROR => 0,
        E_WARNING => 1,
        E_PARSE => 0,
        E_NOTICE => 1,
        E_CORE_ERROR => 0,
        E_CORE_WARNING => 1,
        E_COMPILE_ERROR => 0,
        E_COMPILE_WARNING => 1,
        E_USER_ERROR => 0,
        E_USER_WARNING => 1,
        E_USER_NOTICE => 1,
        E_STRICT => 1,
        E_RECOVERABLE_ERROR => 0,
        E_DEPRECATED => 1,
        E_USER_DEPRECATED => 1
    );

    public function PHPError($_errno, $_errstr, $_errfile, $_errline, $_context = array())
    {
        $namespace = \Team\System\Context::get('NAMESPACE');
        $is_critical = true;
        $_errorcode = self::$php_errors_code[$_errno] ?? 'CRITICAL';

        if ($this->isViewError($_context)) {
            return $this->showViewError($_errno, $_errstr, $_errfile, $_errline, $_context, $_errorcode);
            //Comprobamos si es uno de los errores que bloquean la continuación de la ejecución
        } else {
            if (!$this->php_errors[$_errno]) {
                $is_critical = true;

                //Nuestro último intento de salvar el sistema, probamos a visualizar un mensaje de error
                if (empty($_context) && class_exists('\Context') && \Team\System\Context::getIndex() > 1) {
                    $builder = \Team\System\Context::get('CONTROLLER_BUILDER');

                    $result = '';
                    if (isset($builder)) {
                        $result = $builder->error();
                        echo $result;
                        $is_critical = false;
                    }
                }
            } else {
                //Es un fallo genérico que puede continuarse
                $is_critical = false;
            }
        }

        \Team\Debug::me($_context, "[{$namespace}][" . $_errorcode . "]: {$_errstr} ", $_errfile, $_errline);

        if ($is_critical) {
            return \Team::Critical();
        } else {
            return true;
        }
    }

    /**
     * Comprobamos si hubo un error asociado a las vistas
     */
    private function isViewError($context)
    {
        return isset($context["_smarty_tpl"]);
    }

    /**
     * Mostramos un error producido por las vistas
     */
    private function showViewError($_errno, $_errstr, $_errfile, $_errline, $_context, $_errorcode)
    {
        $error_reporting_template = \Team\Config::get('VIEWS_ERROR_LEVEL', E_ALL & ~E_NOTICE);

        /**
         * Si se escogio desde las opciones de configuración mostrar el error de las vistas, lo hacemos. Sino, lo dejamos pasar
         */
        if ($_errno & $error_reporting_template) {
            $template = $_context["_smarty_tpl"]->_current_file;
            $_errline = "..";
            $_errfile = $file;

            \Team\Debug::me($_context, "[{$namespace}][" . $_errorcode . "]: {$_errstr} in template  {$template} ",
                $_errfile, $_errline);
        } else {
            return true;
        }
    }

    /**
     * Cuando hay un error critico de PHP( ej: FATAL ) o cuando hay un error en un Controller del primer contexto
     * Finalmente cuando se acaba el proceso PHP( ya que es la única oportunidad que queda a veces para recoger errores )
     */
    public function critical($e = null)
    {
        static $num_criticals = 0;

        $error = error_get_last();
        $data = new \Team\Data\Data();
        $data->namespace = \Team\System\Context::get('NAMESPACE');
        $critical = true;

        if (isset($error['type']) && $error['type'] >= 0) {
            $_errno = $error['type'];

            //Hemos podido llegar aquí por culpa de algun error en zona no-framework de algún usuario
            //y este error podría no ser crítico
            if (isset(self::$php_errors_code[$_errno])) {
                $data->code = self::$php_errors_code[$_errno];
                $critical = !self::$php_errors_code[$_errno];
            } else {
                $data->code = $error['type'];
            }

            $data->msg = $error['message'];
            $data->file = $error['file'];
            $data->line = $error['line'];
        } else {
            if (isset($e) && $e instanceof \Throwable) {
                $data->msg = $e->getMessage();
                $data->line = $e->getLine();
                $data->file = $e->getFile();
                $data->code = $e->getCode();
            } else {
                //framework's halting
                \Team\System\Context::close();
                return \Team::event('\team\halt', $data);;
            }
        }

        if ($critical) {
            $data->num_criticals = ++$num_criticals;

            //Only one critical, please
            if ($data->num_criticals > 1 && empty($error)) {
                return false;
            }
        }

        \team\Debug::me("[{$data->namespace}][{$data->code}]: {$data->msg}", '', $data->file, $data->line);

        if (!$critical) {
            return true;
        }

        //Asignamos un contextlevel para que quien lo recoja sepa si es o no main dónde se produjo el error
        $data->context = new \Team\Data\Data(\Team\System\Context::getContext());
        $data->level = \Team\System\Context::getIndex();
        if (\Team\System\Context::get('out') != 'html') {
            $context_main = \Team\System\Context::before();
            //Si el método main no es el que ha cascado, llamamos a su método critical para que lo arregle todo.
            $builder = \Team\System\Context::get('CONTROLLER_BUILDER');

            if (isset($builder)) {
                echo $builder->getCriticalError($data);
                die();
            }
        }
        //Mala suerte, hemos llegado hasta aquí sin que la acción main pudiera hacer nada. Toca buscar ayuda(¿algún awaiter disponible?)
        $type = 'CRITICAL';
        echo \Team::event('\team\CRITICAL', $data, $type);

        return true;
    }

}
