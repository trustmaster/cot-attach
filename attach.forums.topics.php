<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=forums.topics.loop
Tags=forums.topics.tpl:{FORUMS_TOPICS_ROW_ATTACH}
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

if($cfg['plugin']['attach']['forums'] && cot_auth('plug', 'attach', 'R'))
{
	require_once cot_incfile('attach', 'plug');

	if($db->query("SELECT COUNT(*) FROM $db_attach WHERE att_type = 'frm' AND att_parent = {$row['ft_id']}")->fetchColumn() > 0)
		$t->assign('FORUMS_TOPICS_ROW_ATTACH', '<a href="'.cot_url('plug', 'e=attach&q='.$row['ft_id']).'" target="_blank"><img src="'.$cfg['plugins_dir'].'/attach/img/attach.gif" alt="V" /></a>');
	else
		$t->assign('FORUMS_TOPICS_ROW_ATTACH', '');
}
?>