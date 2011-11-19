<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=standalone
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

if(cot_auth('plug', 'attach', 'R'))
{
	require_once cot_incfile('attach', 'plug');

	$q = cot_import('q', 'G', 'INT');
	$id = cot_import('id', 'G', 'INT');
	$act = cot_import('act', 'G', 'ALP');
	$uid = cot_import('uid', 'G', 'INT');
	$aid = cot_import('aid', 'G', 'ALP');

	$adm = cot_auth('plug', 'attach', 'A');

	if($id > 0)
	{
		$sql = $db->query("SELECT att_path FROM $db_attach WHERE att_id = $id");
		if($sql->rowCount() == 1)
		{
			// Getting the server-relative path
			$site_uri = dirname($_SERVER['SCRIPT_NAME']);
			$site_uri = str_replace('\\', '/', $site_uri);
			if($site_uri[strlen($site_uri) - 1] != '/') $site_uri .= '/';
			// Absolute site url
			$abs_url = ($site_uri[0] == '/') ? 'http://'.$_SERVER['HTTP_HOST'].$site_uri : 'http://'.$_SERVER['HTTP_HOST'].'/'.$site_uri;
			$att = $sql->fetch();
			att_inc_count($id);
			header('Location: '.$abs_url.$att['att_path']);
		}
	}
	elseif($q > 0 || $uid > 0)
	{

		//$mskin = file_exists("skins/$skin/plugin.standalone.attach.tpl") ? "skins/$skin/plugin.standalone.attach.tpl" : $cfg['plugins_dir'].'/attach/tpl/attach.tpl' ;
		$t1 = new XTemplate(cot_tplfile('attach', 'plug'));

		if($act == 'remove' && $adm && $aid <= 0)
		{
			// Admin "remove all"
			$count = att_remove_all(null, 'frm', null, $q);
			$t1->assign('MESSAGE', $count.' '.$L['att_items_removed']);
			$t1->parse('MAIN.MESSAGE');
		}
		elseif($act == 'remove' && $adm && $aid > 0)
		{
			// Remove a concrete attachment
			$access = false;
			if($adm) $access = true;
			else
			{
				$urr = @$db->query("SELECT att_user FROM $db_attach WHERE att_id = $aid")->fetchColumn();
				if($urr == $usr['id']) $access = true;
			}
			if($access) $count = (int) att_remove($aid);
			$t1->assign('MESSAGE', $count.' '.$L['att_items_removed']);
			$t1->parse('MAIN.MESSAGE');
		}

		$t1->assign(array(
			'ATTACH_TOP_CAPTION' => $L['att_title'],
			'ATTACH_TOP_TYPE' => $L['att_type'],
			'ATTACH_TOP_SIZE' => $L['att_size'],
			'ATTACH_TOP_USER' => $L['att_user'],
			'ATTACH_TOP_COUNT' => $L['att_downloads'],
			'ATTACH_TOP_ITEM' => $L['att_item'],
		));

		$att_order = '';
		switch($cfg['plugin']['attach']['order'])
		{
			case 'images first':
				$att_order = ' att_img DESC,';
				break;
			case 'files first':
				$att_order = ' att_img ASC,';
				break;
			default:
				$att_order = '';
		}

		if($q > 0)
			$where = "att_type = 'frm' AND att_parent = $q";
		elseif($uid > 0 && ($uid == $usr['id'] || cot_auth('plug', 'attach', 'A')))
		{
			$where = "att_user = $uid";
			$limits = att_get_limits();
			$t1->assign(array(
				'USERSPACE_TOP_MAXFILE' => $L['att_maxsize'],
				'USERSPACE_TOP_FREE' => $L['att_free'],
				'USERSPACE_TOP_USED' => $L['att_used'],
				'USERSPACE_TOP_TOTAL' => $L['att_total'],
				'USERSPACE_TOP_KB' => $L['att_kb'],
				'USERSPACE_MAXFILE' => $limits['file'],
				'USERSPACE_FREE' => $limits['left'],
				'USERSPACE_USED' => $limits['used'],
				'USERSPACE_TOTAL' => $limits['total'],
			));
			$t1->parse('MAIN.USERSPACE');
		}
		else
			$where = '0';

		$sql = $db->query("SELECT a.*, u.user_name
			FROM $db_attach AS a LEFT JOIN $db_users AS u ON u.user_id = a.att_user
			WHERE $where
			ORDER BY$att_order att_id ASC");
		if($sql->rowCount() > 0)
		{
			while($att = $sql->fetch())
			{
				if($att['att_type'] == 'pag')
					$att_item = '<a href="'.cot_url('page', 'id='.$att['att_item']).'"><img src="images/icons/default/arrow-follow.png" alt="" /></a>';
				else
					$att_item = '<a href="'.cot_url('forums', 'm=posts&p='.$att['att_item'], '#'.$att['att_item']).'"><img src="images/icons/default/arrow-follow.png" alt="" /></a>';
				$t1->assign(array(
					'ATTACH_ROW_CAPTION' => '<a href="'.cot_url('plug', 'e=attach&id='.$att['att_id']).'" target="_blank">'.$att['att_title'].'</a>',
					'ATTACH_ROW_TYPE' => '<img src="'.(file_exists("images/filetypes/default/{$att['att_ext']}.png") ? "images/filetypes/default/{$att['att_ext']}.png" : 'images/filetypes/default/zip.gif').'" alt="" /> '.strtoupper($att['att_ext']),
					'ATTACH_ROW_SIZE' => round($att['att_size'] / 1024, 1).' '.$L['att_kb'],
					'ATTACH_ROW_USER' => cot_build_user($att['att_user'], $att['user_name']),
					'ATTACH_ROW_COUNT' => $att['att_count'],
					'ATTACH_ROW_ITEM' => $att_item,
					'ATTACH_ROW_DELETE' => ($adm || $att['att_user'] == $usr['id']) ? '<a href="'.cot_url('plug', 'e=attach&act=remove&q='.$q.'&uid='.$uid.'&aid='.$att['att_id']).'">'.$L['att_delete'].'</a>' : ''
				));
				$t1->parse('MAIN.ATTACH.ATTACH_ROW');
			}
		}
		else
		{
			$t1->assign('ERROR_MSG', $L['att_err_noitems']);
			$t1->parse('MAIN.ERROR');
		}

		$t1->parse('MAIN.ATTACH');

		if(cot_auth('plug', 'attach', 'A') && $q > 0)
		{
			$t1->assign('ADMIN_REMOVE', '<a href="'.cot_url('plug', 'e=attach&q='.$q.'&act=remove').'" onclick="return confirm(\''.$L['att_ensure'].'\')">'.$L['att_remove_all'].'</a>');
			$t1->parse('MAIN.ADMIN');
		}

		$t1->parse('MAIN');

		$popup_body = $t1->text('MAIN');
	}
}
?>