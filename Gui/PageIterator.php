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
 * Simple iterator for pages( pagination )
 */
class PageIterator implements \ArrayAccess, \Iterator, \Countable
{
    use \Team\Data\Storage;

    protected $iteratorPage = 0;
    protected $index = 1;
    protected $currentPage = 1;

    function __construct($pagination)
    {
        if (isset($pagination['page'])) {
            $this->currentPage = $pagination['page'];
        }

        $this->data = $pagination;
        $this->data['goFirst'] = $this->goFirst();
        $this->data['goEnd'] = $this->goEnd();
        $this->data['goPrev'] = $this->goPrev();
        $this->data['goNext'] = $this->goNext();
    }

    /**
     * Comprueba si es posible avanzar hacia la página primera
     * @return false si no es posible avanzar hacia la primera página(porque ya estamos en ella ). Si se puede, devuelve la url de la primera página
     */
    public function goFirst()
    {
        if ($this->start === 1) {
            return false;
        }
        return $this->getPagedUrl(['page' => null]);
    }

    public function getPagedUrl($vars = [])
    {
        return \Team\Client\Url::to($this->baseUrl, $vars + $this->data);
    }

    /**
     * Comprueba si es posible avanzar hacia la última página
     * @return false si no es posible avanzar hacia la última página(porque ya estamos en ella ). Si se puede, devuelve la url de la última página
     */
    public function goEnd()
    {
        if ($this->end === $this->pages) {
            return false;
        }

        return $this->getPagedUrl(['page' => $this->pages]);
    }

    /**
     * Comprueba si es posible retroceder hacia la página previa
     * @return false si no es posible retroceder hacia la anterior página(porque estamos en la primera ). Si se puede, devuelve la url de la anterior página
     */
    public function goPrev()
    {
        if ($this->currentPage === 1) {
            return false;
        }

        return $this->getPagedUrl(['page' => $this->prev]);
    }


    /** -------------- Countable -------------- */

    /**
     * Comprueba si es posible avanzar hacia la página siguiente
     * @return false si no es posible avanzar hacia la siguiente página(porque estamos en la última ). Si se puede, devuelve la url de la página siguiente.
     */
    public function goNext()
    {
        if ($this->currentPage === $this->pages) {
            return false;
        }

        return $this->getPagedUrl(['page' => $this->next]);
    }


    /** -------------- Iterator -------------- */

    /**
     * Al hacer un count sobre la paginación se obtiene el número de páginas que hay
     */
    public function count()
    {
        return $this->pages;
    }

    public function current()
    {
        return $this;
    }

    public function key()
    {
        return $this->getPagedUrl();
    }

    //Pasamos a la siguiente página
    public function next()
    {
        ++$this->index;
        ++$this->iteratorPage;
        $this->iteratorConfig();

        return $this->iteratorPage;
    }

    /**
     * Como valor siempre se devolverá el objeto de paginación. Así siempre podrá llamar fácilmente
     * a los métodos. Si se desea imprimir el valor de la página actual, se lanza toString, que la mostrará
     */

    public function IteratorConfig()
    {
        $this->page = $this->iteratorPage;
        $this->classes = $this->getClasses();

        if (1 === $this->iteratorPage) {
            $this->url = $this->getPagedUrl(['page' => null]);
        } else {
            $this->url = $this->getPagedUrl();
        }
    }

    public function getClasses($extra = '')
    {
        $classes = '';
        if ($this->isCurrent()) {
            $classes .= 'current active ';
        }
        if ($this->isFirst()) {
            $classes .= 'first ';
        }
        if ($this->isLast()) {
            $classes .= 'last ';
        }

        $classes .= ' page-' . $this->iteratorPage;
        $classes .= ' pos' . $this->index;
        if (!empty($extra)) {
            $classes .= ' ' . $extra;
        }

        return trim($classes);
    }

    /* ------------ ITERATOR HELPERS ---------------- */

    public function isCurrent()
    {
        return $this->current = ($this->iteratorPage === $this->currentPage);
    }

    public function isFirst()
    {
        return $this->first = ($this->iteratorPage == $this->start);
    }

    public function isLast()
    {
        return $this->last = ($this->iteratorPage == $this->end);
    }

    /**
     * Construimos la paginación si aún no estaba
     */
    public function rewind()
    {
        $this->iteratorPage = max($this->start, 1);
        $this->iteratorConfig();
        $this->index = 1;
    }

    //Comprobamos si la página actual es la primera

    public function valid()
    {
        $overflow = $this->iteratorPage > $this->end;
        if ($overflow) {
            $this->iteratorPage = 0;
        }
        return !$overflow;
    }

    //Comprobamos si la página actual es la última

    public function __toString()
    {
        return '' . $this->iteratorPage;
    }

    //Comprobamos si la página actual es la actual

    public function getUrl()
    {
        return $this->getPagedUrl();
    }
}
