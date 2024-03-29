<?php

/**
 * EmailResource resolver script - runs on install.
 *
 * Copyright 2011-2023 Bob Ray <https://bobsguides.com>
 * @author Bob Ray <https://bobsguides.com>
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
 * Description: Resolver script for EmailResource package
 * @package emailresource
 * @subpackage build
 */

/* EmailResource PHP resolver */

/* The $modx object is not available here. In its place we
 * use $object->xpdo
 */

/** @var $modx modX
   @var $object modX */

/** @var modTransportPackage $transport */

if ($transport->xpdo) {
    $modx =& $transport->xpdo;
} else {
    $modx =& $object->xpdo;
}

/* Connecting plugins to the appropriate system events and
 * connecting TVs to their templates is done here.
 */

$pluginEvents = array('OnWebPagePrerender');
$plugins = array();
$plugins[] = 'EmailResource';
$category = 'EmailResource';

$hasPlugins = true;
$hasTemplates = false;
$hasTemplateVariables = true;

$hasExistingSettings = false;

/* set to true to connect property sets to elements */
$connectPropertySets = false;

$isMODX3 = $modx->getVersionData()['version'] >= 3;
$classPrefix = $isMODX3
    ? 'MODX\Revolution\\'
    : '';

$success = true;

$modx->log(xPDO::LOG_LEVEL_INFO, 'Running PHP Resolver.');

/* @var $options array */
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    /* This code will execute during a fresh install */
    case xPDOTransport::ACTION_INSTALL:
        /* Assign plugins to System events */
        if ($hasPlugins) {
            foreach ($plugins as $k => $plugin) {
                $pluginObj = $modx->getObject($classPrefix .
                    'modPlugin', array('name' => $plugin));
                if (!$pluginObj) {
                    $modx->log(xPDO::LOG_LEVEL_INFO, 'cannot get object: ' . $plugin);
                }
                if (empty($pluginEvents)) {
                    $modx->log(xPDO::LOG_LEVEL_INFO, 'Cannot get System Events');
                }
                if (!empty ($pluginEvents) && $pluginObj) {

                    $modx->log(xPDO::LOG_LEVEL_INFO, 'Assigning Events to Plugin ' . $plugin);
                    foreach ($pluginEvents as $key => $pluginEvent) {
                        /* @var $pe modPluginEvent */
                        /* @var $pluginObj modPlugin */
                        $pe = $modx->newObject($classPrefix .
                            'modPluginEvent');
                        $pe->set('pluginid', $pluginObj->get('id'));
                        $pe->set('event', $pluginEvent);
                        $pe->save();
                    }

                    //$pluginObj->addMany($pluginEvents);
                    //$pluginObj->save();
                    $modx->log(xPDO::LOG_LEVEL_INFO, 'Successfully assigned Events to Plugin ' . $plugin);
                }
            }
        }

        /* Connect TVs to the default template */
        /* @var $categoryObj modCategory */
        if ($hasTemplateVariables) {
            $categoryObj = $modx->getObject($classPrefix .
                'modCategory', array('category' => $category));
            if (!$categoryObj) {
                $modx->log(xPDO::LOG_LEVEL_INFO, 'Could not retrieve category object: ' . $category);
                $categoryId = 0;
            } else {
                $categoryId = $categoryObj->get('id');
            }

            $modx->log(xPDO::LOG_LEVEL_INFO, 'Attempting to attach TVs to Templates');
            $ok = true;
            $defaultTemplateId = $modx->getOption('default_template', null);
            $template = $modx->getObject($classPrefix .
                'modTemplate', $defaultTemplateId);

            if (!empty($template)) {
                $tvs = $modx->getCollection($classPrefix .
                    'modTemplateVar', array('category' => $categoryId));
                //$template->addMany($tvs);
                //$template->save();
                /* @var $tvt modTemplateVarTemplate */
                /* @var $tv modTemplateVar */

                foreach($tvs as $tv) {
                    $tvt = $modx->newObject($classPrefix .
                        'modTemplateVarTemplate');
                    $tvt->set('tmplvarid',$tv->get('id'));
                    $tvt->set('templateid',$defaultTemplateId);
                    $tvt->save();
                }

                if (!empty($tvs)) {
                    $langFile = MODX_CORE_PATH . 'components/emailresource/lexicon/' . $modx->getOption('manager_language') . '/tvs.inc.php';
                    if (file_exists($langFile)) {
                        include MODX_CORE_PATH . 'components/emailresource/lexicon/' . $modx->getOption('manager_language') . '/tvs.inc.php';
                    } else {
                        include MODX_CORE_PATH . 'components/emailresource/lexicon/en/tvs.inc.php';
                    }
                    unset($langFile);
                    // foreach ($templates as $template) {
                    foreach ($tvs as $tv) {
                        /* @var $_lang array */
                        /* set TV description from language file */
                        $descKey = $tv->get('description');
                        $tv->set('description', $_lang[$descKey]);
                        $tv->save();

                        /* set default value of EmailAddressForTest TV */
                        if ($tv->get('name') == 'EmailAddressForTest') {
                            $tv->set('default_text', '@INHERIT ' . $modx->getOption('emailsender', null));
                            $tv->save();
                        }
                    }

                } else {
                    $ok = false;
                    $modx->log(xPDO::LOG_LEVEL_INFO, 'Could not retrieve TVs in category: ' . $category);
                }

            } else {
                $ok = false;
                $modx->log(xPDO::LOG_LEVEL_INFO, 'Could not retrieve default template');
            }

            if ($ok) {
                $modx->log(xPDO::LOG_LEVEL_INFO, 'TVs attached to Default Template successfully');
            } else {
                $modx->log(xPDO::LOG_LEVEL_INFO, 'Failed to attach TVs to Default Template');
            }
        }
        break;

    /* This code will execute during an upgrade */
    case xPDOTransport::ACTION_UPGRADE:

        /* put any upgrade tasks (if any) here such as removing
           obsolete files, settings, elements, resources, etc.
        */

        $success = true;
        break;

    /* This code will execute during an uninstall */
    case xPDOTransport::ACTION_UNINSTALL:
        $modx->log(xPDO::LOG_LEVEL_INFO, 'Uninstalling . . .');
        $success = true;
        break;

}
$modx->log(xPDO::LOG_LEVEL_INFO, 'Script resolver actions completed');
return $success;