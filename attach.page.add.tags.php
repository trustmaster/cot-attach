<?php
/* ====================
Copyright (c) 2008, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.
[BEGIN_SED]
File=plugins/attach/attach.page.add.tags.php
Version=121
Updated=2008-jan-30
Type=Plugin
Author=Trustmaster
Description=
[END_SED]

[BEGIN_SED_EXTPLUGIN]
Code=attach
Part=page.add.tags
File=attach.page.add.tags
Hooks=page.add.tags
Tags=page.add.tpl:{PAGEADD_ATTACH}
Order=10
[END_SED_EXTPLUGIN]
==================== */
if (!defined('SED_CODE')) { die('Wrong URL.'); }

if($cfg['plugin']['attach']['pages'] && sed_auth('plug', 'attach', 'W'))
{
	require_once($cfg['plugins_dir'].'/attach/inc/functions.php');

	$t1 = new XTemplate(sed_skinfile('attach.page.add', true));
	
	$limits = att_get_limits();
	$t1->assign(array(
		'ATTACH_MAXFILESIZE' => $limits['file'],
		'ATTACH_TOTALSPACE' => $limits['total'],
		'ATTACH_USEDSPACE' => $limits['used'],
		'ATTACH_LEFTSPACE' => $limits['left'],
		'ATTACH_PERSURL' => sed_url('plug', 'o=attach&uid='.$usr['id'])
	));
	
	for($i = 0; $i < $cfg['plugin']['attach']['items']; $i++)
	{
		$t1->assign(array(
			'ATTACH_ROW_CAPTION' => "att_title$i",
			'ATTACH_ROW_FILE' => "att_file$i",
		));
		$t1->parse('MAIN.ATTACH_ROW');
	}

	$t1->parse('MAIN');
	
	$t->assign('PAGEADD_ATTACH', $t1->text('MAIN'));
}
?>