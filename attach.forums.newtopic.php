<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=forums.newtopic.newtopic.done
Order=99
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

$item_id = $p;

// Notice that the order of 99 is needed because we modify $q to transfer error message

if($cfg['plugin']['attach']['forums'] && cot_auth('plug', 'attach', 'W'))
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
			if($err = att_add('frm', $item_id, $q, "att_file$i", $att_title))
				$err_url .= "&err$i=".urlencode($err); // Error msg transfer hack
		}
	}
	$q .= $err_url;
}
?>