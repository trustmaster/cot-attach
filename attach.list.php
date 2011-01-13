<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_SED_EXTPLUGIN]
Code=attach
Part=list
File=attach.list
Hooks=list.loop
Tags=list.tpl:{LIST_ROW_PREVIEW}
Order=10
[END_SED_EXTPLUGIN]
==================== */
if (!defined('SED_CODE')) { die('Wrong URL.'); }

if($cfg['plugin']['attach']['listprev'] && $cfg['plugin']['attach']['pages'])
{
	require_once($cfg['plugins_dir'].'/attach/inc/functions.php');

	$res = sed_sql_query("SELECT att_path FROM $db_attach WHERE att_type = 'pag' AND att_item = {$pag['page_id']} AND att_img = 1 ORDER BY att_id LIMIT 1");
	if(sed_sql_numrows($res) == 1)
	{
		$att = sed_sql_fetcharray($res);
		$prev_path = att_get_preview($att['att_path']);
		if(!file_exists($prev_path))
			att_create_preview($att['att_path']);
		$t->assign('LIST_ROW_PREVIEW', '<img src="'.$prev_path.'" alt="" />');
	}
	else
		$t->assign('LIST_ROW_PREVIEW', '');
}
?>