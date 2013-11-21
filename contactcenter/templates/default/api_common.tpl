<!-- ContactCenter API Start -->
<link rel="stylesheet" type="text/css" href="{cc_css}" />
<link rel="stylesheet" type="text/css" href="{cc_dtree_css}" />
<input id="cc_server_root" type="hidden" value="{cc_server_root}" />
<input id="cc_phpgw_img_dir" type="hidden" value="{cc_phpgw_img_dir}" />
<input id="cc_email_id_type" type="hidden" value="{cc_email_id_type}" />

<!-- DEBUG -->
<div id="cc_debug" style="display: none; position: absolute; top:0px; left: 1000px; width: 600px"></div>
<!-- END DEBUG -->

<!-- JS MESSAGES -->
<input id="cc_msg_err_invalid_catalog" type="hidden" value="{cc_msg_err_invalid_catalog}" />
<input id="cc_msg_err_contacting_server" type="hidden" value="{cc_msg_err_contacting_server}" />
<input id="cc_msg_err_timeout" type="hidden" value="{cc_msg_err_timeout}" />
<input id="cc_msg_err_serialize_data_unknown" type="hidden" value="{cc_msg_err_serialize_data_unknown}" />
<input id="cc_msg_err_shared" type="hidden" type="hidden" value="{cc_msg_err_shared}" />
<input id="cc_msg_err_duplicate_group" type="hidden" type="hidden" value="{cc_msg_err_duplicate_group}" />

<input id="cc_msg_err_empty_field" type="hidden" value="{cc_msg_err_empty_field}" />
<input id="cc_msg_type_state" type="hidden" value="{cc_msg_type_state}" />
<input id="cc_msg_type_city" type="hidden" value="{cc_msg_type_city}" />
<!-- END JS MESSAGES -->


<input id="cc_connector_visible" type="hidden" value="{cc_connector_visible}" />
<input id="cc_loading_1" type="hidden" value="{cc_loading_1}" />
<input id="cc_loading_2" type="hidden" value="{cc_loading_2}" />
<input id="cc_loading_3" type="hidden" value="{cc_loading_3}" />
<input id="cc_loading_image" type="hidden" value="{cc_loading_image}" />

<div id="cc_loading" class="hidden">
	<table class="loading" background="{cc_loading_image}"> 
		<tr>
			<td id="cc_loading_inner"></td>
		</tr>
	</table>
</div>
<script type="text/javascript" src="{cc_js_aux}"></script>
<script type="text/javascript" src="{cc_js_connector}"></script>
<script type="text/javascript" src="{cc_js_wz_dragdrop}"></script>
<script type="text/javascript" src="{cc_js_dtree}"></script>
<script type="text/javascript" src="{cc_js_dtabs}"></script>
<script type="text/javascript" src="{cc_js_djswin}"></script>
<script type="text/javascript" src="{cc_js_catalog_tree}"></script>
<script type="text/javascript">
	var Connector = new cConnector();
	Connector.setVisible(true);
</script>
<!-- ContactCenter API End -->
