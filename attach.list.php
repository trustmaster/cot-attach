<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=page.list.loop
Tags=page.list.tpl:{LIST_ROW_PREVIEW}
Order=10
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

if($cfg['plugin']['attach']['listprev'] && $cfg['plugin']['attach']['pages'])
{
	require_once cot_incfile('attach', 'plug');

	$res = $db->query("SELECT att_path FROM $db_attach WHERE att_type = 'pag' AND att_item = {$pag['page_id']} AND att_img = 1 ORDER BY att_id LIMIT 1");
	if($res->rowCount() == 1)
	{
		$att = $res->fetch();
		$prev_path = att_get_preview($att['att_path']);
		if(!file_exists($prev_path))
			att_create_preview($att['att_path']);
		$t->assign('LIST_ROW_PREVIEW', '<img src="'.$prev_path.'" alt="" />');
	}
	else
		$t->assign('LIST_ROW_PREVIEW', '');
}
?>