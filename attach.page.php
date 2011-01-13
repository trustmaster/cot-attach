<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_SED_EXTPLUGIN]
Code=attach
Part=page
File=attach.page
Hooks=page.tags
Tags=page.tpl:{PAGE_ATTACH}
Order=10
[END_SED_EXTPLUGIN]
==================== */
if (!defined('SED_CODE')) { die('Wrong URL.'); }

if($cfg['plugin']['attach']['pages'] && sed_auth('plug', 'attach', 'R'))
{
	require_once($cfg['plugins_dir'].'/attach/inc/functions.php');

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

	$t1 = new XTemplate(sed_skinfile('attach.page', true));
	
	$att_sql = sed_sql_query("SELECT att_id, att_path, att_ext, att_img, att_size, att_title, att_count
		FROM $db_attach WHERE att_type = 'pag' AND att_item = {$pag['page_id']}
		ORDER BY$att_order att_id ASC");
	while($att = sed_sql_fetcharray($att_sql))
	{

		$t1->assign(array(
			'ATTACH_URL' => sed_url('plug', 'o=attach&id='.$att['att_id']),
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
	sed_sql_freeresult($att_sql);

	$t1->parse('MAIN');

	$t->assign('PAGE_ATTACH', $t1->text('MAIN'));
}
?>