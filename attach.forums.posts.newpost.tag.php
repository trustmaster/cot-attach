<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=forums.posts.newpost.tags
Tags=forums.posts.tpl:{FORUMS_POSTS_NEWPOST_ATTACH}
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

if($cfg['plugin']['attach']['forums'] && cot_auth('plug', 'attach', 'W'))
{
	require_once cot_incfile('attach', 'plug');

	$t1 = new XTemplate(cot_tplfile('attach.forums.posts.newpost', 'plug'));

	$err_msg = '';
	for($i = 0; $i < $cfg['plugin']['attach']['items']; $i++)
	{
		$err = cot_import("err$i", 'G', 'ALP');
		if(!empty($err)) $err_msg .= $L["att_err_$err"].'<br />';
	}
	if(!empty($err_msg))
	{
		$t->assign('ATTACH_ERROR_MSG', $err_msg);
		$t->parse('MAIN.ATTACH_ERROR');
	}
	
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
	
	$t->assign('FORUMS_POSTS_NEWPOST_ATTACH', $t1->text('MAIN'));
}
?>