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
 * File:     smarty_block_multiwrapper
 * Type:     block
 * Name:     multiwrapper
 * Purpose:  avoid code like this:
  <div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <table>
             --content---
            </table>
        </div>
    </div>
  </div<

    and replace with this:

    {multiwrapper div1='row' div2='col-sm-12' div3='ibox' table=''}
        --content---
    {/multiwrapper}
 * -------------------------------------------------------------
 */

function smarty_block_multiwrapper($params, $content, Smarty_Internal_Template $engine, &$repeat)
{
    if (!$repeat) {
        $out = '';
        $tags = [];
        foreach ($params as $tag => $classes) {
            $tag = \Team\Data\Sanitize::text($tag);
            $tags[] = $tag;

            if (!empty($classes)) {
                $out .= "<{$tag} class='{$classes}'>";
            } else {
                $out .= "<{$tag}>";
            }
        }

        $out .= $content;

        rsort($tags);
        foreach ($tags as $tag) {
            $out .= "</{$tag}>";
        }

        return $out;
    }

    return $content;
}
