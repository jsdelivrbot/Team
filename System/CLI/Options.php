<?php
/**
 * This file is part of TEAM.
 *
 * TEAM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License.
 *
 * TEAM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TEAM.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Team\System\CLI;

/**
 * This class split argv in args.
 * Class args
 */
class Options
{

    /**
     * Array index for positional arguments
     */
    public const POSITIONALS = 'positionals';

    /**
     * It's the argument prefix that arguments can have in command line.
     * Example: command -arg1 -arg2 -arg3
     * Example2: ls -l
     */
    private const ARGUMENT_PREFIX = '-';

    /**
     * It's the key/value separator..
     * Example: command arg1=value1 arg2=value2
     * Example2: mysql --user=manuel.canga --password=TEAM
     */
    private const KEY_VALUE_SEPARATOR = '=';

    /**
     * There are three modes of passing arguments:
     * - not_spaced:  command -arg1=value1 -arg2=value2 -arg3=value3
     * - spaced, command -arg1 value1 -arg2 value2 -arg3 value3
     * - positional: command arg1 arg2 arg3 ( It's important the order )
     */
    private const TYPES = ['NOT_SPACED' => 'NOT_SPACED', 'SPACED' => 'SPACED', 'POSITIONAL' => 'POSITIONAL'];

    /**
     * Arguments vector.
     * @var array
     */
    private $argv = [];

    /**
     * name for next argument for processing
     * @var string
     */
    private $next_arg_name = '';

    /**
     * Maybe type for current argument.
     * @var null
     */
    private $current_type;

    /**
     * Arguments list found in $argv
     * @var array
     */
    private $args = [];

    /**
     * args constructor.
     * @param array $argv Arguments in line of command.
     */
    public function __construct(array $argv)
    {
        $this->argv = $argv;
    }

    /**
     * There are three modes of passing arguments:
     * - not_spaced:  command -arg1=value1 -arg2=value2 -arg3=value3
     * - spaced, command -arg1 value1 -arg2 value2 -arg3 value3
     * - positional: command arg1 arg2 arg3 ( It's important the order )
     *
     * Hay que tener en cuenta que en los tipos2, se puede omitir el valor si este es igual a 1 o true.
     * command -arg1 -arg2 -arg3
     *
     * Así que nos tocará ir parseando todos los arguementos, descubriendo de que tipo son,
     * para extraer por cada argumento su key/value corréctamente
     *
     * @return array
     */
    public function extract(): array
    {
        $this->args = [];
        $this->current_type = self::TYPES['SPACED'];
        $this->next_arg_name = '';

        foreach ($this->argv as $argument) {
            if (self::ARGUMENT_PREFIX === $argument[0]) {
                $this->createKeyValueOption($argument);
            } else {
                //Si existía un key es que estamos en la segunda vuelta de un argumento espaciado.
                if (!empty($this->next_arg_name) && self::TYPES['SPACED'] === $this->current_type) {
                    $this->args[$this->next_arg_name] = $argument;
                } else { //Lo consideramos como un posicional
                    $this->args[self::POSITIONALS][] = $argument;
                }
                //Ya no tiene sentido mantener el key, porque si era spaced ya se ha tomado su valor
                //si era posicional, no nos ipmorta para nada el key
                $this->next_arg_name = '';
            }
        }


        return $this->args;
    }

    /**
     * This function extracts key/value and then it creates the args.
     *
     * @param string $arg Command line argument for parsing.
     *
     * @return void
     */
    private function createKeyValueOption(string $arg): void
    {
        //remove '-' from argument name. Example: ls -l => -l => l
        $this->next_arg_name = \ltrim($arg, self::ARGUMENT_PREFIX);
        $key_value_exists = strpos($this->next_arg_name, self::KEY_VALUE_SEPARATOR) !== false;


        /* Si a lo mejor era un argumento espaciado, tenemos que esperar a la siguiente vuelta para
        saber su valor. Por tanto, necesitamos mantener el next_arg_name.
        Por contra, para un no espaciado no necestamos guardar su key, además podría confundirnos
        si lo mantenemos */
        if ($key_value_exists) {
            [$current_arg_name, $value] = \explode('=', $this->next_arg_name);
            $this->args[$current_arg_name] = $value;
            $this->current_type = self::TYPES['NOT_SPACED'];
            $this->next_arg_name = '';
        } else {
            $this->current_type = self::TYPES['SPACED'];
            $this->args[$this->next_arg_name] = true;
        }
    }
}
