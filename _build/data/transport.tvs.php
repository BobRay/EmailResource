<?php $templateVariables = array();
$templateVariables[0]= $modx->newObject('modTemplateVar');
$templateVariables[0]->fromArray(array(
    'id' => '1',
    'type' => 'option',
    'name' => 'PreviewEmail',
    'caption' => 'Preview Email',
    'description' => 'er_preview_email_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => 'Yes==Yes||No==No',
    'rank' => '1',
    'display' => 'default',
    'display_params' => '',
    'default_text' => 'No',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);

$templateVariables[1]= $modx->newObject('modTemplateVar');
$templateVariables[1]->fromArray(array(
    'id' => '2',
    'type' => 'option',
    'name' => 'EmailOnPreview',
    'caption' => 'Bulk Email On Preview',
    'description' => 'er_email_on_preview_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => 'Yes==Yes||No==No',
    'rank' => '9',
    'display' => 'default',
    'display_params' => '',
    'default_text' => 'No',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);

$templateVariables[2]= $modx->newObject('modTemplateVar');
$templateVariables[2]->fromArray(array(
    'id' => '3',
    'type' => 'option',
    'name' => 'SendTestEmail',
    'caption' => 'Send Test Email',
    'description' => 'er_send_test_email_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => 'Yes==Yes||No==No',
    'rank' => '2',
    'display' => 'default',
    'display_params' => '',
    'default_text' => 'No',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);

$templateVariables[3]= $modx->newObject('modTemplateVar');
$templateVariables[3]->fromArray(array(
    'id' => '4',
    'type' => 'text',
    'name' => 'EmailAddressForTest',
    'caption' => 'Email Address For Test',
    'description' => 'er_email_address_for_test_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => '',
    'rank' => '3',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);

$templateVariables[4]= $modx->newObject('modTemplateVar');
$templateVariables[4]->fromArray(array(
    'id' => '5',
    'type' => 'option',
    'name' => 'CssMode',
    'caption' => 'CSS Mode',
    'description' => 'er_css_mode_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => 'FILE||RESOURCE||CHUNK',
    'rank' => '5',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '@INHERIT FILE',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);

$templateVariables[5]= $modx->newObject('modTemplateVar');
$templateVariables[5]->fromArray(array(
    'id' => '6',
    'type' => 'text',
    'name' => 'CssFile',
    'caption' => 'CSS File(s)',
    'description' => 'er_css_files_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => '',
    'rank' => '6',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '@INHERIT emailresource.css',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);

$templateVariables[6]= $modx->newObject('modTemplateVar');
$templateVariables[6]->fromArray(array(
    'id' => '7',
    'type' => 'option',
    'name' => 'InlineCss',
    'caption' => 'Create Inline CSS',
    'description' => 'er_inline_css_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => 'Yes==Yes||No==No',
    'rank' => '0',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '@INHERIT Yes',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);

$templateVariables[7]= $modx->newObject('modTemplateVar');
$templateVariables[7]->fromArray(array(
    'id' => '8',
    'type' => 'text',
    'name' => 'CssBasePath',
    'caption' => 'CSS Base Path',
    'description' => 'er_css_base_path_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => '',
    'rank' => '7',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '@INHERIT {modx_base_path}assets/components/emailresource/css/',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);

/* Groups, BatchSize, BatchDelay, ItemDelay */

$templateVariables[8]= $modx->newObject('modTemplateVar');
$templateVariables[8]->fromArray(array(
    'id' => '9',
    'type' => 'text',
    'name' => 'Groups',
    'caption' => 'Groups',
    'description' => 'er_groups_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => '',
    'rank' => '10',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);

$templateVariables[9]= $modx->newObject('modTemplateVar');
$templateVariables[9]->fromArray(array(
    'id' => '10',
    'type' => 'text',
    'name' => 'BatchSize',
    'caption' => 'Batch Size',
    'description' => 'er_batch_size_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => '',
    'rank' => '11',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);

$templateVariables[10]= $modx->newObject('modTemplateVar');
$templateVariables[10]->fromArray(array(
    'id' => '11',
    'type' => 'text',
    'name' => 'BatchDelay',
    'caption' => 'Batch delay (seconds)',
    'description' => 'er_batch_delay_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => '',
    'rank' => '12',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '@INHERIT 1',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);

$templateVariables[11]= $modx->newObject('modTemplateVar');
$templateVariables[11]->fromArray(array(
    'id' => '12',
    'type' => 'text',
    'name' => 'ItemDelay',
    'caption' => 'Item delay (seconds)',
    'description' => 'er_item_delay_desc',
    'editor_type' => '0',
    'category' => '',
    'locked' => '',
    'elements' => '',
    'rank' => '13',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '@INHERIT .51',
    'properties' => 'array()',
    'lexicon' => 'emailresource:tvs',
),'',true,true);


return $templateVariables;