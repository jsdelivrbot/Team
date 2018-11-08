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

require_once(__DIR__ . '/PageIterator.php');

class Pagination extends \Team\Db\Find
{

    /** Params for url */
    public $url = null;
    /** Url to check */
    public $urlToCheck = null;
    protected $range = 4;
    protected $withPagination = true;
    protected $elementsForPage = 10;
    protected $currentPage = 1;
    protected $start = 1;
    protected $prev = null;
    protected $next = null;
    protected $end = 1;
    protected $pages = 1;
    protected $count = -1;
    protected $pagination = null;
    protected $collection = [];
    protected $GUI = null;
    protected $baseUrl = '/';

    function __construct($_elements_for_page = 10, $current_page = 1, $data = [])
    {
        $this->data = [];

        $this->onImport($data);

        if (\team\client\Http::checkUserAgent('mobile')) {
            $this->range = 2;
        }

        $this->url = new \Team\Data\Data($data);
        $this->GUI = \Team\System\Context::get('CONTROLLER');

        $this->setElementsForPage($_elements_for_page);
        $this->setCurrentPage($current_page);

        $base_url = \Team\System\Context::get('SELF');
        if (!empty($base_url)) {
            $this->setBaseUrl($base_url . ':page');
        }

        $current_url = \Team\System\Context::get('URL');
        $this->setUrlToCheck($current_url['location']);

        $this->onInitialize($data);
    }

    /** -------------------- SETTERS / GETTERS Elements ------------------ */

    public function setElementsForPage($_elements_for_page = 'all')
    {
        if ('all' !== $_elements_for_page) {
            $this->elementsForPage = \Team\Data\Check::id($_elements_for_page, $this->elementsForPage);
        } else {
            $this->elementsForPage = 'all';
        }

        return $this;
    }

    public function setBaseUrl($_url)
    {
        $this->baseUrl = $_url;
        return $this;
    }

    public function setUrlToCheck($url)
    {
        $this->urlToCheck = $url;
    }

    public function onInitialize()
    {
    }

    public function setGUI(\team\Controller $GUI = null)
    {
        $this->GUI = $GUI;
    }

