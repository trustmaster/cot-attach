<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_SED_EXTPLUGIN]
Code=attach
Part=forums.posts.delete
File=attach.forums.posts.delete
Hooks=forums.posts.delete.first
Tags=
Order=10
[END_SED_EXTPLUGIN]
==================== */
if (!defined('SED_CODE')) { die('Wrong URL.'); }

if($cfg['plugin']['attach']['forums'] && sed_auth('plug', 'attach', 'W'))
{
	require_once($cfg['plugins_dir'].'/attach/inc/functions.php');

	att_remove_all(null, 'frm', $p, null);
}
?>