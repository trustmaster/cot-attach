<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_SED_EXTPLUGIN]
Code=attach
Part=page.add
File=attach.page.add
Hooks=page.add.add.done
Tags=
Order=99
[END_SED_EXTPLUGIN]
==================== */
if (!defined('SED_CODE')) { die('Wrong URL.'); }

$item_id = $id;

// Notice that the order of 99 is neaded because we modify headers to transfer error message

if($cfg['plugin']['attach']['pages'] && sed_auth('plug', 'attach', 'W'))
{
	require_once($cfg['plugins_dir'].'/attach/inc/functions.php');
	
	$err_url = '';
	for($i = 0; $i < $cfg['plugin']['attach']['items']; $i++)
	{
		if(!empty($_FILES["att_file$i"]) && $_FILES["att_file$i"]['size'] > 0)
		{
			if(empty($_POST["att_title$i"]))
			{
				$att_name = $_FILES["att_file$i"]['name'];
				if(!empty($att_name)) $att_title = $att_name;
				else $att_title = $L['att_title']; 
			}
			else $att_title = $_POST["att_title$i"];
			if($err = att_add('pag', $item_id, crc32(sed_sql_prep($newpagecat)), "att_file$i", $att_title))
				$err_url .= "&err$i=".$err; // Error msg transfer hack
		}
	}
	
	// If there were errors, show them
	if(!empty($err_url))
	{
		sed_shield_update(30, 'New page');
		header('Location: ' . SED_ABSOLUTE_URL . sed_url('message', 'message.php?msg=300'.$err_url, '', true));
		exit;
	}
}
?>