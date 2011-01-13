<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_SED_EXTPLUGIN]
Code=attach
Part=message
File=attach.message
Hooks=message.tags
Tags=message.tpl:{MESSAGE_ERROR_MSG}
Order=10
[END_SED_EXTPLUGIN]
==================== */
if (!defined('SED_CODE')) { die('Wrong URL.'); }

if($cfg['plugin']['attach']['pages'] && sed_auth('plug', 'attach', 'W'))
{
	require_once($cfg['plugins_dir'].'/attach/inc/functions.php');
	$err_msg = '';
	for($i = 0; $i < $cfg['plugin']['attach']['items']; $i++)
	{
		$err = sed_import("err$i", 'G', 'ALP');
		if(!empty($err)) $err_msg .= $L["att_err_$err"].'<br />';
	}
	if(!empty($err_msg))
	{
		$t->assign('MESSAGE_ERROR_MSG', $err_msg);
		$t->parse('MAIN.ERROR');
	}
}
?>