<!-- BEGIN: MAIN -->

<script type="text/javascript" src="{PHP.cfg.plugins_dir}/attach/js/attach.js"></script>

<div id="att_box" style="display:none">
<em>{PHP.L.att_your_space}: {ATTACH_LEFTSPACE} {PHP.L.att_of} {ATTACH_TOTALSPACE} {PHP.L.att_kb_left_of} {ATTACH_MAXFILESIZE} {PHP.L.att_kb}.</em><br />

<!-- BEGIN: ATTACH_ROW -->
<div id="{ATTACH_ROW_FILE}" style="display:none">
<input type="text" name="{ATTACH_ROW_CAPTION}" />
<input type="file" name="{ATTACH_ROW_FILE}" />
</div>
<!-- END: ATTACH_ROW -->

</div>
<a href="{ATTACH_PERSURL}" target="_blank"><img src="{PHP.cfg.plugins_dir}/attach/img/attach.gif" alt="" /></a><a href="javascript:addAttach()">{PHP.L.att_attach}</a><br />

<!-- END: MAIN -->