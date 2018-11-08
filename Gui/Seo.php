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

namespace Team\Gui;

/**
 * Funciones útiles para SEO.
 * Es necesario que aquí se guarde en Config pero se recupere como Contexto.
 * Hay que hacerlo por Config para que distintos niveles de Gui puedan añadir elementos que se recogeran a un mismo nivel.
 * Hay que recogerlo como Contexto porque no sólo las Gui generan tpl, también la clase Template, por ejemplo.
 * Si tomaramos los datos desde Config obtendríamos ls mismos recursos sea la plantilla que sea y eso no es correcto.
 *
 * Class Seo
 * @package team\gui
 */
trait Seo
{
    /** -------------------- Breadscrumb --------------------  */
    public function addCrumb($name, $link = '#', $classes = '')
    {
        \Team\Config::push('BREADCRUMB', ['name' => $name, 'url' => $link, 'classes' => $classes]);
    }

    /**
     * Añade una metaetiqueta SEO
     * $this->seo('description', 'Hola Mundo');
     */
    function seo($key, $value, $options = null)
    {
        if (isset($options)) {
            \Team\Config::add('SEO_METAS', $key, ['value' => $options, 'options' => $options]);
        } else {
            \Team\Config::add('SEO_METAS', $key, $value);
        }
    }

    /**
     * Asign a value to SEO_TITLE
     * @param string $title webpage title
     * @param ?bool $separator false(not separator), true(with separator), null(remove previous title )
     *
     */
    public function setTitle($title, $separator = true, $after = false)
    {
        $SEO_TITLE = \Team\System\Context::get('SEO_TITLE', '');

        if (null === $separator || !$SEO_TITLE) {
            $SEO_TITLE = $title;
        } else {
            if (!$after) {
                $SEO_TITLE = $title . ' ' . ($separator ? \Team\Config::get('SEO_TITLE_SEPARATOR', '-',
                        'setTitle') : '') . ' ' . $SEO_TITLE;
            } else {
                $SEO_TITLE = $SEO_TITLE . ' ' . ($separator ? \Team\Config::get('SEO_TITLE_SEPARATOR', '-',
                        'setTitle') : '') . ' ' . $title;
            }
        }

        \Team\System\Context::set('SEO_TITLE', $SEO_TITLE);

        return $SEO_TITLE;
    }
}