    public function parseUrl($url, &$filtros = [])
    {
        if (\team\client\Url::match($this->urlToCheck, $url, $filtros)) {
            $this->import($filtros);
            return true;
        } else {
            return false;
        }
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function getPagination()
    {
        if (!isset($this->pagination)) {
            $this->createPagination();
        }

        return $this->pagination;
    }

    protected function createPagination()
    {
        if (isset($this->pagination)) {
            return $this->pagination;
        }

        if ($this->pages > 1 && $this->count >= 1) {
            $this->withPagination = true;
            $this->prev = $this->getPagePrev();
            $this->next = $this->getPageNext();
            $this->buildLimits();
            $this->start = $this->getStart();
            $this->end = $this->getEnd();
        } else {
            $this->withPagination = false;
        }

        $collection = [
            "withPagination" => $this->withPagination,
            "elements" => $this->elements,
            "offset" => $this->offset,
            'limit' => $this->limit,
            'numElements' => $this->count,
            'pages' => $this->pages,
            'page' => $this->currentPage,
            'next' => $this->next,
            'prev' => $this->prev,
            'start' => $this->start,
            'end' => $this->end,
            'baseUrl' => $this->baseUrl,
            'classes' => [],
            'url' => ''
        ];

        return $this->pagination = $this->onBuild($collection + $this->url->get(), $collection);
    }

    public function getPagePrev()
    {
        if ($this->currentPage > 1) {
            return $this->currentPage - 1;
        } else {
            return 1;
        }
    }

    public function getPageNext()
    {
        if ($this->currentPage < $this->pages) {
            return $this->currentPage + 1;
        } else {
            return $this->pages;
        }
    }

    public function buildLimits()
    {
        $range = $this->range;
        if ($this->currentPage > 10) {
            $range--;
            if ($this->currentPage > 100) {
                $range--;
            }
        }

        $this->start = \Team\Data\Check::id($this->currentPage - $range, 1);
        $max_range = ($range * 2) + 1;

        if ($this->currentPage <= ($this->range + 1)) {
            $end = $max_range;
        } else {
            $end = $this->currentPage + $range;
        }

        if ($end > $this->pages) {
            $end = min($this->pages, $end);
            $this->start = max($end - $max_range, 1);
        }

        $this->end = $end;
    }

    public function getStart()
    {
        return $this->start = \Team\Data\Check::id($this->start, 1);
    }

    public function getEnd()
    {
        return $this->end = \Team\Data\Check::id($this->end, $this->pages);
    }

    /** when pagination is been created*/
    public function onBuild($data, $collection)
    {
        $this->collection = $collection;
        return new \Team\Gui\PageIterator($data);
    }

    public function debug($title = 'Collection')
    {
        \team\Debug::me($this->queryLog, $title . ' Query');
    }

    /**
     *  Para casos que sólo se quiera un elemento por página o sólo se quiera devolver el primer elemento */
    public function getElement()
    {
        $elements = $this->search();

        if (1 === $this->elementsForPage && !empty($elements)) {
            return $elements->first();
        }

        return $elements;
    }

    //Pagina desde la que empezaremos a mostrar la paginación Ej: 5 6 7 8 |9| 10 11 12 13 . este caso 5

    public function search()
    {
        if (!$this->pagination) {
            $this->elements = $this->buildElements();

            $this->createPagination();
        }

        return $this->elements;
    }

    /** -------------------- BUILDING Elements ------------------ */
    protected function buildElements()
    {
        $this->commons();

        /** Obtenemos el número de elementos que queremos paginar */

        $this->count = $this->buildCount();

        //No hay elementos
        if ($this->count == 0) {
            $this->pages = 0;
            return $this->components = null;
        }

        /** calculamos el número de páginas totales que hay de elementos */
        if ($this->elementsForPage === 'all') {
            //Si se ha decidido que no haya paginación( es decir, todos los elemenos en una misma página )
            $this->pages = 1;
            $this->currentPage = 1;
        } else {
            if (!$this->elementsForPage) {
                $this->pages = 1;
            } else {
                $this->pages = \Team\Data\Check::id(ceil($this->count / $this->elementsForPage), 1);
            }

            /** Validamos que la pagina actual sea mayor o igual que 1 y menor o igual que el número máximo de paginas */
            if (!$this->currentPage) {
                $this->currentPage = 1;
            }
            if ($this->currentPage >= $this->pages) {
                $this->currentPage = $this->pages;
            }

            if (!isset($this->offset)) {
                $this->offset = ($this->currentPage - 1) * $this->elementsForPage;
            }

            if (!isset($this->limit)) {
                //Si estamos en la última página
                if ($this->currentPage == $this->pages) {
                    $this->limit = $this->count - ($this->currentPage - 1) * $this->elementsForPage;
                    //El techo es el pico de elementos que queden por mostrar

                } else {
                    //El techo de los elementos es el número de elementos por pagina
                    $this->limit = $this->elementsForPage;
                }
            }
        }

        $this->elements = $this->findElements();

        $this->custom();

        return $this->elements;
    }

    //Pagina hasta la que mostraremos la paginación Ej: 5 6 7 8 |9| 10 11 12 13 .  en este caso 13

    /** Before build query elements of pagination */
    public function commons()
    {
    }

    /** Obtenemos el número de elementos */
    function buildCount()
    {
        if ($this->count != -1) {
            $this->count;
        }

        $database = $this->getDatabase();

        $query = [
            'select' => 'count(*) as total',
            'from' => $this->buildFrom(),
            'where' => $this->buildWhere()
        ];

        //si hay group_by, no podemos contar los elementos con count tal y como está.
        $group_by = $this->buildGroupBy();
        if (!empty($group_by)) {
            $query['select'] = "count(distinct $group_by) as total";

            $having = $this->buildHaving();
            if (!empty($having)) {
                if (empty($query['where'])) {
                    $query['where'] = $having;
                } else {
                    $query['where'] = "( {$query['where']} ) AND ( $having ) ";
                }
            }
        }

        $this->queryLog = $query;

        $result = $database->get($query, $this->data);

        if (count($result)) {
            $this->count = $result[0]['total'];
        } else {
            $this->count = 0;
        }

        return $this->count;
    }

    /** After query elements but before getElements */
    public function custom()
    {
    }

    public function getElements()
    {
        return $this->search();
    }

    /**
     * Total de elementos
     */
    public function getcount()
    {
        return $this->count;
    }

    public function setCount($_num = 0)
    {
        $this->count = \Team\Data\Check::id($_num, 0);
        return $this;
    }

    /* ------------------ Events ___________________ */

    //After __construct

    /** -------------------- SETTERS / GETTERS PAGES ------------------ */

    public function putPage($_currentPage)
    {
        $this->setCurrentPage($_currentPage);
        return $this;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function setCurrentPage($_currentPage)
    {
        $this->currentPage = \Team\Data\Check::id($_currentPage, 1);
        $this->url["page"] = $this->currentPage;

        return $this;
    }

    public function getPagedUrl($vars = [])
    {
        return \Team\Client\Url::to($this->baseUrl, $vars + $this->url->get());
    }

}
