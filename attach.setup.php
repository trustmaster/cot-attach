<?php
/* ====================
Copyright (c) 2008-2009, Vladimir Sibirov.
All rights reserved. Distributed under BSD License.

[BEGIN_SED_EXTPLUGIN]
Code=attach
Name=File Attachments
Description=Attach files to posts and pages
Version=1.1.0
Date=2009-jan-30
Author=Trustmaster
Copyright=(c) Vladimir Sibirov, 2008-2009
Notes=DO NOT FORGET to add enctype="multipart/form-data" to forms in your TPLs
SQL=
Auth_guests=R1
Lock_guests=W2345A
Auth_members=RW1
Lock_members=2345
[END_SED_EXTPLUGIN]

[BEGIN_SED_EXTPLUGIN_CONFIG]
folder=01:string::datas/users:Directory for files
prefix=02:string::att_:File prefix
exts=03:string::gif,jpg,jpeg,png,zip,rar,7z,gz,bz2,pdf,djvu,mp3,ogg,wma,avi,divx,mpg,mpeg,swf,txt:Allowed extensions (comma separated, no dots and spaces)
thumbs=04:radio::1:Display images as thumbnails?
thumb_x=05:string::160:Max. thumbnail width
thumb_y=06:string::160:Max. thumbnail height
items=07:select:1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16:4:Attachments per post (max.)
order=08:select:mixed,images first,files first:mixed:Display order
pages=11:radio::1:Enable attachments in pages?
forums=12:radio::1:Enable attachments in forums?
listprev=13:radio::0:Enable preview image in list.php (showcase)?
userdir=14:radio::0:Enable files in user subdirectories?
prev_x=15:string::160:Showcase preview image width
prev_y=16:string::160:Showcase preview image height
prev_border=17:string::0:Showcase preview image border
[END_SED_EXTPLUGIN_CONFIG]
==================== */

if ( !defined('SED_CODE') ) { die("Wrong URL."); }

if($action == 'install')
{
	require_once($cfg['plugins_dir'].'/attach/inc/functions.php');

	att_install();
}

?>