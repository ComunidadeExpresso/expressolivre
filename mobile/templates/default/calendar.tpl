<!-- BEGIN page -->
<form id="form_calendar_type" action="index.php" method="get">
	<input type="hidden" name="menuaction" value="mobile.ui_mobilecalendar.index" />
	<div class="menu-contexto" style="height:25px !important;">
		<div style="float:left; position:absolute;">
			<select name="type" onchange="document.getElementById('form_calendar_type').submit()">
				{type_option_box}
			</select>
		</div>
						 
		<span style="float:right; position:relative;" class="titulo-secao">
			{lang_calendar}
		</span>
	</div>
</form>
<form method="post" action="index.php?menuaction=mobile.ui_home.search" id="form_search">
	<input type="hidden" name="calendar_search" value="1">
	{search}
</form>

<dl id="lista_miolo">	
	{events_box}	
</dl>
<div class="menu-contexto centraliza" style="display:{show_more};">
	<button name="more_events" title="{lang_more_events}" class="btn-contexto" onClick="location.href='index.php?menuaction=mobile.ui_mobilecalendar.index&type={type}&dia={dia}&mes={mes}&ano={ano}&results={next_max_results}'">{lang_more} 10 {lang_events}</button>
</div>
{calendar_box}

<!-- END page -->

<!-- BEGIN day_event_block -->
<div class="dt-titulo">
	<div class="posiciona-esquerda aumenta-tamanho ">
		<p class="email-item">{day}</p>
	</div>
</div>
<!-- END day_event_block -->
<!-- BEGIN event_block -->
<div class="{dd_class}">
	<div class="posiciona-esquerda aumenta-tamanho ">
		<p class="email-item">{startdate_data} às {enddate_data} - {title_data}</p>
		<p><strong><label>{location_field}:</label></strong> {location_data}</p>
		<p><strong><label>{description_field}:</label></strong> {description_data}</p>
	</div>
</div>
<!-- END event_block -->
<!-- BEGIN no_event_block -->
<div class="dt_branco">
	<div class="centraliza aumenta-tamanho">
		<p class="email-item">{msg_no_event}</p>
	</div>
</div>
<!-- END no_event_block -->
<!-- BEGIN type_option_block -->
<option value="{value}" {selected}>{label}</option>
<!-- END type_option_block -->

<!-- BEGIN bar_block -->
<dt class="menu-diverso">
  <div class="margin-geral centraliza">
  	<a href="{today_link}" class="btn_off">{lang_today}</a>
  	<a href="{before_link}" class="btn_off"><</a>
  	<a href="{current_link}" class="btn_off">{current_label}</a> 
   	<a href="{next_link}" class="btn_off">></a>
	</div>
</dt>
<!-- END bar_block -->

<!-- BEGIN calendar_bar_block -->
<dt class="menu-diverso">
  <div class="margin-geral centraliza">
  	<a href="{before_month_link}" class="btn_off"><</a>
  	<a href="{current_month_link}" class="btn_off">{current_month_label}</a> 
   	<a href="{next_month_link}" class="btn_off">></a> 
	</div>
</dt>
<!-- END calendar_bar_block -->

<!-- BEGIN calendar_header_begin_block -->
<div id="pre-calendario">
  <table id="calendario" border="0" align="center" cellpadding="0" cellspacing="0" >
  	<thead>
		  <tr>
		    {week_day_box}
		  </tr>
	  </thead>
	  </tbody>
<!-- END calendar_header_begin_block -->	

<!-- BEGIN calendar_header_block -->
<th>{week_day}</th>
<!-- END calendar_header_block -->	

<!-- BEGIN calendar_header_end_block -->
		</tbody>
	</table>
</div>
<!-- END calendar_header_end_block -->	

<!-- BEGIN calendar_day_begin_block -->
<tr class="dias">
<!-- END calendar_day_begin_block -->
<!-- BEGIN calendar_day_end_block -->
</tr>
<!-- END calendar_day_end_block -->
<!-- BEGIN calendar_day_block -->
<td align="center" valign="middle"><a class="dias {extra_class}" href="{calendar_day_link}">{calendar_day}<span class="qtd_commitment">{qtd_commitment}</span></a></td>
<!-- END calendar_day_block -->