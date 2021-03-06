<?php
/**
New Licence bsd:
Copyright (c) <2012>, Manuel Jesus Canga Muñoz
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright
notice, this list of conditions and the following disclaimer in the
documentation and/or other materials provided with the distribution.
 * Neither the name of the trasweb.net nor the
names of its contributors may be used to endorse or promote products
derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Manuel Jesus Canga Muñoz BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

 */

namespace Team\Controller;


/**
Representa los datos que llegaran a utilizarse en la vista para formar la web
Es la base para las acciones tipo GUI
 */
class Gui extends Controller {
    use \Team\Gui\Assets;
    use \Team\Gui\View;
    use \Team\Gui\Seo;

    const TYPE = 'Gui';



    /* ____________ METHODS DE EVENTOS BASES DEL FRAMEWORK___________ */


    function ___load($response) {


        if($this->parent() === null) {

            //Add Default template and layout
            $this->setView(\Team\System\Context::get('RESPONSE'));

            //Por defecto, no habrá layout
            $this->noLayout();

        }

        if($this->isMain() ) {
            $this->includeFile(_SCRIPTS_.\Team\Config::get('_THEME_').'/setup.php');
        }



        parent::___load($response);

    }


    function ___unload($result, $response) {
        $result = parent::___unload($result, $response);


        if($this->isMain() && $this->parent() === null) {
            $this->includeFile(_SCRIPTS_.\Team\Config::get('_THEME_').'/' . $this->getComponent() . '/setup.php');
        }

        return $result;
    }



    /* ____________ Views / Templates___________ */


    public function addClassToWrapper($class, $wrapper, $order = 50) {
        $pipeline = '\team\gui\wrappers\\'.$wrapper;

        return \Team\Data\Filter::add($pipeline,function($classes) use ($class) {
            return trim($classes.' '.$class);
        }, $order);
    }

    /* ____________ Contextos ___________ */

    public function getContext($var, $default = null) {
        return \Team\System\Context::get($var, $default);

    }
    public function setContext($var, $value) {
        \Team\System\Context::set($var, $value);
    }

    /* ____________ UserAgent ___________ */

    function getNavigator() {return  \Team\Client\Http::checkUserAgent('navigator'); }

    function getDevice() {return  \Team\Client\Http::checkUserAgent('device'); }

    function isMobile() { return  \Team\Client\Http::checkUserAgent('mobile'); }

    function isDesktop() { return  \Team\Client\Http::checkUserAgent('desktop'); }

    function isComputer() { return  \Team\Client\Http::checkUserAgent('computer'); }

    function isTablet() { return  \Team\Client\Http::checkUserAgent('tablet'); }

    function addBodyClass($class = '', $overwrite = false) {
        if($overwrite) {
            \Team\System\Context::set('BODY_CLASSES', [$class]);
        }else {
            \Team\System\Context::push('BODY_CLASSES', $class);
        }
    }



    /* ____________ Helpers ___________ */

    /**
    El metodo tostring mostraria la web en html
     */
    public function __toString() { return $this->data->out("html");}
}