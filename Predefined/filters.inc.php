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

namespace Team\Predefined;

use Team\Config;
use Team\Data\Filter;

if (!defined('_TEAM_')) {
    die("Hello, World!");
}

//Avoid proxys domains
Filter::add('\team\request_uri', function ($url) {
    $url = parse_url($url);

    $path = $url['path'] ?? '/';

    $query = '';
    if (isset($url['query'])) {
        $query = '?' . $url['query'];
    }

    return $path . $query;
});

Config::setUp();

//Data Formats
Filter::add('\team\data\format\url', function ($data, $options = []) {
    return \Team\Data\Formatter::change($data, 'url', $options);
});
Filter::add('\team\data\format\terminal', function ($data, $options = []) {
    return \Team\Data\Formatter::change($data, 'terminal', $options);
});
Filter::add('\team\data\format\string', function ($data, $options = []) {
    return \Team\Data\Formatter::change($data, 'string', $options);
});
Filter::add('\team\data\format\params', function ($data, $options = []) {
    return \Team\Data\Formatter::change($data, 'params', $options);
});
Filter::add('\team\data\format\object', function ($data, $options = []) {
    return \Team\Data\Formatter::change($data, 'object', $options);
});
Filter::add('\team\data\format\xml', function ($data, $options = []) {
    return \Team\Data\Formatter::change($data, 'xml', $options);
});
Filter::add('\team\data\format\json', function ($data, $options = []) {
    return \Team\Data\Formatter::change($data, 'json', $options);
});
Filter::add('\team\data\format\html', function ($data, $options = []) {
    return \Team\Data\Formatter::change($data, 'html', $options);
});




