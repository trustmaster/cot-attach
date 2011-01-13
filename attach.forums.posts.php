<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_SED_EXTPLUGIN]
Code=attach
Part=forums.posts
File=attach.forums.posts
Hooks=forums.posts.loop
Tags=forums.posts.tpl:{FORUMS_POSTS_ROW_ATTACH}
Order=10
[END_SED_EXTPLUGIN]
==================== */
if (!defined('SED_CODE')) { die('Wrong URL.'); }

if($cfg['plugin']['attach']['forums'] && sed_auth('plug', 'attach', 'R'))
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
	}

	$t1 = new XTemplate(sed_skinfile('attach.forums.posts', true));
	
	$att_sql = sed_sql_query("SELECT att_id, att_path, att_ext, att_img, att_size, att_title, att_count
		FROM $db_attach WHERE att_type = 'frm' AND att_item = {$row['fp_id']}
		ORDER BY$att_order att_id ASC");
	while($att = sed_sql_fetcharray($att_sql))
	{

		$t1->assign(array(
			'ROW_ATTACH_URL' => 'plug.php?o=attach&id='.$att['att_id'],
			'ROW_ATTACH_ICON' => file_exists("images/pfs/{$att['att_ext']}.gif") ? "images/pfs/{$att['att_ext']}.gif" : 'images/pfs/zip.gif',
			'ROW_ATTACH_SIZE' => round($att['att_size'] / 1024, 1),
			'ROW_ATTACH_CAPTION' => $att['att_title'],
			'ROW_ATTACH_HITS' => $att['att_count']
		));
		if(((int) $att['att_img']) && $cfg['plugin']['attach']['thumbs'])
		{
			$t1->assign('ROW_ATTACH_THUMB', att_get_thumb($att['att_path']));
			$t1->parse('MAIN.ROW_ATTACH.ROW_ATTACH_IMAGE');
		}
		else
		{
			$t1->parse('MAIN.ROW_ATTACH.ROW_ATTACH_FILE');
		}
		$t1->parse('MAIN.ROW_ATTACH');
	}
	sed_sql_freeresult($att_sql);

	$t1->parse('MAIN');

	$t->assign('FORUMS_POSTS_ROW_ATTACH', $t1->text('MAIN'));
}
?>