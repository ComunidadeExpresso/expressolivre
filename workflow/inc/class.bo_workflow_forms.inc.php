<?php
	require_once(dirname(__FILE__) . SEP . 'class.WorkflowUtils.inc.php');
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */	
	class bo_workflow_forms extends WorkflowUtils
	{
		/**
		 * @var int $nextrows nextmatchs (max number of rows per page) and associated vars
		 * @access public
		 */
		var $nextmatchs;
		/**
		 * @var int $start actual starting row number
		 * @access public
		 */
		var $start; 
		/**
		 * @var int $total_records total number of rows
		 * @access public
		 */
		var $total_records; 
		/**
		 * @var string $order column used for order 
		 * @access public
		 */
		var $order;
		/**
		 * @var string $sort ASC or DESC 
		 * @access public
		 */
		var $sort;
		/**
		 * @var int $sort_mode combination of order and sort 
		 * @access public
		 */
		var $sort_mode; 
		/**
		 * @var int $offset the number of authorized lines 
		 * @access public
		 */
		var $offset; 
		/**
		 * @var string $search_str searched string
		 * @access public
		 */
		var $search_str;
		/**
		 * @var array $link_data associative array of input and values used for get links and/or hidden fields
		 * @access public
		 */
		var $link_data = array();
		/**
		 * @var string $template_name name of the template associated with this form, used to set the form_action as well
		 * for example a template named monitor_processes must be linked with a class.ui_monitor_processes.inc.php
		 * @access public
		 */
		var $template_name;
		/**
		 * @var string $form_action form destination
		 * @access public
		 */
		var $form_action;
		/**
		 * @var string $class_name related to template_name, name of the child class
		 * @access public
		 */
		var $class_name;
		/**
		 * @var array $message message shown in red in top of forms
		 * @access public
		 */
		var $message=Array();
		
		/**
		 * Construtor 
		 * 
		 * @access public
		 * @return object
		 */
		function bo_workflow_forms($template_name)
		{
			parent::WorkflowUtils();
			
			//retrieve common form POST or GET values
			$this->start		= (int)get_var('start', 'any', 0);
			$this->order		= get_var('order','any','wf_procname');
			$this->sort		= get_var('sort','any','ASC');
			$this->sort_mode	= $this->order . '__' . $this->sort;
			$this->search_str	= get_var('find', 'any', '');
			$this->nextmatchs	= Factory::getInstance('nextmatchs');
			
			// number of rows allowed
			if ($GLOBALS['phpgw_info']['user']['preferences']['workflow']['ui_items_per_page'] > 0)
			{
				$this->offset = $GLOBALS['phpgw_info']['user']['preferences']['workflow']['ui_items_per_page'];
			}
			else
			{
				$this->offset = 15;
			}
			$this->nextmatchs->maxmatches = $this->offset;
			
			$this->template_name = $template_name;
			$this->class_name = explode('_', $this->template_name);
			$this->class_name = implode('', $this->class_name);
			$this->form_action = $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_'. $this->class_name .'.form');

			$title = explode('_', $this->template_name);
			$title[0] = ucfirst($title[0]);
			$title[1] = ucfirst($title[1]);
			$title = implode(' ', $title);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang($title);
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file($this->template_name, $this->template_name . '.tpl');
			
			//common css
 	  	    $this->t->set_var('processes_css', '<LINK href="'.$this->get_css_link('processes').'" type="text/css" rel="StyleSheet">');
 	  	 
 	  	 
		}
		/**
		 *  Fill the nextmatchs fields, arrows, and counter
		 * 
		 * @param array $header_array is an array with header_names => header_text_shown
		 * @param int $total_number total records
		 *  warning header names are header_[name or alias of the column in the query without a dot]
		 *  this is necessary for sorting
		 *  You need some fields on the template:
		 *          <table style="border: 0px;width:100%; margin:0 auto">
		 *          	<tr class="th" style="font-weight:bold">
		 *                 	{left}
		 * 		 * 	* 		 * 	<td><div align="center">{lang_showing}</div></td>
		 * 		 * 	* 		 * 	{right}
		 *          	</tr>
		 * 	</table>
		 * @return void
		 * @access public
		 */
		function fill_nextmatchs(&$header_array, $total_number)
		{
			$this->total_records = $total_number;
			// left and right nextmatchs arrows
			$this->t->set_var('left',$this->nextmatchs->left(
				$this->form_action,$this->start,$this->total_records,$this->link_data));
			$this->t->set_var('right',$this->nextmatchs->right(
				$this->form_action,$this->start,$this->total_records,$this->link_data));
			//show table headers with sort
			foreach($header_array as $col => $translation) 
			{
				$this->t->set_var('header_'.$col,$this->nextmatchs->show_sort_order(
					$this->sort,$col,$this->order,'/index.php',$translation,$this->link_data));
			}
			
			// info about number of rows
			if (($this->total_records) > $this->offset)	
			{
				$this->t->set_var('lang_showing',lang('showing %1 - %2 of %3',
					1+$this->start,
					(($this->start+$this->offset) > ($this->total_records))? $this->total_records : $this->start+$this->offset,
					$this->total_records));
			}
			else 
			{
				$this->t->set_var('lang_showing', lang('showing %1',$this->total_records));
			}
		}
		/**
		 * Fill general datas of workflow forms 
		 * 
		 * theses datas are:
		 * 	$message	   : one or more ui message
		 * 	$search_str	   : search string for research
		 * 	$start		   : nextmatch: number of the first row
		 * 	$sort		   : nextmatch: current sort header
		 * 	$order		   : nextmatch: asc or desc
		 * 	$form_action   : link to the monitor subclass
		 * 
		 * @return void
		 * @access public
		 */
		function fill_form_variables()
		{
			$this->t->set_var(array(
				'message'			=> implode('<br />', array_filter($this->message)),
				'start'				=> $this->start,
				'search_str'			=> $this->search_str,
				'sort'				=> $this->sort,
				'order'				=> $this->order,
				'form_action'			=> $this->form_action,
			));
		}
		/**
		 * Finish the form process by translating the template, outputing it and showing the footer
		 * @return void
		 * @access public
		 */
		function finish()
		{
			$this->translate_template($this->template_name);
			$this->t->pparse('output', $this->template_name);
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

	}
?>
