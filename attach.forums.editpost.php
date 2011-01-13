<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_SED_EXTPLUGIN]
Code=attach
Part=forums.editpost
File=attach.forums.editpost
Hooks=forums.editpost.update.done
Tags=
Order=99
[END_SED_EXTPLUGIN]
==================== */
if (!defined('SED_CODE')) { die('Wrong URL.'); }

// Notice that the order of 99 is neaded because we modify headers to transfer error message

if($cfg['plugin']['attach']['forums'] && sed_auth('plug', 'attach', 'W'))
{
	require_once($cfg['plugins_dir'].'/attach/inc/functions.php');

	$i = 0;
	$err_url = '';
	foreach($_FILES as $key => $val)
	{
		if(preg_match('#^att_file_(\d+)$#', $key, $mt))
		{
			if(empty($_POST["att_title_{$mt[1]}"]))
			{
				$att_name = $_FILES[$key]['name'];
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
				if(!att_remove($mt[1]) || $err = att_add('frm', $p, $q, $key, $att_title))
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
					$att_name = $_FILES[$key]['name'];
					if(!empty($att_name)) $att_title = $att_name;
					else $att_title = $L['att_title']; 
				}
				else $att_title = $_POST["att_title{$mt[1]}"];
				if($err = att_add('frm', $p, $q, $key, $att_title))
					$err_url .= "&err$i=".$err;
			}
		}
		$i++;
	}
	
	// If there were errors, get back
	if(!empty($err_url))
	{
		sed_forum_sectionsetlast($fp_sectionid);
		header('Location: ' . SED_ABSOLUTE_URL . sed_url('forums', "m=editpost&s=$s&q=$q&p=$p&".sed_xg().$err_url, '', true));
		exit;
	}
}
?>