<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=message.tags
Tags=message.tpl:{MESSAGE_ERROR_MSG}
Order=10
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

if($cfg['plugin']['attach']['pages'] && cot_auth('plug', 'attach', 'W'))
{
	require_once cot_incfile('attach', 'plug');
	$err_msg = '';
	for($i = 0; $i < $cfg['plugin']['attach']['items']; $i++)
	{
		$err = cot_import("err$i", 'G', 'ALP');
		if(!empty($err)) $err_msg .= $L["att_err_$err"].'<br />';
	}
	if(!empty($err_msg))
	{
		$t->assign('MESSAGE_ERROR_MSG', $err_msg);
		$t->parse('MAIN.ERROR');
	}
}
?>