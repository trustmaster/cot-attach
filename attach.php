<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_SED_EXTPLUGIN]
Code=attach
Part=popup
File=attach
Hooks=popup
Tags=
Order=
[END_SED_EXTPLUGIN]
==================== */
if (!defined('SED_CODE')) { die('Wrong URL.'); }

if(sed_auth('plug', 'attach', 'R'))
{
	require_once($cfg['plugins_dir'].'/attach/inc/functions.php');

	$q = sed_import('q', 'G', 'INT');
	$id = sed_import('id', 'G', 'INT');
	$act = sed_import('act', 'G', 'ALP');
	$uid = sed_import('uid', 'G', 'INT');
	$aid = sed_import('aid', 'G', 'ALP');

	$adm = sed_auth('plug', 'attach', 'A');

	if($id > 0)
	{
		$sql = sed_sql_query("SELECT att_path FROM $db_attach WHERE att_id = $id");
		if(sed_sql_numrows($sql) == 1)
		{
			// Getting the server-relative path
			$site_uri = dirname($_SERVER['SCRIPT_NAME']);
			$site_uri = str_replace('\\', '/', $site_uri);
			if($site_uri[strlen($site_uri) - 1] != '/') $site_uri .= '/';
			// Absolute site url
			$abs_url = ($site_uri[0] == '/') ? 'http://'.$_SERVER['HTTP_HOST'].$site_uri : 'http://'.$_SERVER['HTTP_HOST'].'/'.$site_uri;
			$att = sed_sql_fetcharray($sql);
			att_inc_count($id);
			header('Location: '.$abs_url.$att['att_path']);
		}
	}
	elseif($q > 0 || $uid > 0)
	{

		//$mskin = file_exists("skins/$skin/plugin.standalone.attach.tpl") ? "skins/$skin/plugin.standalone.attach.tpl" : $cfg['plugins_dir'].'/attach/tpl/attach.tpl' ;
		$t1 = new XTemplate(sed_skinfile('attach', true));

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
				$urr = @sed_sql_result(sed_sql_query("SELECT att_user FROM $db_attach WHERE att_id = $aid"), 0, 0);
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
		elseif($uid > 0 && ($uid == $usr['id'] || sed_auth('plug', 'attach', 'A')))
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

		$sql = sed_sql_query("SELECT a.*, u.user_name
			FROM $db_attach AS a LEFT JOIN $db_users AS u ON u.user_id = a.att_user
			WHERE $where
			ORDER BY$att_order att_id ASC");
		if(sed_sql_numrows($sql) > 0)
		{
			while($att = sed_sql_fetcharray($sql))
			{
				if($att['att_type'] == 'pag')
					$att_item = '<a href="'.sed_url('page', 'id='.$att['att_item']).'"><img src="images/admin/jumpto.gif" alt="" /></a>';
				else
					$att_item = '<a href="'.sed_url('forums', 'm=posts&p='.$att['att_item'], '#'.$att['att_item']).'"><img src="images/admin/jumpto.gif" alt="" /></a>';
				$t1->assign(array(
					'ATTACH_ROW_CAPTION' => '<a href="'.sed_url('plug', 'o=attach&id='.$att['att_id']).'" target="_blank">'.$att['att_title'].'</a>',
					'ATTACH_ROW_TYPE' => '<img src="'.(file_exists("images/pfs/{$att['att_ext']}.gif") ? "images/pfs/{$att['att_ext']}.gif" : 'images/pfs/zip.gif').'" alt="" /> '.strtoupper($att['att_ext']),
					'ATTACH_ROW_SIZE' => round($att['att_size'] / 1024, 1).' '.$L['att_kb'],
					'ATTACH_ROW_USER' => sed_build_user($att['att_user'], $att['user_name']),
					'ATTACH_ROW_COUNT' => $att['att_count'],
					'ATTACH_ROW_ITEM' => $att_item,
					'ATTACH_ROW_DELETE' => ($adm || $att['att_user'] == $usr['id']) ? '<a href="'.sed_url('plug', 'o=attach&act=remove&q='.$q.'&uid='.$uid.'&aid='.$att['att_id']).'">'.$L['att_delete'].'</a>' : ''
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

		if(sed_auth('plug', 'attach', 'A') && $q > 0)
		{
			$t1->assign('ADMIN_REMOVE', '<a href="'.sed_url('plug', 'o=attach&q='.$q.'&act=remove').'" onclick="return confirm(\''.$L['att_ensure'].'\')">'.$L['att_remove_all'].'</a>');
			$t1->parse('MAIN.ADMIN');
		}

		$t1->parse('MAIN');

		$popup_body = $t1->text('MAIN');
	}
}
?>