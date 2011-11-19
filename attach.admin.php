<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=tools
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL.');

require_once cot_incfile('attach', 'plug');
$act = cot_import('act', 'G', 'ALP');

if($act == 'cleanup')
{
	// Remove unused forum attachments
	$condition = "LEFT JOIN $db_forum_posts ON $db_attach.att_item = $db_forum_posts.fp_id
	WHERE $db_attach.att_type = 'frm' AND $db_forum_posts.fp_id IS NULL";
	
	$res = $db->query("SELECT att_id FROM $db_attach $condition");
	$count = $res->rowCount();
	while($att = $res->fetch())
		att_remove($att['att_id']);
	$res = null;
	
	$res = $db->query("DELETE FROM $db_attach USING $db_attach $condition");
	
	// Remove unused page attachments
	$condition = "LEFT JOIN $db_pages ON $db_attach.att_item = $db_pages.page_id
	WHERE $db_attach.att_type = 'pag' AND $db_pages.page_id IS NULL";
	
	$res = $db->query("SELECT att_id FROM $db_attach $condition");
	$count += $res->rowCount();
	while($att = $res->fetch())
		att_remove($att['att_id']);
	$res = null;
	
	$res = $db->query("DELETE FROM $db_attach USING $db_attach $condition");
	
	$plugin_body = "$count Items removed";
}
elseif($act == 'showinfo')
{
	$id = cot_import('id', 'P', 'INT');
	$plugin_body .= <<<END
	<h4>{$L['att_info']}:</h4>
	<form action="admin.php?m=tools&p=attach&act=showinfo" method="post">
	<strong>ID: </strong><input type="text" name="id" value="$id" /> <input type="submit" value="OK" />
	</form>
END;
	if($id > 0)
	{
		$sql = $db->query("SELECT a.*, u.user_id, u.user_name FROM $db_attach AS a LEFT JOIN $db_users AS u ON u.user_id = a.att_user WHERE a.att_id = $id");
		if($att = $sql->fetch())
		{
			$plugin_body .= '<table class="cells"><tr>';
			if($att['att_type'] == 'pag') $att_item = '<a href="'.cot_url('page', 'id='.$att['att_item']).'"><img src="images/admin/jumpto.gif" alt="" /></a>';
			else $att_item = '<a href="'.cot_url('forums', 'm=posts&p='.$att['att_item'], '#'.$att['att_item']).'"><img src="images/admin/jumpto.gif" alt="" /></a>';
			$plugin_body .= '<td><a href="'.cot_url('plug', 'e=attach&id='.$att['att_id']).'" target="_blank">'.$att['att_title'].'</a></td>';
			$plugin_body .= '<td><img src="'.(file_exists("images/pfs/{$att['att_ext']}.gif") ? "images/pfs/{$att['att_ext']}.gif" : 'images/pfs/zip.gif').'" alt="" /> '.strtoupper($att['att_ext']).'</td>';
			$plugin_body .= '<td>'.round($att['att_size'] / 1024, 1).' '.$L['att_kb'].'</td>';
			$plugin_body .= '<td>'.cot_build_user($att['att_user'], $att['user_name']).'</td>';
			$plugin_body .= '<td>'.$att['att_count'].'</td>';
			$plugin_body .= '<td>'.$att_item.'</td>';
			$plugin_body .= '</tr></table>';
		}
	}
}
else
{
	$plugin_body = <<<END
<ul>
<li><a href="admin.php?m=tools&p=attach&act=cleanup" onclick="return confirm('{$L['att_cleanup_confirm']}')">{$L['att_cleanup']}</a></li>
<li><a href="admin.php?m=tools&p=attach&act=showinfo">{$L['att_show_info']}</a></li>
</ul>
END;
}
?>