<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_COT_EXT]
Hooks=page.edit.delete.done
attach=
Order=10
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

if($cfg['plugin']['attach']['pages'] && cot_auth('plug', 'attach', 'W'))
{
	require_once cot_incfile('attach', 'plug');

	att_remove_all(null, 'pag', $id, null);
}
?>
