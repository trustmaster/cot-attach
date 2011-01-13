<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_SED_EXTPLUGIN]
Code=attach
Part=forums.posts.newpost
File=attach.forums.posts.newpost
Hooks=forums.posts.newpost.done
Tags=
Order=99
[END_SED_EXTPLUGIN]
==================== */
if (!defined('SED_CODE')) { die('Wrong URL.'); }

$item_id = $p;

// Notice that the order of 99 is neaded because we modify $q to transfer error message

if($cfg['plugin']['attach']['forums'] && sed_auth('plug', 'attach', 'W'))
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
			if($err = att_add('frm', $item_id, $q, "att_file$i", $att_title))
				$err_url .= "&err$i=".urlencode($err); // Error msg transfer hack
		}
	}
	$q .= $err_url;
}
?>