<?php
/* ====================
Copyright (c) 2008, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=page.add.tags
Tags=page.add.tpl:{PAGEADD_ATTACH}
Order=10
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

if($cfg['plugin']['attach']['pages'] && cot_auth('plug', 'attach', 'W'))
{
	require_once cot_incfile('attach', 'plug');

	$t1 = new XTemplate(cot_tplfile('attach.page.add', 'plug'));
	
	$limits = att_get_limits();
	$t1->assign(array(
		'ATTACH_MAXFILESIZE' => $limits['file'],
		'ATTACH_TOTALSPACE' => $limits['total'],
		'ATTACH_USEDSPACE' => $limits['used'],
		'ATTACH_LEFTSPACE' => $limits['left'],
		'ATTACH_PERSURL' => cot_url('plug', 'e=attach&uid='.$usr['id'])
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