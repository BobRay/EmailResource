<?php
/**
 * EmailResource transport chunks
 * Copyright 2011 Bob Ray <http:bobsguides.com>
 * @author Bob Ray <http:bobsguides.com>
 * 8/20/11
 *
 * EmailResource is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * EmailResource is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * EmailResource; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package emailresource
 */
/**
 * Description: Array of chunk objects for EmailResource package
 * @package emailresource
 * @subpackage build
 */

$chunks = array();

$chunks[1]= $modx->newObject('modChunk');
$chunks[1]->fromArray(array(
    'id' => 1,
    'name' => 'MyChunk1',
    'description' => 'MyChunk1 for EmailResource',
    'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/mychunk1.chunk.tpl'),
    'properties' => '',
),'',true,true);

$chunks[2]= $modx->newObject('modChunk');
$chunks[2]->fromArray(array(
    'id' => 2,
    'name' => 'MyChunk2',
    'description' => 'MyChunk2 for entire EmailResource',
    'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/mychunk2.chunk.tpl'),
    'properties' => '',
),'',true,true);

return $chunks;