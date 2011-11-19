<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=page.edit.tags
Tags=page.edit.tpl:{PAGEEDIT_ATTACH}
Order=10
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

if($cfg['plugin']['attach']['pages'] && cot_auth('plug', 'attach', 'W'))
{
	require_once cot_incfile('attach', 'plug');

	$t1 = new XTemplate(cot_tplfile('attach.page.edit', 'plug'));
	
	$limits = att_get_limits();
	$t1->assign(array(
		'ATTACH_MAXFILESIZE' => $limits['file'],
		'ATTACH_TOTALSPACE' => $limits['total'],
		'ATTACH_USEDSPACE' => $limits['used'],
		'ATTACH_LEFTSPACE' => $limits['left'],
		'ATTACH_PERSURL' => cot_url('plug', 'e=attach&uid='.$usr['id'])
	));
	
	$err_msg = '';
	for($i = 0; $i < $cfg['plugin']['attach']['items']; $i++)
	{
		$err = cot_import("err$i", 'G', 'ALP');
		if(!empty($err)) $err_msg .= $L["att_err_$err"].'<br />';
	}
	if(!empty($err_msg))
	{
		$t1->assign('ATTACH_ERROR_MSG', $err_msg);
		$t1->parse('MAIN.ATTACH_ERROR');
	}
	
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
	
	$att_sql = $db->query("SELECT att_id, att_path, att_title
		FROM $db_attach WHERE att_type = 'pag' AND att_item = $id
		ORDER BY$att_order att_id ASC");
	$i = 0;
	while($att = $att_sql->fetch())
	{
		$t1->assign(array(
			'ATTACH_ROW_CAPTION' => "att_title_{$att['att_id']}",
			'ATTACH_ROW_CAPTION_VALUE' => $att['att_title'],
			'ATTACH_ROW_FILE' => "att_file_{$att['att_id']}",
			'ATTACH_ROW_REPLACE' => $L['att_replace'].' <input type="checkbox" name="att_rpl_'.$att['att_id'].'" />',
			'ATTACH_ROW_DELETE' => $L['att_delete'].' <input type="checkbox" name="att_del_'.$att['att_id'].'" />',
			'ATTACH_ROW_DISPLAY' => 'display:;',
			'ATTACH_ROW_ID' => "att_file_{$att['att_id']}"
		));
		$t1->parse('MAIN.ATTACH_ROW');
		$i++;
	}
	
	$pos = 0;
	for(; $i < $cfg['plugin']['attach']['items']; $i++)
	{
		$t1->assign(array(
			'ATTACH_ROW_CAPTION' => "att_title$i",
			'ATTACH_ROW_CAPTION_VALUE' => '',
			'ATTACH_ROW_FILE' => "att_file$i",
			'ATTACH_ROW_REPLACE' => '',
			'ATTACH_ROW_DELETE' => '',
			'ATTACH_ROW_DISPLAY' => 'display:none',
			'ATTACH_ROW_ID' => "att_file$pos"
		));
		$t1->parse('MAIN.ATTACH_ROW');
		$pos++;
	}
	$att_sql = null;

	$t1->parse('MAIN');
	
	$t->assign('PAGEEDIT_ATTACH', $t1->text('MAIN'));
}
?>