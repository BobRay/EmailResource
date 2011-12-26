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

function fullUrls($html, $base)
{
    /* remove any spaces around = sign */
    $html = preg_replace('@(href|src)\s*=\s*@', '\1=', $html);

    /* Correct base URL, if necessary */
    $server = preg_replace('@^([^\:]*)://([^/*]*)(/|$).*@', '\1://\2/', $base);

    /* handle root-relative URLs */
    $html = preg_replace('@\<([^>]*) (href|src)="/([^"]*)"@i', '<\1 \2="' . $server . '\3"', $html);

    /* handle base-relative URLs */
    $html = preg_replace('@\<([^>]*) (href|src)="(([^\:"])*|([^"]*:[^/"].*))"@i', '<\1 \2="' . $base . '\3"', $html);

    return $html;
}

function imgAttributes($html) {
    $replace = array (
        '<img style="vertical-align: baseline;' =>'<img align="bottom" hspace="4" vspace="4" style="vertical-align: baseline;',
        '<img style="vertical-align: middle;' => '<img align="middle" hspace="4" vspace="4" style="vertical-align: middle;',
        '<img style="vertical-align: top;' => '<img align="top" hspace="4" vspace="4" style="vertical-align: top;',
        '<img style="vertical-align: bottom;' => '<img align="bottom" hspace="4" vspace="4" style="vertical-align: bottom;',
        '<img style="vertical-align: text-top;' =>'<img align="top" hspace="4" vspace="4" style="vertical-align: text-top;',
        '<img style="vertical-align: text-bottom;' => '<img align="bottom" hspace="4" vspace="4" style="vertical-align: text-bottom;',
        '<img style="float: left;' => '<img align="left" hspace="4" vspace="4" style="float: left;',
        '<img style="float: right;' => '<img align="right" hspace="4" vspace="4" style="float: right;',
    );
    return strReplaceAssoc($replace, $html);

}

function strReplaceAssoc(array $replace, $subject) {
       return str_replace(array_keys($replace), array_values($replace), $subject);
}


function my_debug($message, $clear = false)
{
    global $modx;

    $chunk = $modx->getObject('modChunk', array('name' => 'debug'));
    if (!$chunk) {
        $chunk = $modx->newObject('modChunk', array('name' => 'debug'));
        $chunk->save();
        $chunk = $modx->getObject('modChunk', array('name' => 'debug'));
    }
    if ($clear) {
        $content = '';
    } else {
        $content = $chunk->getContent();
    }
    $content .= $message;
    $chunk->setContent($content);
    $chunk->save();
}

$sp =& $scriptProperties;
$base_url = $modx->getOption('site_url');

/* Act only when previewing from the back end */
if (!$modx->user->hasSessionContext('mgr')) {
    return '';
}

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


$preview = $modx->resource->getTVValue('PreviewEmail') == 'Yes';
$emailit = $modx->resource->getTVValue('EmailOnPreview') == 'Yes';
$inlineCss = $modx->resource->getTVValue('InlineCss') == 'Yes';

$cssBasePath = $modx->resource->getTVValue('CssBasePath');

if (empty ($cssBasePath)) {
    $cssBasePath = MODX_BASE_PATH . 'assets/components/emailresource/css/';
} else {
    if (strstr($cssBasePath, '{modx_base_path}')) {
        $cssBasePath = str_replace('{modx_base_path}', MODX_BASE_PATH, $cssBasePath);
    }
}

$sendTestEmail = $modx->resource->getTVValue('SendTestEmail') == 'Yes';

$testEmailAddress = $modx->resource->getTVValue('EmailAddressForTest');

$cssTv = $modx->resource->getTVValue('cssFile');
$cssFiles = explode(',', $cssTv);

$cssMode = $modx->resource->getTVValue('CssMode');

if (empty ($cssMode)) {
    $cssMode = 'FILE';
} else {
    $cssMode = strtoupper($cssMode);
}

if ($emailit || $preview || $sendTestEmail) {
    $html = $modx->resource->_output;

    /* convert all images and links to full urls */
    $html = fullUrls($html, $base_url);
    /* Fix image attributes */
    $html = imgAttributes($html);

    if ($inlineCss) {
        $root = MODX_BASE_PATH;
        $assets_path = $root . 'assets/components/emailresource/';
        $core_path = $root . 'core/components/emailresource/';
        require $core_path . 'model/emailresource/css_to_inline_styles.class.php';

        $css = '';
        foreach ($cssFiles as $cssFile) {
            switch ($cssMode) {
                case 'FILE':
                    $tempCss = file_get_contents($cssBasePath . $cssFile);
                    if (empty($tempCss)) {
                        die('Could not get CSS file: ' . $cssBasePath . $cssFile);
                    }
                    break;
                case 'RESOURCE':
                    $res = $modx->getObject('modResource', array('pagetitle' => $cssFile));
                    $tempCss = $res->getContent();
                    unset($res);
                    if (empty($tempCss)) {
                        die('Could not get resource content: ' . $cssFile);
                    }
                    break;
                case 'CHUNK':
                    $tempCss = $modx->getChunk($cssFile);
                    if (empty($tempCss)) {
                        die('Could not get chunk content: ' . $cssFile);
                    }

            }
            $css .= $tempCss . "\n";
        }

        $ctis = new CSSToInlineStyles($html, $css);
        $output = $ctis->convert();
    } else {
        $output = $html;
    }
} else {
    return;
}

if ($emailit || $sendTestEmail) {
    $preview = true;

    $mail_from = $modx->getOption('mail_from', $sp);
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


    if ($emailit) {


        /* *********************************** */
        /* bulk email $output goes here */
        /* *********************************** */


        /* turn the TVs off to prevent accidental resending */
        $tv = $modx->getObject('modTemplateVar', (integer)$modx->getOption('emailOnPreviewTvId', $sp));
        $tv->setValue($modx->resource->get('id'), 'No');
        $tv->save();
        $tv = $modx->getObject('modTemplateVar', (integer)$modx->getOption('emailOnPreviewTvId', $sp));
                $tv->setValue($modx->resource->get('id'), 'No');
                $tv->save();

    }

    if (empty($testEmailAddress) && $sendTestEmail) {
        return '<p>Test email address is empty';
    }

    if ($sendTestEmail) {
        $modx->mail->mailer->SMTPDebug = 2;
        $modx->getService('mail', 'mail.modPHPMailer');
        $modx->mail->set(modMail::MAIL_BODY, $output);
        $modx->mail->set(modMail::MAIL_FROM, $mail_from);
        $modx->mail->set(modMail::MAIL_FROM_NAME, $mail_from_name);
        $modx->mail->set(modMail::MAIL_SENDER, $mail_sender);
        $modx->mail->set(modMail::MAIL_SUBJECT, $mail_subject);
        $modx->mail->address('to', $testEmailAddress, $testEmailAddress);
        $modx->mail->address('reply-to', $mail_reply_to);
        $modx->mail->setHTML(true);

        $sent = $modx->mail->send();

        if ($sent) {
            $output = '<h3>The following test Email has been sent</h3>' . $output;
        } else {

            $output = '<h3>Error sending test email</h3>' . $output;
            $output .= '<br />' . $modx->mail->mailer->ErrorInfo;
        }
    }
}
if ($preview) {
    if ($emailit == false) {
        $output = '<h3>Preview of Email version of this resource</h3>' . $output;
    }
    $output = str_replace('[[', '[ [', $output);
    $modx->resource->_output = $output;
}

