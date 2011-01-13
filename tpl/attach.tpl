<!-- BEGIN: MAIN -->

<!-- BEGIN: USERSPACE -->
<div class="box">
{USERSPACE_FREE} {USERSPACE_TOP_KB} {USERSPACE_TOP_FREE},
{USERSPACE_USED} {USERSPACE_TOP_KB} {USERSPACE_TOP_USED}, {USERSPACE_TOTAL} {USERSPACE_TOP_KB} {USERSPACE_TOP_TOTAL},
{USERSPACE_MAXFILE} {USERSPACE_TOP_KB} {USERSPACE_TOP_MAXFILE}.
</div>
<!-- END: USERSPACE -->

<!-- BEGIN: ERROR -->
<div class="error">
{ERROR_MSG}
</div>
<!-- END: ERROR -->

<!-- BEGIN: MESSAGE -->
<div class="notice">
{MESSAGE}
</div>
<!-- END: MESSAGE -->

<!-- BEGIN: ATTACH -->
<table class="cells">
<tr>
<td class="coltop">{ATTACH_TOP_ITEM}</td>
<td class="coltop">{ATTACH_TOP_TYPE}</td>
<td class="coltop">{ATTACH_TOP_CAPTION}</td>
<td class="coltop">{ATTACH_TOP_USER}</td>
<td class="coltop">{ATTACH_TOP_SIZE}</td>
<td class="coltop">{ATTACH_TOP_COUNT}</td>
</tr>

<!-- BEGIN: ATTACH_ROW -->
<tr>
<td>{ATTACH_ROW_ITEM}</td>
<td>{ATTACH_ROW_TYPE}</td>
<td>{ATTACH_ROW_CAPTION}</td>
<td>{ATTACH_ROW_USER}</td>
<td>{ATTACH_ROW_SIZE}</td>
<td>{ATTACH_ROW_COUNT} {ATTACH_ROW_DELETE}</td>
</tr>
<!-- END: ATTACH_ROW -->

</table>
<!-- END: ATTACH -->

<!-- BEGIN: ADMIN -->
{ADMIN_REMOVE}
<!-- END: ADMIN -->

<!-- END: MAIN -->