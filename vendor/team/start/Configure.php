<?php

namespace team\start;


class Configure
{

    /**
     * preConfigure the framework enviroment
     *
     */
    function preconfigure() {

        $this->preconfigureEnviroment();
        $this->preconfigureUrlConfigs();

        //Añadimos las constantes que hubiera como variables de configuración
        \team\Config::set(get_defined_constants(true)['user']);
    }

    private function preconfigureEnviroment() {
        //Cargamos la clase Log para todo ayudar al programador/maquetador en su tarea.
        \team\loader\Classes::add('\team\Log', '/classes/notices/Log.php', _TEAM_);

        \team\Config::set('TRASWEB', 'dev');
        \team\Config::set('_THEME_', \_SCRIPT_.'/themes/default');

        \team\Config::set('LANG', 'es_ES');
        \team\Config::set('CHARSET', 'UTF-8');
        \team\Config::set('TIMEZONE', 'Europe/Madrid');

        //Motor que se usara para procesar las vistas
        \team\Config::set('HTML_ENGINE',"TemplateEngine");


        //Es posible lanzar TEAM framework desde terminal
        //Así que comprobamos si se está haciendo
        global $argv, $argc;
        $cli_mode = true;
        if('cli' != php_sapi_name() || empty($argv) || 0 == $argc  ) {
            $cli_mode = false;
        }

        \team\Debug::trace('¿Cli mode activo?', $cli_mode);
        \team\Config::set('CLI_MODE',   $cli_mode );
    }


    private function preconfigureUrlConfigs() {

        //Avoid proxys domains
        \team\data\Filter::add('\team\request_uri', function($url) {
               return  parse_url($url, PHP_URL_PATH);
        });

        /*
         * Un area se marca a traves de una url base.
         * Todas las peticiones webs que contengan esa url base formarán parte de esa zona.
         * A cada area( o zonas) se le puede asignar un target( /package/component ) que la procese.
         * El area vacía o con valor '/', se refiere al area principal. Pues todas las peticiones dependerán de ella
         *
         * Las areas más especificas( mayor path ) tienen prioridad sobre las más globales( menor path )
         */
        \team\Config::set('AREAS',  ['/' =>  '/web/welcome'] );


        $method  = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']?? $_POST['_method']?? $_SERVER["REQUEST_METHOD"];
        \team\Config::set('REQUEST_METHOD', strtoupper($method));

        $port = $_SERVER['SERVER_PORT']?? 80;


        $is_ssl = false;
        if ( isset($_SERVER['HTTPS']) &&  \team\data\Check::choice($_SERVER['HTTPS']) ) {
            $is_ssl = true;
        } elseif (  '443' == $port  ) {
            $is_ssl = true;
        }

        \team\Config::set('IS_SSL', $is_ssl );
        \team\Config::set('PROTOCOL', $is_ssl? 'https://' : 'http://' );
        \team\Config::set('DOMAIN',  trim($_SERVER["SERVER_NAME"], '/') );
        \team\Config::set('PORT', $port);

        \team\Config::addModifier('WEB', function($url){
            if(isset($url)) return $url;

            $domain = \team\system\Context::get('DOMAIN');

            $port = \team\system\Context::get('PORT');
            $with_port = '';
            if('80' != $port && '443' != $port) {
                $with_port = ":{$port}";
            }

            $protocol =  \team\system\Context::get('PROTOCOL');
            $domain = rtrim($domain, '/');



            return $url = "{$protocol}{$domain}{$with_port}";
        });


    }




    /**
     * Llamamos a los scripts de comienzos.
     * Estos scripts deberían de asignar filtros, eventos y tareas deseados
     */
    function launchConfigScripts() {
        \team\system\FileSystem::load('/start/Start.php', _TEAM_);
        \team\system\FileSystem::load('/config/setup.php', \team\_SERVER_);
        \team\system\FileSystem::load('/'. \team\Config::get('TRASWEB').'/setup.php', \team\_CONFIG_);
    }



    /**
     *  Definimos un autoload de clases
     *
     *  Por cada clase desconocida que se instancie o se utilice sin haberse procesado, php llamara a Classes.
     *  Este método define un autoloader por defecto llamado Casses y avisa a php para que lo utilice
     */

    function registerAutoload() {
        spl_autoload_register(\team\Config::get('\team\autoload', ['\team\loader\Classes', 'factory'] ));
    }

    function cachingSystem() {
        \team\system\Cache::__initialize();
    }


    function system() {
        \team\Config::setUp();
        \team\system\I18N::setTimezone();
        \team\system\I18N::setLocale();

        //Sistema de errores
        \Team::__initialize();

        //Añadimos la clase que gestiona los datos de session
        \team\loader\Classes::load('\team\client\User', '/client/User.php', _TEAM_);
    }
}