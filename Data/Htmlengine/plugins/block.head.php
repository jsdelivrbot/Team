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

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     block.head
 * Type:     block
 * Name:     head
 * Purpose:  output a dtd tag + head tag
 * -------------------------------------------------------------
 */

function smarty_block_head($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    $place = 'head';
    if (isset($params['place'])) {
        $place = $params['place'];
        unset($params['place']);
    }
    if ($repeat) { //open tag

        $responsive = '';
        if (isset($params['responsive'])) {
            $responsive = "<meta name='viewport' content='width=device-width, initial-scale=1.0' />";
            unset($params['responsive']);
        }

        /* ******************** HTML TAG *************** */
        $out = '<!DOCTYPE html>';
        $out .= '<html';
        foreach ($params as $attr => $value) {
            $out .= " {$attr}='{$value}'";
        }

        $out .= '><head>' . $responsive . trim($content);

        $charset = \Team\System\Context::get('CHARSET');
        $out .= "<meta charset='{$charset}'>";

        return $out;
    } else {//close tag

        $out = $content;

        /* ******************** METAS *************** */
        //head tag
        $metas = (array)\Team\System\Context::get('SEO_METAS');
        $metas = \Team\Data\Filter::apply('\team\tag\metas', $metas);

        foreach ($metas as $name => $content) {
            if (stripos($name, 'og:') === 0) {
                $out .= "<meta property='{$name}' ";
            } else {
                $out .= "<meta name='{$name}' ";
            }

            if (is_array($content)) {
                $options = $content;
                foreach ($options as $key => $value) {
                    $out .= $key . "='{$value}'";
                }
                $out .= '>';
            } else {
                $out .= "content='{$content}'>";
            }
        }

        $out = trim(\team\data\Filter::apply('\team\tag\head', $out));

        /* ******************** TOP CSS Y JS FILES *************** */
        //TOP CSS
        $css_files = \Team\Config::get('\team\css\top', []);

        if (!empty($css_files)) {
            foreach ($css_files as $id => $file) {
                $out .= "<link href='{$file}' rel='stylesheet'/>";
            }
        }

        //TOP JS
        $js_files = \Team\Config::get('\team\js\top', []);

        if (!empty($js_files)) {
            foreach ($js_files as $id => $file) {
                $out .= "<script src='{$file}'></script>";
            }
        }

        /* ******************** /TOP CSS Y JS FILES *************** */
        $out = \Team\Gui\Place::getHtml($place, $out, $params, $template);

        $out .= '</head>';

        return $out;
    }
}
