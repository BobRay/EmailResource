<?php
/**
 * Action file for the components menu item of the EmailResource package
 * @author Bob Ray
 * 2/4/11
 *
 * @package emailresource
 */

/* This is an example of an action index file. This file executes
 * When you select EmailResource on the Components menu.
 * This example displays a simple MODX cheat sheet in the
 * manager. In order to reach page 2 (or return to page 1), you'll
 * have to edit the action ID in the URL of the two chunks.
 */
$path = $modx->getOption('assets_url');
$modx->regClientCSS($path . 'components/emailresource/css/emailresource.css');
$output = '<div class="emailresource">';
$output .= '<h2>MODX Cheat Sheet</h2>';
/* get page to show from URL */
switch($_GET['page']) {
    case '2':
        $output .= $modx->getChunk( 'MyChunk2');
        break;
    default: /* default to page 1 */
        $output .= $modx->getChunk( 'MyChunk1');
}
$output .= '</div>';
return $output;
?>
