<?php
/**
 * EmailResource plugin
 *
 * Copyright 2011 Bob Ray <http:bobsguides.com>
 *
 * @author Bob Ray <http:bobsguides.com>
 * @version Version 1.0.0 Beta-1
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
 * MODx EmailResource plugin
 *
 * Description: Creates and Sends an email version of a resource
 * Events: OnWebPagePrerender
 *
 * @package emailresource
 *
 * This extra would not exist without the generous support provided by WorkDay Media (http://www.workdaymedia.com.au/)
 */



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


/* only do this if you need lexicon strings */
// $modx->lexicon->load('emailresource:default');

$er = new EmailResource($modx, $sp);

/* Get TV values */
$preview = $modx->resource->getTVValue('PreviewEmail') == 'Yes';
$emailit = $modx->resource->getTVValue('EmailOnPreview') == 'Yes';
$inlineCss = $modx->resource->getTVValue('InlineCss') == 'Yes';
$sendTestEmail = $modx->resource->getTVValue('SendTestEmail') == 'Yes';

$groups = $modx->resource->getTVValue('Groups');
$batchSize = $modx->resource->getTVValue('BatchSize');
$batchSize = empty($batchSize)? 50 : $batchSize;
$batchDelay = $modx->resource->getTVValue('BatchDelay');
$batchDelay = empty($batchDelay)? 1 : $batchDelay;
$itemDelay = $modx->resource->getTVValue('itemDelay');
$itemDelay = empty($itemDelay)? .51 : $itemDelay;

$testEmailAddress = $modx->resource->getTVValue('EmailAddressForTest');

if ($emailit || $preview || $sendTestEmail) {
    $html =& $modx->resource->_output;
    $er->init();
    $er->setHtml($html);

    /* convert all images and links to full urls */
    $er->fullUrls($base_url);
    /* Fix image attributes */
    $er->imgAttributes();
    //$html = $er->getHtml();
    if ($inlineCss) {
        $er->inlineCss();
        $output = $er->getHtml();
    }

    $output = $er->getHtml();

} else {
    /* just return without modifying output */
    return;
}

if ($emailit || $sendTestEmail) {
    $preview = true;
    $er->setMailHeaders();

    if (false) { $mail_from = $modx->getOption('mail_from', $sp);
    $mail_from = empty($mail_from) ? $modx->getOption('emailsender', null) : $mail_from;

    $mail_from_name = $modx->getOption('mail_from_name', $sp);
    $mail_from_name = empty($mail_from_name) ? $modx->getOption('site_name', null) : $mail_from_name;

    $mail_sender = $modx->getOption('mail_sender', $sp);
    $mail_sender = empty($mail_sender) ? $modx->getOption('emailsender', null) : $mail_sender;

    $mail_reply_to = $modx->getOption('mail_reply_to', $sp);
    $mail_reply_to = empty($mail_reply_to) ? $modx->getOption('emailsender', null) : $mail_reply_to;

    $mail_subject = $modx->getOption('mail_subject', $sp);
    $mail_subject = empty($mail_subject) ? $modx->resource->get('longtitle') : $mail_subject;
    /* fall back to pagetitle if longtitle is empty */
        $mail_subject = empty($mail_subject) ? $modx->resource->get('pagetitle') : $mail_subject;
    }


    if ($emailit && false) {


        /* *********************************** */
        /* bulk email $output goes here */
        /* *********************************** */

        /* turn the TVs off to prevent accidental resending */
        $tv = $modx->getObject('modTemplateVar', array('name'=> 'SendTestEmail'));
        $tv->setValue($modx->resource->get('id'), 'No');
        $tv->save();
        $tv = $modx->getObject('modTemplateVar', array('name'=>'EmailOnPreview'));
        $tv->setValue($modx->resource->get('id'), 'No');
        $tv->save();

        if (empty ($groups)) {
            $modx->resource->_output = '<p>No User Groups selected</p>';
            return '';
        }
        $recipients = array();
        $userGroupNames = explode(',',$groups);
        /* Build Recipient array */
        foreach ($userGroupNames as $userGroupName) {
            $group = $modx->getObject('modUserGroup', array('name' => trim($userGroupName)));
            if (empty($group)) {
                $modx->resource->_output =  '<p>Could not find User Group: ' . $userGroupName . '</p>';
                return '';
            }

            $ugms = $group->getMany('UserGroupMembers');
            if (empty ($ugms)) {
                $modx->resource->_output = '<p>User Group: ' . $userGroupName . ' has no members</p>';
                return '';
            }

            foreach ($ugms as $ugm) {
                $memberId = $ugm->get('member');
                $user = $modx->getObject('modUser', $memberId);
                $username = $user->get('username');
                $profile = $user->getOne('Profile');
                $email = $profile->get('email');
                $fullName = $profile->get('fullname');
                $fullName = empty($fullName)? $username : $fullName;
                $recipients[] = array (
                    'group' => $userGroupName,
                    'email' => $fullName . ' <' . $email . '>');
            }
        }
        unset($users, $ugms);
        if (empty($recipients)) {
            $modx->resource->_output =  '<p>No Recipients to send to</p>';
            return '';
        }
        /* $recipients array now complete */
        $i = 1;
        foreach ($recipients as $recipient) {
            $output .=  '<br />(' . $i . ') ' . $recipient['group'] . ': ' . htmlentities($recipient['email']);

            if (($i%$batchSize) == 0) {
                $output .= '<br /> ------------------------------------------ <br/>';
            }
            $i++;
        }
        $modx->resource->_output = $output;
        return '';

    }

    if (empty($testEmailAddress) && $sendTestEmail) {
        return '<p>Test email address is empty</p>';
    }

    if ($sendTestEmail) {
        $success = $er->sendMail($testEmailAddress,$testEmailAddress);
        if ($success) {
            $header = '<h3>The following test Email has been sent</h3>';
        } else {
            $header = '<h3>Error Sending Email</h3>';
            /*ToDo: Set mail error message */
        }
        /*$modx->mail->mailer->SMTPDebug = 2;
        $modx->getService('mail', 'mail.modPHPMailer');
        $modx->mail->set(modMail::MAIL_BODY, $output);
        $modx->mail->set(modMail::MAIL_FROM, $mail_from);
        $modx->mail->set(modMail::MAIL_FROM_NAME, $mail_from_name);
        $modx->mail->set(modMail::MAIL_SENDER, $mail_sender);
        $modx->mail->set(modMail::MAIL_SUBJECT, $mail_subject);
        $modx->mail->address('to', $testEmailAddress, $modx->user->get('username'));
        $modx->mail->address('reply-to', $mail_reply_to);
        $modx->mail->setHTML(true);

        $sent = $modx->mail->send();

        if ($sent) {
            $output = '<h3>The following test Email has been sent</h3>' . $output;
        } else {

            $output = '<h3>Error sending test email</h3>' . '<br />' . $modx->mail->mailer->ErrorInfo . '<br />' . $output;

        }*/
    }
}
if ($preview) {
    if ($emailit == false) {
        $output = $header . $er->getHtml();
    }
    $output = str_replace('[[', '[ [', $output);
    $modx->resource->_output = $output;
}

