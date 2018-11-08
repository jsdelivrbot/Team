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

namespace Team\Controller;

/**
 * Representa los datos que llegaran a utilizarse en la vista para formar la web
 * Es la base para las acciones tipo GUI
 */
class Gui extends Controller
{
    use \Team\Gui\Assets;
    use \Team\Gui\View;
    use \Team\Gui\Seo;

    const TYPE = 'Gui';

    /* ____________ METHODS DE EVENTOS BASES DEL FRAMEWORK___________ */

    function ___load($response)
    {
        if ($this->parent() === null) {
            //Add Default template and layout
            $this->setView(\Team\System\Context::get('RESPONSE'));

            //Por defecto, no habrá layout
            $this->noLayout();
        }

        if ($this->isMain()) {
            $this->includeFile(_SCRIPTS_ . \Team\Config::get('_THEME_') . '/setup.php');
        }

        parent::___load($response);
    }

    function ___unload($result, $response)
    {
        $result = parent::___unload($result, $response);

        if ($this->isMain() && $this->parent() === null) {
            $this->includeFile(_SCRIPTS_ . \Team\Config::get('_THEME_') . '/' . $this->getComponent() . '/setup.php');
        }

        return $result;
    }

    /* ____________ Views / Templates___________ */

    public function addClassToWrapper($class, $wrapper, $order = 50)
    {
        $pipeline = '\team\gui\wrappers\\' . $wrapper;

        return \Team\Data\Filter::add($pipeline, function ($classes) use ($class) {
            return trim($classes . ' ' . $class);
        }, $order);
    }

    /* ____________ Contextos ___________ */

    public function getContext($var, $default = null)
    {
        return \Team\System\Context::get($var, $default);
    }

    public function setContext($var, $value)
    {
        \Team\System\Context::set($var, $value);
    }

    /* ____________ UserAgent ___________ */

    function getNavigator()
    {
        return \Team\Client\Http::checkUserAgent('navigator');
    }

    function getDevice()
    {
        return \Team\Client\Http::checkUserAgent('device');
    }

    function isMobile()
    {
        return \Team\Client\Http::checkUserAgent('mobile');
    }

    function isDesktop()
    {
        return \Team\Client\Http::checkUserAgent('desktop');
    }

    function isComputer()
    {
        return \Team\Client\Http::checkUserAgent('computer');
    }

    function isTablet()
    {
        return \Team\Client\Http::checkUserAgent('tablet');
    }

    function addBodyClass($class = '', $overwrite = false)
    {
        if ($overwrite) {
            \Team\System\Context::set('BODY_CLASSES', [$class]);
        } else {
            \Team\System\Context::push('BODY_CLASSES', $class);
        }
    }



    /* ____________ Helpers ___________ */

    /**
     * El metodo tostring mostraria la web en html
     */
    public function __toString()
    {
        return $this->data->out("html");
    }
}