<?php

class EmailResource
{

    protected $html;
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


    public function __construct(&$modx, &$props)
    {
        $this->modx =& $modx;
        $this->props =& $props;
        /* er paths; Set the er. System Settings only for development */
        $this->corePath = $this->modx->getOption('er.core_path', null, MODX_CORE_PATH . 'components/emailresource/');
        $this->assetsPath = $this->modx->getOption('er.assets_path', null, MODX_ASSETS_PATH . 'components/emailresource/');
        $this->assetsUrl = $this->modx->getOption('er.assets_url', null, MODX_ASSETS_URL . 'components/emailresource/');

    }

    public function init()
    {
        $this->logFile = $this->corePath . 'logs/' . date('Y-m-d-h.i.sa');
        $this->errors = array();
        $cssBasePath = $this->modx->resource->getTVValue('CssBasePath');

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

    }

    public function fullUrls($base)
    {
        $html =& $this->html;
        /* remove any spaces around = sign */
        $html = preg_replace('@(href|src)\s*=\s*@', '\1=', $html);

        /* Correct base URL, if necessary */
        $server = preg_replace('@^([^\:]*)://([^/*]*)(/|$).*@', '\1://\2/', $base);

        /* handle root-relative URLs */
        $html = preg_replace('@\<([^>]*) (href|src)="/([^"]*)"@i', '<\1 \2="' . $server . '\3"', $html);

        /* handle base-relative URLs */
        $html = preg_replace('@\<([^>]*) (href|src)="(([^\:"])*|([^"]*:[^/"].*))"@i', '<\1 \2="' . $base . '\3"', $html);

    }

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

    public function inlineCss()
    {
        $root = MODX_BASE_PATH;
        //$assets_path = $root . 'assets/components/emailresource/';
        $core_path = $root . 'core/components/emailresource/';
        require $core_path . 'model/emailresource/css_to_inline_styles.class.php';

        $css = '';
        if (empty($this->cssFiles)) {
            $this->setError('cssFiles is empty');
        }
        foreach ($this->cssFiles as $cssFile) {
            switch ($this->cssMode) {

                case 'RESOURCE':
                    $res = $this->modx->getObject('modResource', array('pagetitle' => $cssFile));
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

    protected function strReplaceAssoc(array $replace, $subject) {
           return str_replace(array_keys($replace), array_values($replace), $subject);
    }


    public function my_debug($message, $clear = false)
    {
        $chunk = $this->modx->getObject('modChunk', array('name' => 'debug'));
        if (!$chunk) {
            $chunk = $this->modx->newObject('modChunk', array('name' => 'debug'));
            $chunk->save();
            $chunk = $this->modx->getObject('modChunk', array('name' => 'debug'));
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
    public function setHtml($html) {
        $this->html = $html;
    }
    public function getHtml() {
        return $this->html;
    }

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
    public function initializeMailer() {
        $this->modx->getService('mail', 'mail.modPHPMailer');
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
        $this->modx->mail->set(modMail::MAIL_BODY, $this->html);
        $this->modx->mail->set(modMail::MAIL_FROM, $this->mail_from);
        $this->modx->mail->set(modMail::MAIL_FROM_NAME, $this->mail_from_name);
        $this->modx->mail->set(modMail::MAIL_SENDER, $this->mail_sender);
        $this->modx->mail->set(modMail::MAIL_SUBJECT, $this->mail_subject);
        $this->modx->mail->address('reply-to', $this->mail_reply_to);
        $this->modx->mail->setHTML(true);

    }

    public function sendMail($address, $name)
    {

        //$this->modx->mail->reset();
        $this->modx->mail->mailer->clearAddresses();

        $this->modx->mail->address('to', $address, $name);

        $sent = $this->modx->mail->send();

        if ($sent) {
            return true;
        } else {
            $this->setError($this->modx->mail->mailer->ErrorInfo);
            return false;
        }

    }

    public function sendBulkEmail()
    {

        if (empty ($this->groups)) {
            $this->setError('No User Groups selected to send bulk email to');
            return false;
        }
        $recipients = array();
        $userGroupNames = explode(',', $this->groups);
        /* Build Recipient array */
        foreach ($userGroupNames as $userGroupName) {
            $group = $this->modx->getObject('modUserGroup', array('name' => trim($userGroupName)));
            if (empty($group)) {
                $this->setError('Could not find User Group: ' . $userGroupName);
            }

            $ugms = $group->getMany('UserGroupMembers');
            if (empty ($ugms)) {
                $this->setError('User Group: ' . $userGroupName . ' has no members');
            }
            /* create recipient array from user modUserGroupMember objects */
            foreach ($ugms as $ugm) {
                /* get the user id */
                $memberId = $ugm->get('member');

                /* get the user object and username */
                $user = $this->modx->getObject('modUser', $memberId);
                $username = $user->get('username');

                /* get the user's profile and extract email and fullname */
                $profile = $user->getOne('Profile');
                $email = $profile->get('email');
                $fullName = $profile->get('fullname');

                /* fall back to username if fullname is empty */
                $fullName = empty($fullName) ? $username : $fullName;

                /* add user data to recipient array */
                $recipients[] = array(
                    'group' => $userGroupName,
                    'email' => $email,
                    'fullName' => $fullName,
                );
            }
        }
        unset($users, $ugms);

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
        }
        set_time_limit(0);
        foreach ($recipients as $recipient) {
            if ($this->sendMail($recipient['email'], $recipient['fullName'])) {
                if ($fp) {
                    fwrite($fp, 'Successful send to: ' . $recipient['email'] . ' (' . $recipient['fullName'] . ")\n");
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
    public function sendTestEmail($address, $name){
        if (empty($address)) {
            $this->setError('TestEmailAddress is empty; test email not sent');
            return;
        }
        if (! $this->sendMail($address, $name)) {
            $this->setError('Test email not sent');
        }
    }

    public function setError($error){
        $this->errors[] = $error;
    }
    public function getErrors() {
        return $this->errors;
    }
    public function showErrors() {
        $retVal = '';
        foreach ($this->errors as $error) {
            $retVal .= '<p>' . $error . "</p>\n";
        }
        return $retVal();
    }

} /* end class */


