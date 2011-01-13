<?php
/**
 * Attachments API
 *
 * @package Attachments
 * @author Trustmaster
 * @copyright Copyright (c) 2008-2009, Vladimir Sibirov. All rights reserved. Distributed under BSD License.
 */

$db_attach = 'sed_attach';
file_exists($cfg['plugins_dir']."/attach/lang/attach.$lang.lang.php") ? require_once($cfg['plugins_dir']."/attach/lang/attach.$lang.lang.php") : require_once($cfg['plugins_dir'].'/attach/lang/attach.en.lang.php');

/**
 * Installs plugin database.
 *
 * @return string Database error message
 */
function att_install()
{
	global $db_attach, $L;
	$sql = <<<END
	CREATE TABLE IF NOT EXISTS $db_attach (
		att_id INT NOT NULL AUTO_INCREMENT,
		att_user INT NOT NULL,
		att_type CHAR(3) NOT NULL,
		att_parent INT NOT NULL,
		att_item INT NOT NULL,
		att_path VARCHAR(255) NOT NULL,
		att_ext VARCHAR(16) NOT NULL,
		att_img TINYINT NOT NULL DEFAULT 0,
		att_size INT NOT NULL,
		att_title VARCHAR(255) NOT NULL,
		att_count INT NOT NULL DEFAULT 0,
		PRIMARY KEY(att_id)
	)
END;
	//sed_sql_query("DROP TABLE IF EXISTS $db_attach");
	sed_sql_query($sql);
	return sed_sql_errno() > 0 ? $L['att_err_db'].': '.sed_sql_error() : $L['att_installed'];
}

/**
 * Performs basic checks to allow a desired file for upload or not. Returns error message or empty string.
 *
 * @param string $var_name Name of file upload field
 * @return string
 */
function att_upload_err($var_name, $ext)
{
	global $cfg, $usr, $db_attach, $L;
	if($_FILES[$var_name]['size'] > 0 && is_uploaded_file($_FILES[$var_name]['tmp_name']))
	{
		$valid_exts = explode(',', $cfg['plugin']['attach']['exts']);
		if(!empty($ext) && in_array($ext, $valid_exts))
		{
			$limits = att_get_limits();
			if($_FILES[$var_name]['size'] <= $limits['file'] * 1024)
			{
				if($_FILES[$var_name]['size'] <= $limits['left'] * 1024)
				{
					$msg = ''; // No error
				}
				else $msg = 'nospace';
			}
			else $msg = 'toobig';
		}
		else $msg = 'type';
	}
	else $msg = 'upload';
	return $msg;
}

/**
 * Adds a new attachment to the database and disk.
 *
 * @param string $type Target plugin code. Examples: 'frm' for forums, 'pag' for pages
 * @param int $item_id Target item id
 * @param int $parent_id Parent for the item
 * @param string $var_name Upload field name
 * @param string $title Attachment caption
 * @return string
 */
