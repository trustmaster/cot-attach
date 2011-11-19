<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=page.edit.update.done
Tags=
Order=99
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

// Notice that the order of 99 is neaded because we modify headers to transfer error message

if($cfg['plugin']['attach']['pages'] && cot_auth('plug', 'attach', 'W'))
{

	$item_id = $id;
	require_once cot_incfile('attach', 'plug');

	$i = 0;
	$err_url = '';
	foreach($_FILES as $key => $val)
	{
		if(preg_match('#^att_file_(\d+)$#', $key, $mt))
		{
			if(empty($_POST["att_title_{$mt[1]}"]))
			{
				$att_name = cot_import($_FILES[$key]['name'], 'D', 'TXT');
				if(!empty($att_name)) $att_title = $att_name;
				else $att_title = $L['att_title']; 
			}
			else $att_title = $_POST["att_title_{$mt[1]}"];
			// Update existing file
			if($_POST["att_del_{$mt[1]}"] && !$_POST["att_rpl_{$mt[1]}"])
			{
				// Remove only
				if(!att_remove($mt[1]))
					$err_url .= "&err$i=delete"; // Err msg transfer
			}
			elseif($_POST["att_rpl_{$mt[1]}"] && !$_POST["att_del_{$mt[1]}"] && !empty($val['tmp_name']) && $val['size'] > 0)
			{
				// Replace only
				if($err = att_update($mt[1], $key, $att_title))
					$err_url .= "&err$i=".$err;
			}
			elseif($_POST["att_rpl_{$mt[1]}"] && $_POST["att_del_{$mt[1]}"] && !empty($val['tmp_name']) && $val['size'] > 0)
			{
				// Remove and replace
				if(!att_remove($mt[1]) || $err = att_add('pag', $id, crc32($db->prep($rpage['page_cat'])), $key, $att_title))
					$err_url .= "&err$i=replace";
			}
			else
			{
				// Change caption
				if($err = att_update_title($mt[1], $att_title))
					$err_url .= "&err$i=".$err;
			}
		}
		elseif(preg_match('#^att_file(\d+)$#', $key, $mt))
		{
			// Upload a new file
			if(!empty($val['tmp_name']) && $val['size'] > 0)
			{
				if(empty($_POST["att_title{$mt[1]}"]))
				{
					$att_name = cot_import($_FILES[$key]['name'], 'D', 'TXT');
					if(!empty($att_name)) $att_title = $att_name;
					else $att_title = $L['att_title']; 
				}
				else $att_title = $_POST["att_title{$mt[1]}"];
				if($err = att_add('pag', $id, crc32($db->prep($rpage['page_cat'])), $key, $att_title))
					$err_url .= "&err$i=".$err;
			}
		}
		$i++;
	}
	
	// If there were errors, get back
	if(!empty($err_url))
	{
		cot_log("Edited page #".$id,'adm');
		cot_redirect(cot_url('page', "id=$id&m=edit&r=list&".cot_xg().$err_url, '', true));
		exit;
	}
}
?>