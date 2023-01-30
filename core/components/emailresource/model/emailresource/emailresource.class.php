<?php

/**
 * EmailResource
 *
 * Copyright 2011-2023 Bob Ray
 *
 * @author Bob Ray <https://bobsguides.com>
 * 
 * 
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
 * MODX EmailResource Class
 *
 *
 * @package  emailresource
 *
 * The EmailResource plugin for emailing resources to users
 *
 * The EmailResource class contains all functions relating to EmailResource's
 * operation.
 */

/**
 * MODX EmailResource class
 *
 * Description: Creates and Sends an email version of a resource
 *
 * @package emailresource
 *
 * This extra would not exist without the generous support provided by WorkDay Media (http://www.workdaymedia.com.au/)
 */



class EmailResource
{

    protected $html;
    /* @var $modx modX */
    protected $modx;
    protected $props;
    protected $cssMode;
    protected $cssFiles;
    protected $cssBasePath;
    protected $mail_from;
    protected $mail_from_name;
    protected $mail_sender;
    protected $mail_reply_to;
    protected $mail_subject;
    protected $groups;
    protected $batchSize;
    protected $batchDelay;
    protected $itemDelay;
    protected $errors;
    protected $logFile;
    protected $userClass;
    protected $profileAlias;
    protected $profileClass;
    protected $sortBy;
    protected $sortByAlias;
    protected $tags;
    protected $unSubUrl;
    /* @var $unSub Unsubscribe */
    protected $unSub;
    protected $unSubTpl;
    protected $userTagsMethod;
    protected $userTagsExtendedField = '';
    protected string $classPrefix = '';
    /** @var modPhpMailer $mail */
    protected $mail = null;


    /**
     * EmailResource class constructor
     *
     * @param $modx modX
     * @param $props array
     */
    public function __construct(&$modx, &$props) {

        $this->modx =& $modx;
        $this->props =& $props;

        /* Set the er paths from System Settings
           (Settings will only exist in dev. environment) */
        $this->corePath = $this->modx->getOption('er.core_path', null, MODX_CORE_PATH . 'components/emailresource/');
        $this->assetsPath = $this->modx->getOption('er.assets_path', null, MODX_ASSETS_PATH . 'components/emailresource/');
        $this->assetsUrl = $this->modx->getOption('er.assets_url', null, MODX_ASSETS_URL . 'components/emailresource/');

    }

