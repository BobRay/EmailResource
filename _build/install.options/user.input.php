<?php

/**
 * Script to interact with user during EmailResource package install
 *
 * Copyright 2011-2023 Bob Ray <https://bobsguides.com>
 * @author Bob Ray <https://bobsguides.com>
 * 8/20/11
 *
 *  is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 *  is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * ; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package emailresource
 */
/**
 * Description: Script to interact with user during EmailResource package install
 * @package emailresource
 * @subpackage build
 */
/* Use these if you would like to do different things depending on what's happening */

/* The return value from this script should be an HTML form (minus the
 * <form> tags and submit button) in a single string.
 *
 * The form will be shown to the user during install
 * after the readme.txt display.
 *
 * This example presents an HTML form to the user with one input field
 * (you can have more).
 *
 * The user's entries in the form's input field(s) will be available
 * in any php resolvers with $modx->getOption('field_name', $options, 'default_value').
 *
 * You can use the value(s) to set system settings, snippet properties,
 * chunk content, etc. One common use is to use a checkbox and ask the
 * user if they would like to install an example resource for your
 * component.
 */

$output = '<p>&nbsp;</p>
<label for="sitename">The value here will be used to set the site_name system setting on install.</label>
<p>&nbsp;</p>
<input type="text" name="sitename" id="sitename" value="" align="left" size="40" maxlength="60" />
<p>&nbsp;</p>
<input type="checkbox" name="change_sitename" id="change_sitename" checked="checked" value="1" align="left" />&nbsp;&nbsp;
<label for="change_sitename">Set site name on install</label>
<p>&nbsp;</p>';


return $output;