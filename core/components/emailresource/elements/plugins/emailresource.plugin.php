<?php
/**
 * EmailResource plugin
 *
 * Copyright 2011-2019 Bob Ray <https://bobsguides.com>
 *
 * @author Bob Ray <https://bobsguides.com>
 * Created 8/20/11
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
 * MODX EmailResource plugin
 *
 * Description: Creates and Sends an email version of a resource
 * Events: OnWebPagePrerender
 *
 * @package emailresource
 *
 * This extra would not exist without the generous support provided by WorkDay Media (http://www.workdaymedia.com.au/)
 */


/* ToDo: Internationalize error messages */
/* @var $modx modX */


$sp =& $scriptProperties;
$base_url = $modx->getOption('site_url');
$header = '';

/* Act only when previewing from the back end */
if (!$modx->user->hasSessionContext('mgr')) {
    return '';
}

require_once $modx->getOption('er.core_path', null, $modx->getOption('core_path') . 'components/emailresource/') . 'model/emailresource/emailresource.class.php';


/* Abort if in a resource that won't be emailed */
$templates = $modx->getOption('template_list', $sp, null);
if (!empty($templates)) {
    $templates = explode(',', $templates);
    if (!in_array($modx->resource->get('template'), $templates)) {
        return '';
    }

}
unset($templates);

$er = new EmailResource($modx, $sp);

/* make sure plugin can't crash if E_NOTICE is on */
$oldErrorLevel = error_reporting(E_ALL & ~(E_STRICT | E_NOTICE));

/* Get TV values */
$preview = $modx->resource->getTVValue('PreviewEmail') == 'Yes';
$emailit = $modx->resource->getTVValue('EmailOnPreview') == 'Yes';
$inlineCss = $modx->resource->getTVValue('InlineCss') == 'Yes';
$sendTestEmail = $modx->resource->getTVValue('SendTestEmail') == 'Yes';
$testEmailAddress = $modx->resource->getTVValue('EmailAddressForTest');

/* restore error level */
error_reporting($oldErrorLevel);


if ($emailit || $preview || $sendTestEmail) {
    $html =& $modx->resource->_output;
    $er->init();
    $er->setHtml($html);

    /* convert all images and links to full urls */
    $er->fullUrls($base_url);
    /* Fix image attributes */
    $er->imgAttributes();
    if ($inlineCss) {
        $er->inlineCss();
    }

    // $output = $er->getHtml();

} else {
    /* just return without modifying output */
    return;
}

/* turn the TVs off to prevent accidental resending */
/* @var $tv modTemplateVar */
$tv = $modx->getObject('modTemplateVar', array('name' => 'SendTestEmail'));
$tv->setValue($modx->resource->get('id'), 'No');
$tv->save();
$tv = $modx->getObject('modTemplateVar', array('name' => 'EmailOnPreview'));
$tv->setValue($modx->resource->get('id'), 'No');
$tv->save();
/* Need to change the TV values in memory too */

$fields = array(
    'SendTestEmail',
    'No',
    'default',
    '',
    'option',
);
$modx->resource->set('SendTestEmail', $fields);
$fields[0] = 'EmailOnPreview';
$modx->resource->set('EmailOnPreview', $fields);

/* Work starts here */

$username = $modx->user->get('username');
$profile = $modx->user->getOne('Profile');
$profileId = $profile->get('id');
unset($profile);
if ($emailit || $sendTestEmail) {
    $preview = true;
    //$er->setMailHeaders();
    $er->initializeMailer();

    if ($emailit) {
        /* send bulk email */
        $er->sendBulkEmail();
    }

    if ($sendTestEmail) {
        /* send test email */

        // my_debug('Profile ID: ' . $pid);
        // my_debug('Email: ' . $testEmailAddress);
        // my_debug('Username: ' . $username);
        // my_debug('Full Name: ' . $profile->get('fullname'));
        $er->sendTestEmail($testEmailAddress, $username, $profileId);
    }
}


$errors = $er->getErrors();
if (!empty($errors)) {
    $header = $er->showErrorStrings();
} else {
    if ($sendTestEmail) {
        $header = '<h3>Test email sent successfully</h3>';
    }
    if ($emailit) {
        $header .= '<h3>Bulk Email sent successfully</h3>';
    }
    if ($preview && !($sendTestEmail || $emailit)) {
        $header .= '<h3>Preview of Email:</h3>';
    }
}

if ($preview) {
   $output = $er->injectUnsubscribe($profileId);
} else {
    $output = $er->getHtml();
}
/* Make unprocessed tags visible */
$output = str_replace('[[', '[ [', $output);

/* Add header message */
$output = str_replace('<body>', '<body>' . $header . "\n\n", $output);

/* Set resource output */
$modx->resource->_output = $output;