    /**
     * Initialize properties and Tpls for class
     */
    public function init() {
        $isMODX3 = $this->modx->getVersionData()['version'] >= 3;

        $this->classPrefix = $isMODX3
            ? 'MODX\Revolution\\'
            : '';

        if ($isMODX3) {
            if (!$this->modx->services->has('mail')) {
                $this->modx->services->add('mail', new MODX\Revolution\Mail\modPhpMailer($this->modx));
            }
            $this->mail = $this->modx->services->get('mail');
        } else {
            $this->mail = $this->modx->getService('mail', 'mail.modPhpMailer');
        }


        $useCommentField = $this->modx->getOption('sbs_use_comment_field', null, true);
        if (! $useCommentField) {
            $this->userTagsMethod = 'extended';
            $this->userTagsExtendedField = $this->modx->getOption('sbs_extended_field', null, 'interests');
        } else {
            $this->userTagsMethod = 'comment';
        }
        $this->userTagsMethod =
        $this->sortBy = $this->modx->getOption('sortBy',$this->props,'username');
        $this->sortByAlias = $this->modx->getOption('sortByAlias',$this->props,$this->classPrefix . 'modUser');
        $this->userClass = $this->modx->getOption('userClass',$this->props,
            $this->classPrefix . 'modUser');
        $this->profileAlias = $this->modx->getOption('profileAlias',$this->props,'Profile');
        $this->profileClass = $this->modx->getOption('profileClass',$this->props,$this->classPrefix . 'modUserProfile');
        $this->logFile = $this->corePath . 'logs/' . $this->modx->resource->get('alias') . '--'. date('Y-m-d-h.i.sa');
        $this->errors = array();
        /* make sure plugin can't crash if E_NOTICE is on */
        $oldErrorLevel = error_reporting(E_ALL & ~(E_STRICT | E_NOTICE));
        $cssBasePath = $this->modx->resource->getTVValue('CssBasePath');
        $this->tags = $this->modx->resource->getTVValue('Tags');

        if (empty ($cssBasePath)) {
            $cssBasePath = MODX_BASE_PATH . 'assets/components/emailresource/css/';
        } else {
            if (strstr($cssBasePath, '{modx_base_path}')) {
                $cssBasePath = str_replace('{modx_base_path}', MODX_BASE_PATH, $cssBasePath);
            }
        }
        $this->cssBasePath = $cssBasePath;
        $cssTv = $this->modx->resource->getTVValue('CssFile');
        $cssTv = empty($cssTv)? 'emailresource.css': $cssTv;
        $this->cssFiles = explode(',', $cssTv);

        $cssMode = $this->modx->resource->getTVValue('CssMode');

        if (empty ($cssMode)) {
            $this->cssMode = 'FILE';
        } else {
            $this->cssMode = strtoupper($cssMode);
        }

        /* Bulk email settings */
        $this->groups = $this->modx->resource->getTVValue('Groups');
        $batchSize = $this->modx->resource->getTVValue('BatchSize');
        $this->batchSize = empty($batchSize)? 50 : $batchSize;
        $batchDelay = $this->modx->resource->getTVValue('BatchDelay');
        $this->batchDelay = empty($batchDelay)? 1 : $batchDelay;
        $itemDelay = $this->modx->resource->getTVValue('itemDelay');
        $this->itemDelay = empty($itemDelay)? .51 : $itemDelay;

        /* restore error reporting level */
        error_reporting($oldErrorLevel);
        /* Unsubscribe settings */
        $unSubId = $this->modx->getOption('sbs_unsubscribe_page_id', null, null);
        $this->unSubUrl = $this->modx->makeUrl($unSubId, "", "", "full");
        $subscribeCorePath = $this->modx->getOption('subscribe.core_path', null, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/subscribe/');
        require_once($subscribeCorePath . 'model/subscribe/unsubscribe.class.php');
        $unSubTpl = $this->modx->getOption('unsubscribe_tpl', $this->props, 'unsubscribeTpl');
        $this->unSub = new Unsubscribe($this->modx, $this->props);
        $this->unSub->init();
        $this->unSubTpl = $this->modx->getChunk($unSubTpl);
    }

    /**
     * Get user tags for the current user from comment field or User Profile extended field
     * @param $profile modUserProfile - profile of the logged-in user
     */
    public function getUserTags($profile) {

        if ($this->userTagsMethod == 'extended') {
            $extended = $profile->get('extended');
            $userTags = $extended[$this->userTagsExtendedField];
        } else {
            $userTags = $profile->get('comment');
        }
        return $userTags;

    }


    /**
     * Convert URL in any form to a fully qualified URL
     *
     * @param $base string - sites base URL
     * @return string - full URL
     */
    public function fullUrls($base) {
        $core_path = MODX_CORE_PATH . 'components/emailresource/';
        require $core_path . 'model/emailresource/fullurls.class.php';
        $this->html = FullUrls::fullUrls($base, $this->html);
        return;
    }

    /**
     * Fix images attributes that confuse some mail rendering engines
     */
    public function imgAttributes() {
        $html =& $this->html;
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
        $html = $this->strReplaceAssoc($replace, $html);

    }

    /**
     * Move CSS from files into inline style tags
     */
    public function inlineCss()
    {
        $root = MODX_BASE_PATH;
        //$assets_path = $root . 'assets/components/emailresource/';
        $core_path = MODX_CORE_PATH . 'components/emailresource/';
        require $core_path . 'model/emailresource/css_to_inline_styles.class.php';

        $css = '';
        if (empty($this->cssFiles)) {
            $this->setError('cssFiles is empty');
        }
        foreach ($this->cssFiles as $cssFile) {
            switch ($this->cssMode) {

                case 'RESOURCE':
                    /* @var $res modResource */
                    $res = $this->modx->getObject($this->classPrefix .
                        'modResource', array('pagetitle' => $cssFile));
                    $tempCss = $res->getContent();
                    unset($res);
                    if (empty($tempCss)) {
                        $this->setError('Could not get resource content: ' . $cssFile);
                    }
                    break;
                case 'CHUNK':
                    $tempCss = $this->modx->getChunk($cssFile);
                    if (empty($tempCss)) {
                        $this->setError('Could not get chunk content: ' . $cssFile);
                    }
                    break;
                default:
                case 'FILE':
                    $tempCss = file_get_contents($this->cssBasePath . $cssFile);
                    if (empty($tempCss)) {
                        $this->setError('Could not get CSS file: ' . $this->cssBasePath . $cssFile);
                    }
                    break;

            }
            $css .= $tempCss . "\n";
        }

        $ctis = new CSSToInlineStyles($this->html, $css);
        $this->html = $ctis->convert();
    }

    /**
     * Replaces strings based on an associative array
     *
     * @param array $replace - array of needle => replacement pairs
     * @param $subject - string to do replacements in
     * @return string - original string with values replaced
     */
    protected function strReplaceAssoc(array $replace, $subject) {
           return str_replace(array_keys($replace), array_values($replace), $subject);
    }


    /**
     * Debugging code -- writes output to chunk named 'debug'
     * @param $message - message to write
     * @param bool $clear - if set, clears previous entries
     */
    public function my_debug($message, $clear = false)
    {
        /* @var $chunk modChunk */
        $chunk = $this->modx->getObject($this->classPrefix .
            'modChunk', array('name' => 'debug'));

        if (!$chunk) {
            $chunk = $this->modx->newObject($this->classPrefix .
                'modChunk', array('name' => 'debug'));
            $chunk->save();
            $chunk = $this->modx->getObject($this->classPrefix .
                'modChunk', array('name' => 'debug'));
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

    /**
     * Sets the HTML content for the email
     *
     * @param $html string
     */
    public function setHtml($html) {
        $this->html = $html;
    }

    /**
     * Gets the email content of the email
     *
     * @return string
     */
    public function getHtml() {
        return $this->html;
    }

    /**
     * Sets the mail headers for the email
     *
     */
    public function setMailHeaders()
    {
        $mail_from = $this->modx->getOption('mail_from', $this->props);
        $this->mail_from = empty($mail_from) ? $this->modx->getOption('emailsender', null) : $mail_from;

        $mail_from_name = $this->modx->getOption('mail_from_name', $this->props);
        $this->mail_from_name = empty($mail_from_name) ? $this->modx->getOption('site_name', null) : $mail_from_name;

        $mail_sender = $this->modx->getOption('mail_sender', $this->props);
        $this->mail_sender = empty($mail_sender) ? $this->modx->getOption('emailsender', null) : $mail_sender;

        $mail_reply_to = $this->modx->getOption('mail_reply_to', $this->props);
        $this->mail_reply_to = empty($mail_reply_to) ? $this->modx->getOption('emailsender', null) : $mail_reply_to;

        $mail_subject = $this->modx->getOption('mail_subject', $this->props);
        $mail_subject = empty($mail_subject) ? $this->modx->resource->get('longtitle') : $mail_subject;
        /* fall back to pagetitle if longtitle is empty */
        $this->mail_subject = empty($mail_subject) ? $this->modx->resource->get('pagetitle') : $mail_subject;
    }

    /**
     * Initializes the MODX mailer
     */
    public function initializeMailer() {
        set_time_limit(0);
        $mail = $this->mail;

        $mail_from = $this->modx->getOption('mail_from', $this->props);
        $this->mail_from = empty($mail_from)
            ? $this->modx->getOption('emailsender', null)
            : $mail_from;

        $mail_from_name = $this->modx->getOption('mail_from_name', $this->props);
        $this->mail_from_name = empty($mail_from_name)
            ? $this->modx->getOption('site_name', null)
            : $mail_from_name;

        $mail_sender = $this->modx->getOption('mail_sender', $this->props);
        $this->mail_sender = empty($mail_sender)
            ? $this->modx->getOption('emailsender', null)
            : $mail_sender;

        $mail_reply_to = $this->modx->getOption('mail_reply_to', $this->props);
        $this->mail_reply_to = empty($mail_reply_to)
            ? $this->modx->getOption('emailsender', null)
            : $mail_reply_to;

        $mail_subject = $this->modx->getOption('mail_subject', $this->props);
        $mail_subject = empty($mail_subject)
            ? $this->modx->resource->get('longtitle')
            : $mail_subject;
        /* fall back to pagetitle if longtitle is empty */
        $this->mail_subject = empty($mail_subject)
            ? $this->modx->resource->get('pagetitle')
            : $mail_subject;

        $mail->set(modMail::MAIL_FROM, $this->mail_from);
        $mail->set(modMail::MAIL_FROM_NAME, $this->mail_from_name);
        $mail->set(modMail::MAIL_SENDER, $this->mail_sender);
        $mail->set(modMail::MAIL_SUBJECT, $this->mail_subject);
        $mail->address('reply-to', $this->mail_reply_to);
        $mail->setHTML(true);

    }

    /**
     * Injects the unsubscribe link into the message
     *
     * @param $profileId int -- ID of the user's profile
     * @return string - the full email including the unsubscribe link
     */
    public function injectUnsubscribe($profileId) {
        $profile = $this->modx->getObject($this->classPrefix .
            'modUserProfile', $profileId);
        $url = $this->unSub->createUrl($this->unSubUrl, $profile);
        $tpl = str_replace('[[+unsubscribeUrl]]', $url, $this->unSubTpl);
        if (stristr($this->html, '</body>')) {
            /* inject link just above the closing body tag */
            $html = $this->html;
            $html = str_replace('</body>', "\n" . $tpl . "\n" . '</body>', $html);
        } else {
            /* append link to the end if there is no body tag */
            $html = $this->html . $tpl;
        }
        unset($profile);
        return $html;

    }

    /**
     * Sends an individual email
     * @param $address string - user's email address
     * @param $name string - user's fullname (or username if fullname is empty)
     * @param $profileId int - ID of the user's profile
     * @return bool - true on success, false on failure
     */
    public function sendMail($address, $name, $profileId)
    {
        $mail = $this->mail;
        $html = $this->injectUnsubscribe($profileId);
        // my_debug("Tpl: " . $tpl);
        // my_debug("HTML: " . $html);
        $mail->set(modMail::MAIL_BODY_TEXT, strip_tags($html));
        $mail->set(modMail::MAIL_BODY, $html);
        $mail->address('to', $address, $name);
        $success = $mail->send();
        if (! $success) {
            $this->setError($mail->mailer->ErrorInfo);
        }
        $mail->mailer->ClearAddresses();
        /* $this->modx->mail->mailer->ClearBCCs(); */
        return $success;
    }

    /**
     * Sends emails to the entire list
     * Logs successful sends, echoes failures
     * @return bool - true on success false on failure
     */
    public function sendBulkEmail()
    {
        /* @var $user modUser */
        if (empty ($this->groups)) {
            $this->setError('No User Groups selected to send bulk email to');
            return false;
        }
        $recipients = array();
        $userGroupNames = explode(',', $this->groups);
        /* Build Recipient array */
        foreach ($userGroupNames as $userGroupName) {
            /* @var $group modUserGroup */
            $userGroupName = trim($userGroupName);
            /* allow UserGroup name or ID */
            $c = intval($userGroupName);
            $c = is_int($c) && !empty($c) ? $userGroupName : array('name' => $userGroupName);
            $group = $this->modx->getObject($this->classPrefix .
                'modUserGroup',$c);

            if (empty($group)) {
                $this->setError('Could not find User Group: ' . $userGroupName);
            }
            //***
            /* get users */
            $c = $this->modx->newQuery($this->userClass);
            $c->innerJoin('modUserGroupMember','UserGroupMembers');
            $c->where(array(
                'UserGroupMembers.user_group' => $group->get('id'),
                'active' => '1',
            ));
            //$total = $this->modx->getCount($this->userClass,$c);
            $c->select($this->modx->getSelectColumns($this->userClass,$this->userClass),"", array('id','username','active'));
            $c->sortby($this->modx->escape($this->sortByAlias).'.'.$this->modx->escape($this->sortBy),'ASC');
            $users = $this->modx->getIterator($this->userClass,$c);

           /* $ugms = $group->getMany('UserGroupMembers');
            if (empty ($ugms)) {
                $this->setError('User Group: ' . $userGroupName . ' has no members');
            }*/

            foreach ($users as $user) {
                /* @var $user modUser */
                /* get the user id */
                /* get the user object and username */

                $username = $user->get('username');

                /* get the user's profile and extract email and fullname */

                $profile = $user->getOne($this->profileAlias);
                if ($profile) {
                    $userTags = $this->getUserTags($profile);
                    $email = $profile->get('email');
                    $fullName = $profile->get('fullname');

                } else {
                    $this->setError('User has no Profile');
                }

                /* fall back to username if fullname is empty */
                $fullName = empty($fullName) ? $username : $fullName;

                /* process tags if Tags TV is set */
                if (!empty ($this->tags)) {
                    $tags = explode(',',$this->tags);
                    $hasTag = false;

                    foreach ($tags as $tag) {
                        $tag = trim($tag);


                        if ( (!empty($tag)) && stristr($userTags,$tag)) {
                            $hasTag = true;
                        }
                    }
                    if (! $hasTag) {
                        continue;
                    }
                }

                if (! empty($email)) {
                    /* add user data to recipient array */

                    /* Either no tags are in use or this user has a tag.
                     * Add user to recipient array */
                    $recipients[] = array(
                        'group' => $userGroupName,
                        'email' => $email,
                        'fullName' => $fullName,
                        'userTags' => $userTags,
                        'profileId' => $profile->get('id'),
                    );
                } else {
                    $this->setError('User: ' . $username . ' has no email address');
                }
            }
        }

        unset($users);

        if (empty($recipients)) {
            $this->setError('No Recipients to send to');
        }
        /* skip mail send if any errors are set */
        if (!empty($this->errors)) {
            $this->setError('Bulk Emails not sent');
            return false;
        }
        /* $recipients array now complete and no errors - send bulk emails */
        $i = 1;
        $fp = fopen($this->logFile, 'w');
        if (!$fp) {
            $this->setError('Could not open log file');
        } else {
            //fwrite($fp,print_r($recipients, true));
        }
        foreach ($recipients as $recipient) {
            if ($this->sendMail($recipient['email'], $recipient['fullName'], $recipient['profileId'])) {
                if ($fp) {
                    fwrite($fp, 'Successful send to: ' . $recipient['email'] . ' (' . $recipient['fullName'] . ') User Tags: ' . $recipient['userTags'] . "\n");
                }
            } else {
                if ($fp) {
                    $e = array_pop($this->errors);
                    fwrite($fp, 'Error sending to: ' . $recipient['email'] . ' (' . $recipient['fullName'] . ') ' . $e . "\n");
                }
            }
            sleep($this->itemDelay);

            /* implement batch delay if it's time */
            if (($i % $this->batchSize) == 0) {
                sleep($this->batchDelay);
            }
            $i++;
        }
        if ($fp) {
            fclose($fp);
        }
        return true;
    }

    /**
     * Sends test email
     *
     * @param $address string - email address to send to
     * @param $name string - name of admin
     * @param $profileId - ID of admin's user profile
     */
    public function sendTestEmail($address, $name, $profileId){
        if (empty($address)) {
            $this->setError('TestEmailAddress is empty; test email not sent');
            return;
        }

        if (! $this->sendMail($address, $name, $profileId)) {
            $this->setError('Test email not sent');
        }
        return;
    }

    /**
     * Creates an HTML display showing messages in the error array
     *
     * @return string
     */
    public function showErrorStrings() {
           $retVal = '';
           foreach ($this->errors as $error) {
               $retVal .= '<h3>' . $error . "</h3>\n";
           }
           return $retVal;
    }

    /**
     * Adds and error string to the errors array
     *
     * @param $error
     */
    public function setError($error){
        $this->errors[] = $error;
    }

    /**
     * Returns the array of error messages
     *
     * @return int
     */
    public function getErrors() {
        return count($this->errors);
    }


} /* end class */
