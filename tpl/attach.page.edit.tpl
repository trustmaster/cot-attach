<!-- BEGIN: MAIN -->

<script type="text/javascript" src="{PHP.cfg.plugins_dir}/attach/js/attach.js"></script>

<!-- BEGIN: ATTACH_ERROR -->
<div class="error">
{ATTACH_ERROR_MSG}
</div>
<!-- END: ATTACH_ERROR -->

<div id="att_box" style="display:;">
<em>{PHP.L.att_your_space}: {ATTACH_LEFTSPACE} {PHP.L.att_of} {ATTACH_TOTALSPACE} {PHP.L.att_kb_left_of} {ATTACH_MAXFILESIZE} {PHP.L.att_kb}.</em><br />

<!-- BEGIN: ATTACH_ROW -->
<div id="{ATTACH_ROW_ID}" style="{ATTACH_ROW_DISPLAY}">
<input type="text" name="{ATTACH_ROW_CAPTION}" value="{ATTACH_ROW_CAPTION_VALUE}" />
<input type="file" name="{ATTACH_ROW_FILE}" /> {ATTACH_ROW_REPLACE} {ATTACH_ROW_DELETE}
</div>
<!-- END: ATTACH_ROW -->

</div>
<a href="{ATTACH_PERSURL}" target="_blank"><img src="{PHP.cfg.plugins_dir}/attach/img/attach.gif" alt="" /></a> <a href="javascript:addAttach()">{PHP.L.att_attach}</a><br />

<!-- END: MAIN -->