<?php
/**
 * EmailResource
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
 * EmailResource; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package emailresource
 */
/**
 * Properties (property descriptions) Lexicon Topic
 *
 * @package emailresource
 * @subpackage lexicon
 */

/* EmailResource Property Description strings */
$_lang['er_mail_from_desc'] = '(optional) MAIL_FROM setting for email. Defaults to emailsender System Setting';
$_lang['er_mail_from_name_desc'] = '(optional) MAIL_FROM_NAME setting for email. Defaults to site_name System Setting.';
$_lang['er_mail_sender_desc'] = '(optional) EMAIL_SENDER setting for email. Defaults to emailsender System Setting.';
$_lang['er_mail_reply_to_desc'] = '(optional) REPLY_TO setting for email. Defaults to emailsender System Setting';
$_lang['er_mail_subject_desc'] = '(optional) MAIL_SUBJECT setting for email. Defaults to resource longtitle, or pagetitle if longtitle is empty.';
$_lang['er_template_list_desc'] = '(optional but highly recommended) Comma-separated list of Template IDs. List all templates used by resources that might be emailed. This will speed up the site by preventing the plugin from running for pages that will not be emailed.';
$_lang['er_unsubscribe_tpl_desc'] = '(optional) Name of the chunk to use for the Unsubscribe link.';
