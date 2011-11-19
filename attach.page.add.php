<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=page.add.add.done
Order=99
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

$item_id = $id;

// Notice that the order of 99 is neaded because we modify headers to transfer error message

if($cfg['plugin']['attach']['pages'] && cot_auth('plug', 'attach', 'W'))
{
	require_once cot_incfile('attach', 'plug');
	
	$err_url = '';
	for($i = 0; $i < $cfg['plugin']['attach']['items']; $i++)
	{
		if(!empty($_FILES["att_file$i"]) && $_FILES["att_file$i"]['size'] > 0)
		{
			if(empty($_POST["att_title$i"]))
			{
				$att_name = cot_import($_FILES["att_file$i"]['name'], 'D', 'TXT');
				if(!empty($att_name)) $att_title = $att_name;
				else $att_title = $L['att_title']; 
			}
			else $att_title = $_POST["att_title$i"];
			if($err = att_add('pag', $item_id, crc32($db->prep($rpage['page_cat'])), "att_file$i", $att_title))
				$err_url .= "&err$i=".$err; // Error msg transfer hack
		}
	}
	
	// If there were errors, show them
	if(!empty($err_url))
	{
		cot_shield_update(30, 'New page');
		cot_redirect(cot_url('message', 'message.php?msg=300'.$err_url, '', true));
		exit;
	}
}
?>