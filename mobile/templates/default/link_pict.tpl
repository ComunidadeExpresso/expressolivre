
<!-- BEGIN link_pict -->
{picture}
<!-- END link_pict -->

<!-- BEGIN link_open -->
<div id="calendar_event_entry" style="overflow:hidden;">
<a class="event_entry" href="#" onMouseOver="window.status='{lang_view}'; return true;" onMouseOut="window.status=''; return true;" title="{desc} {location}"><br>
<!-- END link_open -->

<!-- BEGIN pict -->
 <img src="{pic_image}" width="{width}" height="{height}" title="{title}" border="0" />
<!-- END pict -->

<!-- BEGIN link_text_old -->
<nobr>&nbsp;{time}&nbsp;</nobr> {title}&nbsp;{users_status}: <i>{desc}</i><!--({location})-->
<!-- END link_text_old -->

<!-- BEGIN link_text -->
&nbsp;<FONT SIZE=1><span style="color: black">{time}</span> {users_status}<br><b>{title}</b><br><i>{desc}</i> {location}
<!-- END link_text -->

<!-- BEGIN link_close -->
</a></div>
<!-- END link_close -->
