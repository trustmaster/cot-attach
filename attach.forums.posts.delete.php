<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
File=attach.forums.posts.delete
Hooks=forums.posts.delete.first
Order=10
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

if($cfg['plugin']['attach']['forums'] && cot_auth('plug', 'attach', 'W'))
{
	require_once cot_incfile('attach', 'plug');

	att_remove_all(null, 'frm', $p, null);
}
?>