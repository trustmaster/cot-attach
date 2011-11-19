<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=page.tags
Tags=page.tpl:{PAGE_ATTACH}
Order=10
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL');

if($cfg['plugin']['attach']['pages'] && cot_auth('plug', 'attach', 'R'))
{
	require_once cot_incfile('attach', 'plug');

	$att_order = '';
	switch($cfg['plugin']['attach']['order'])
	{
		case 'images first':
			$att_order = ' att_img DESC,';
		break;
		case 'files first':
			$att_order = ' att_img ASC,';
		break;
		default:
			$att_order = '';
		break;
	}

	$t1 = new XTemplate(cot_tplfile('attach.page', 'plug'));
	
	$att_sql = $db->query("SELECT att_id, att_path, att_ext, att_img, att_size, att_title, att_count
		FROM $db_attach WHERE att_type = 'pag' AND att_item = {$pag['page_id']}
		ORDER BY$att_order att_id ASC");
	while($att = $att_sql->fetch())
	{

		$t1->assign(array(
			'ATTACH_URL' => cot_url('plug', 'e=attach&id='.$att['att_id']),
			'ATTACH_ICON' => file_exists("images/pfs/{$att['att_ext']}.gif") ? "images/pfs/{$att['att_ext']}.gif" : 'images/pfs/zip.gif',
			'ATTACH_SIZE' => round($att['att_size'] / 1024, 1),
			'ATTACH_CAPTION' => $att['att_title'],
			'ATTACH_HITS' => $att['att_count']
		));
		if(((int) $att['att_img']) && $cfg['plugin']['attach']['thumbs'])
		{
			$t1->assign('ATTACH_THUMB', att_get_thumb($att['att_path']));
			$t1->parse('MAIN.ATTACH.ATTACH_IMAGE');
		}
		else
		{
			$t1->parse('MAIN.ATTACH.ATTACH_FILE');
		}
		$t1->parse('MAIN.ATTACH');
	}
	$att_sql = null;

	$t1->parse('MAIN');

	$t->assign('PAGE_ATTACH', $t1->text('MAIN'));
}
?>