function att_add($type, $item_id, $parent_id, $var_name, $title)
{
	global $cfg, $usr, $db_attach;
	if(!sed_auth('plug', 'attach', 'W')) return $L['att_err_perms'];
	$extp = mb_strrpos($_FILES[$var_name]['name'], '.') + 1;
	$ext = strtolower(mb_substr($_FILES[$var_name]['name'], $extp, mb_strlen($_FILES[$var_name]['name']) - $extp));
	if(!($err = att_upload_err($var_name, $ext)))
	{
		// This is done in 2 steps, otherwise we may run into race condition
		$title = att_filter($title);
		$img = (int) in_array($ext, array('gif', 'jpg', 'jpeg', 'png'));
		sed_sql_query("INSERT INTO $db_attach (att_user, att_type, att_parent, att_item, att_path, att_ext, att_img, att_size, att_title, att_count)
		VALUES ({$usr['id']}, '$type', $parent_id, $item_id, '', '$ext', $img, {$_FILES[$var_name]['size']}, '$title', 0)");
		if(sed_sql_affectedrows() == 1)
		{
			$id = sed_sql_insertid();
			$tar = preg_match('#\.tar\.(gz|bz2)#i', $_FILES[$var_name]['name']) ? '.tar' : '';
			if($cfg['plugin']['attach']['userdir'])
			{
				$path = $cfg['plugin']['attach']['folder'].'/'.$usr['id'];
				if(!file_exists($path)) mkdir($path);
				$path .= '/'.$cfg['plugin']['attach']['prefix'].$id.$tar.'.'.$ext;
			}
			else $path = $cfg['plugin']['attach']['folder'].'/'.$cfg['plugin']['attach']['prefix'].$id.$tar.'.'.$ext;
			move_uploaded_file($_FILES[$var_name]['tmp_name'], $path);
			sed_sql_query("UPDATE $db_attach SET att_path = '$path' WHERE att_id = $id");
			if($img)
			{
				if(!att_create_thumb($path))
				{
					// XSS protect
					sed_sql_query("DELETE FROM $db_attach WHERE att_id = $id");
					@unlink($path);
					$err = 'thumb';
				}
			}
		}
		else $err = 'db';
	}
	else return $err;
}


/**
 * Removes an attachment by identifier.
 *
 * @param int $id Attachment ID
 * @return bool
 */
function att_remove($id)
{
	global $cfg, $usr, $db_attach;
	$res = true;
	$id = (int) $id;
	$sql = sed_sql_query("SELECT att_path, att_user, att_ext FROM $db_attach WHERE att_id = $id");
	if($row = sed_sql_fetcharray($sql))
	{
		if($row['att_user'] != $usr['id'] && !sed_auth('plug', 'attach', 'A')) return false;
		$res &= @unlink($row['att_path']);
		$res &= att_remove_thumb($row['att_path']);
		$sql = sed_sql_query("DELETE FROM $db_attach WHERE att_id = $id");
		$res &= sed_sql_affectedrows() == 1;
	}
	return $res;
}


/**
 * Remove all attachments matching the criteria. Returns number of entries affected.
 *
 * @param int $usr_id Target user identifier
 * @param string $type Target plugin type
 * @param int $item_id Target item identifier
 * @param int $parent_id Parent item identifier
 * @return int
 */
function att_remove_all($user_id = null, $type = null, $item_id = null, $parent_id = null)
{
	global $cfg, $db_attach;
	$count = 0;
	$bits = array('att_user' => $usr_id, 'att_type' => $type, 'att_item' => $item_id, 'att_parent' => $parent_id);
	$where = '';
	foreach($bits as $key => $bit)
	{
		if(!is_null($bit))
		{
			if(!empty($where)) $where .= ' AND ';
			$where .= "$key = '$bit'";
		}
	}
	if(empty($where)) $where = '1';
	$sql = sed_sql_query("SELECT att_path FROM $db_attach WHERE $where");
	$count = sed_sql_numrows($sql);
	while($row = sed_sql_fetcharray($sql))
	{
		@unlink($row['att_path']);
		att_remove_thumb($row['att_path']);
	}
	sed_sql_query("DELETE FROM $db_attach WHERE $where");
	return $count;
}


/**
 * Updates an existing attachment. Returns error message or empty string
 *
 * @param int $id Attachment ID
 * @param string $var_name Upload field name
 * @param string $title Attachment caption
 * @return string
 */
function att_update($id, $var_name, $title = '')
{
	global $cfg, $usr, $db_attach;
	$title = att_filter($title);
	$extp = mb_strrpos($_FILES[$var_name]['name'], '.') + 1;
	$ext = strtolower(mb_substr($_FILES[$var_name]['name'], $extp, mb_strlen($_FILES[$var_name]['name']) - $extp));
	if(!($err = att_upload_err($var_name, $ext)))
	{
		$sql = sed_sql_query("SELECT att_path, att_user FROM $db_attach WHERE att_id = $id");
		$row = sed_sql_fetcharray($sql);
		if($row['att_user'] != $usr['id'] && !sed_auth('plug', 'attach', 'A')) return $L['att_err_perms'];
		$path = $row['att_path'];
		@unlink($path);
		att_remove_thumb($path);
		$tar = preg_match('#\.tar\.(gz|bz2)#i', $_FILES[$var_name]['name']) ? '.tar' : '';
		if($cfg['plugin']['attach']['userdir'])
		{
			$path = $cfg['plugin']['attach']['folder'].'/'.$row['att_user'];
			if(!file_exists($path)) mkdir($path);
			$path .= '/'.$cfg['plugin']['attach']['prefix'].$id.$tar.'.'.$ext;
		}
		else $path = $cfg['plugin']['attach']['folder'].'/'.$cfg['plugin']['attach']['prefix'].$id.$tar.'.'.$ext;
		move_uploaded_file($_FILES[$var_name]['tmp_name'], $path);
		$size = filesize($path);
		$img = (int) in_array($ext, array('gif', 'jpg', 'jpeg', 'png'));
		$ttl = (empty($title)) ? '' : ", att_title = '$title'";
		sed_sql_query("UPDATE $db_attach SET att_ext = '$ext', att_img = $img, att_size = $size, att_path = '$path'$ttl WHERE att_id = $id");
		if(sed_sql_errno() > 0)
			$err = $L['att_err_db'].': '.sed_sql_error();
		if($img)
		{
			if(!att_create_thumb($path)) $err = 'thumb';
		}
	}
	elseif(!empty($title))
	{
		sed_sql_query("UPDATE $db_attach SET att_title = '$title' WHERE att_id = $id");
		if(sed_sql_affectedrows() != 1)
			$err = 'db';
	}
	return $err;
}


/**
 * Updates file caption only.
 *
 * @param int $id Attachment ID
 * @param string $title Caption
 * @return string
 */
function att_update_title($id, $title)
{
	global $db_attach, $usr;
	$title = att_filter($title);
	$sql = sed_sql_query("SELECT att_title, att_user FROM $db_attach WHERE att_id = $id");
	$row = sed_sql_fetcharray($sql);
	if($row['att_user'] != $usr['id'] && !sed_auth('plug', 'attach', 'A')) return $L['att_err_perms'];
	if($row['att_title'] == $title) return '';
	if(!empty($title))
	{
		sed_sql_query("UPDATE $db_attach SET att_title = '$title' WHERE att_id = $id");
		if(sed_sql_errno() > 0)
			return $L['att_err_db'].': '.sed_sql_error();
		return '';
	}
	else return $L['att_err_title'];
}

// A slightly modified sed_createthumb to track invalid images
function att_createthumb($img_big, $img_small, $small_x, $small_y, $keepratio, $extension, $filen, $fsize, $textcolor, $textsize, $bgcolor, $bordersize, $jpegquality, $dim_priority="Width")
{
	if (!function_exists('gd_info'))
	{ return false; }
	global $cfg;
	$gd_supported = array('jpg', 'jpeg', 'png', 'gif');
	switch($extension)
	{
		case 'gif':
			$source = imagecreatefromgif($img_big);
			break;
		case 'png':
			$source = imagecreatefrompng($img_big);
			break;
		default:
			$source = imagecreatefromjpeg($img_big);
			break;
	}	
	if(!$source) return false;
	
	$big_x = imagesx($source);
	$big_y = imagesy($source);
	if (!$keepratio)
	{
		$thumb_x = $small_x;
		$thumb_y = $small_y;
	}
	elseif ($dim_priority=="Width")
	{
		$thumb_x = $small_x;
		$thumb_y = floor($big_y * ($small_x / $big_x));
	}
	else
	{
		$thumb_x = floor($big_x * ($small_y / $big_y));
		$thumb_y = $small_y;
	}
	if ($textsize==0)
	{
		if ($cfg['th_amode']=='GD1')
		{ $new = imagecreate($thumb_x+$bordersize*2, $thumb_y+$bordersize*2); }
		else
		{ $new = imagecreatetruecolor($thumb_x+$bordersize*2, $thumb_y+$bordersize*2); }
		$background_color = imagecolorallocate ($new, $bgcolor[0], $bgcolor[1] ,$bgcolor[2]);
		imagefilledrectangle ($new, 0,0, $thumb_x+$bordersize*2, $thumb_y+$bordersize*2, $background_color);
		if ($cfg['th_amode']=='GD1')
		{ imagecopyresized($new, $source, $bordersize, $bordersize, 0, 0, $thumb_x, $thumb_y, $big_x, $big_y); }
		else
		{ imagecopyresampled($new, $source, $bordersize, $bordersize, 0, 0, $thumb_x, $thumb_y, $big_x, $big_y); }
	}
	else
	{
		if ($cfg['th_amode']=='GD1')
		{ $new = imagecreate($thumb_x+$bordersize*2, $thumb_y+$bordersize*2+$textsize*3.5+6); }
		else
		{ $new = imagecreatetruecolor($thumb_x+$bordersize*2, $thumb_y+$bordersize*2+$textsize*3.5+6); }
		$background_color = imagecolorallocate($new, $bgcolor[0], $bgcolor[1] ,$bgcolor[2]);
		imagefilledrectangle ($new, 0,0, $thumb_x+$bordersize*2, $thumb_y+$bordersize*2+$textsize*4+14, $background_color);
		$text_color = imagecolorallocate($new, $textcolor[0],$textcolor[1],$textcolor[2]);
		if ($cfg['th_amode']=='GD1')
		{ imagecopyresized($new, $source, $bordersize, $bordersize, 0, 0, $thumb_x, $thumb_y, $big_x, $big_y); }
		else
		{ imagecopyresampled($new, $source, $bordersize, $bordersize, 0, 0, $thumb_x, $thumb_y, $big_x, $big_y); }
		imagestring ($new, $textsize, $bordersize, $thumb_y+$bordersize+$textsize+1, $big_x."x".$big_y." ".$fsize."kb", $text_color);
	}
	switch($extension)
	{
		case 'gif':
			imagegif($new, $img_small);
			break;
		case 'png':
			imagepng($new, $img_small);
			break;
		default:
			imagejpeg($new, $img_small, $jpegquality);
			break;
	}
	imagedestroy($new);
	imagedestroy($source);
	return true;
}

/**
 * Creates a thumbnail for image.
 *
 * @param string $path Image path
 * @return string
 */
function att_create_thumb($path)
{
	global $cfg;
	$extp = mb_strrpos($path, '.');
	$len = mb_strlen($path);
	$ext = mb_strtolower(mb_substr($path, $extp + 1, $len - $extp - 1));
	$fname = mb_substr($path, mb_strrpos($path, '/') + 1, $len - mb_strrpos($path, '/') - $extp + 1);
	$thumb_path = mb_substr($path, 0, $extp).'.thumb.'.$ext;
	@unlink($thumb_path);
	$th_colortext = array(hexdec(substr($cfg['th_colortext'],0,2)), hexdec(substr($cfg['th_colortext'],2,2)), hexdec(substr($cfg['th_colortext'],4,2)));
	$th_colorbg = array(hexdec(substr($cfg['th_colorbg'],0,2)), hexdec(substr($cfg['th_colorbg'],2,2)), hexdec(substr($cfg['th_colorbg'],4,2)));
	if(!att_createthumb($path, $thumb_path, $cfg['plugin']['attach']['thumb_x'], $cfg['plugin']['attach']['thumb_y'], true, $ext, $fname, round(filesize($path)/1024), $th_colortext, $cfg['th_textsize'], $th_colorbg, $cfg['th_border'], $cfg['th_jpeg_quality'], $cfg['th_dimpriority']))
		return false;
	return $thumb_path;
}

/**
 * Creates a preview image.
 *
 * @param string $path Image path
 * @return string
 */
function att_create_preview($path)
{
	global $cfg;
	$extp = mb_strrpos($path, '.');
	$len = mb_strlen($path);
	$ext = mb_strtolower(mb_substr($path, $extp + 1, $len - $extp - 1));
	$fname = mb_substr($path, mb_strrpos($path, '/') + 1, $len - mb_strrpos($path, '/') - $extp + 1);
	$thumb_path = mb_substr($path, 0, $extp).'.prev.'.$ext;
	@unlink($thumb_path);
	$th_colortext = array(hexdec(substr($cfg['th_colortext'],0,2)), hexdec(substr($cfg['th_colortext'],2,2)), hexdec(substr($cfg['th_colortext'],4,2)));
	$th_colorbg = array(hexdec(substr($cfg['th_colorbg'],0,2)), hexdec(substr($cfg['th_colorbg'],2,2)), hexdec(substr($cfg['th_colorbg'],4,2)));
	if(!att_createthumb($path, $thumb_path, $cfg['plugin']['attach']['prev_x'], $cfg['plugin']['attach']['prev_y'], true, $ext, $fname, round(filesize($path)/1024), $th_colortext, 0, $th_colorbg, $cfg['plugin']['attach']['prev_border'], $cfg['th_jpeg_quality'], $cfg['th_dimpriority']))
		return false;
	return $thumb_path;
}


/**
 * Removes an image thumbnail.
 *
 * @param string $path Original image path.
 * @return bool
 */
function att_remove_thumb($path)
{
	if($thumb_path = att_get_thumb($path)) return @unlink($thumb_path);
	else return true;
}


/**
 * Gets thumbnail path from original image path.
 *
 * @param string $path Image path
 * @return string
 */
function att_get_thumb($path)
{
	$extp = mb_strrpos($path, '.');
	$len = mb_strlen($path);
	$ext = mb_strtolower(mb_substr($path, $extp + 1, $len - $extp - 1));
	if(in_array($ext, array('gif', 'jpeg', 'jpg', 'png')))
		return mb_substr($path, 0, $extp).'.thumb.'.$ext;
	else
		return '';
}

/**
 * Gets preview image path from original image path.
 *
 * @param string $path Image path
 * @return string
 */
function att_get_preview($path)
{
	$extp = mb_strrpos($path, '.');
	$len = mb_strlen($path);
	$ext = mb_strtolower(mb_substr($path, $extp + 1, $len - $extp - 1));
	if(in_array($ext, array('gif', 'jpeg', 'jpg', 'png')))
		return mb_substr($path, 0, $extp).'.prev.'.$ext;
	else
		return '';
}



/**
 * Gets upload space limits.
 *
 * @return array
 */
function att_get_limits()
{
	global $db_attach, $db_groups, $usr;
	$att_sql = sed_sql_query("SELECT grp_pfs_maxfile, grp_pfs_maxtotal FROM $db_groups WHERE grp_id = '{$usr['maingrp']}'");
	$row = sed_sql_fetcharray($att_sql);
	$res['file'] = $row['grp_pfs_maxfile'];
	$res['total'] = $row['grp_pfs_maxtotal'];
	$res['used'] = round(sed_sql_result(sed_sql_query("SELECT SUM(att_size) FROM $db_attach WHERE att_user = {$usr['id']}"), 0, 0) / 1024);
	$res['left'] = $res['total'] - $res['used'];
	return $res;
}


/**
 * Increment a hit counter.
 *
 * @param int $id Attachment ID
 * @return bool
 */
function att_inc_count($id)
{
	global $db_attach;
	$sql = sed_sql_query("UPDATE $db_attach SET att_count = att_count + 1 WHERE att_id = $id");
	$err = sed_sql_error();
	return empty($err);
}


/**
 * String security filter.
 *
 * @param string $str Imported data
 * @return string
 */
function att_filter($str)
{
	// ASCII XSS fix
	$str = preg_replace('/&#[0-9a-fA-F]+/', '', $str);
	// Standard CC
	$str = sed_cc($str);
	// SQL prep
	$str = sed_sql_prep($str);
	return $str;
}
?>