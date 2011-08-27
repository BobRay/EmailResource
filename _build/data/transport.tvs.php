<?php $templateVariables = array();
$templateVariables[0]= $modx->newObject('modTemplateVar');
$templateVariables[0]->fromArray(array(
    'id' => '15',
    'type' => 'option',
    'name' => 'PreviewEmail',
    'caption' => 'Preview Email',
    'description' => 'Show preview of email when resource is previewed',
    'editor_type' => '0',
    'category' => '52',
    'locked' => '',
    'elements' => 'Yes==Yes||No==No',
    'rank' => '1',
    'display' => 'default',
    'display_params' => '',
    'default_text' => 'No',
    'properties' => 'array()',
),'',true,true);

$templateVariables[1]= $modx->newObject('modTemplateVar');
$templateVariables[1]->fromArray(array(
    'id' => '16',
    'type' => 'option',
    'name' => 'EmailOnPreview',
    'caption' => 'Bulk Email On Preview',
    'description' => 'Email the resource to all subscribers when it is previewed',
    'editor_type' => '0',
    'category' => '52',
    'locked' => '',
    'elements' => 'Yes==Yes||No==No',
    'rank' => '10',
    'display' => 'default',
    'display_params' => '',
    'default_text' => 'No',
    'properties' => 'array()',
),'',true,true);

$templateVariables[2]= $modx->newObject('modTemplateVar');
$templateVariables[2]->fromArray(array(
    'id' => '17',
    'type' => 'option',
    'name' => 'SendTestEmail',
    'caption' => 'Send Test Email',
    'description' => 'Send a test email of the resource when it is previewed',
    'editor_type' => '0',
    'category' => '52',
    'locked' => '',
    'elements' => 'Yes==Yes||No==No',
    'rank' => '2',
    'display' => 'default',
    'display_params' => '',
    'default_text' => 'No',
    'properties' => 'array()',
),'',true,true);

$templateVariables[3]= $modx->newObject('modTemplateVar');
$templateVariables[3]->fromArray(array(
    'id' => '18',
    'type' => 'text',
    'name' => 'EmailAddressForTest',
    'caption' => 'Email Address For Test',
    'description' => 'Email address to sent test email to',
    'editor_type' => '0',
    'category' => '52',
    'locked' => '',
    'elements' => '',
    'rank' => '3',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '',
    'properties' => 'array()',
),'',true,true);

$templateVariables[4]= $modx->newObject('modTemplateVar');
$templateVariables[4]->fromArray(array(
    'id' => '19',
    'type' => 'option',
    'name' => 'CssMode',
    'caption' => 'CSS Mode',
    'description' => 'Specifies how the CSS is stored.',
    'editor_type' => '0',
    'category' => '52',
    'locked' => '',
    'elements' => 'FILE||RESOURCE||CHUNK',
    'rank' => '5',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '@INHERIT FILE',
    'properties' => 'array()',
),'',true,true);

$templateVariables[5]= $modx->newObject('modTemplateVar');
$templateVariables[5]->fromArray(array(
    'id' => '20',
    'type' => 'text',
    'name' => 'CssFile',
    'caption' => 'CSS File(s)',
    'description' => 'Comma separated list of CSS files, chunk, or resource names. For files, CssBasePath will be prepended.',
    'editor_type' => '0',
    'category' => '52',
    'locked' => '',
    'elements' => '',
    'rank' => '6',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '@INHERIT',
    'properties' => 'array()',
),'',true,true);

$templateVariables[6]= $modx->newObject('modTemplateVar');
$templateVariables[6]->fromArray(array(
    'id' => '21',
    'type' => 'option',
    'name' => 'InlineCss',
    'caption' => 'Create Inline CSS',
    'description' => 'Use CSS files to create inline CSS',
    'editor_type' => '0',
    'category' => '52',
    'locked' => '',
    'elements' => 'Yes==Yes||No==No',
    'rank' => '0',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '@INHERIT Yes',
    'properties' => 'array()',
),'',true,true);

$templateVariables[7]= $modx->newObject('modTemplateVar');
$templateVariables[7]->fromArray(array(
    'id' => '22',
    'type' => 'text',
    'name' => 'CssBasePath',
    'caption' => 'CSS Base Path',
    'description' => 'Path of directory containing CSS files (ignored if mode is RESOURCE or CHUNK) -- should end with a slash.',
    'editor_type' => '0',
    'category' => '52',
    'locked' => '',
    'elements' => '',
    'rank' => '7',
    'display' => 'default',
    'display_params' => '',
    'default_text' => '@INHERIT {modx_base_path}assets/components/emailresource/css/',
    'properties' => 'array()',
),'',true,true);

return $templateVariables;