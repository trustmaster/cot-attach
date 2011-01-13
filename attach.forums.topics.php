<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_SED_EXTPLUGIN]
Code=attach
Part=forums.topics
File=attach.forums.topics
Hooks=forums.topics.loop
Tags=forums.topics.tpl:{FORUMS_TOPICS_ROW_ATTACH}
Order=10
[END_SED_EXTPLUGIN]
==================== */
if (!defined('SED_CODE')) { die('Wrong URL.'); }

if($cfg['plugin']['attach']['forums'] && sed_auth('plug', 'attach', 'R'))
{
	require_once($cfg['plugins_dir'].'/attach/inc/functions.php');

	if(sed_sql_result(sed_sql_query("SELECT COUNT(*) FROM $db_attach WHERE att_type = 'frm' AND att_parent = {$row['ft_id']}"), 0, 0) > 0)
		$t->assign('FORUMS_TOPICS_ROW_ATTACH', '<a href="'.sed_url('plug', 'o=attach&q='.$row['ft_id']).'" target="_blank"><img src="'.$cfg['plugins_dir'].'/attach/img/attach.gif" alt="V" /></a>');
	else
		$t->assign('FORUMS_TOPICS_ROW_ATTACH', '');
}
?>