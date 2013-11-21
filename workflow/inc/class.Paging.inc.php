<?php
/*
 * Created on 02/07/2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
*/
/**
 * Database query results paging class
 * @author Sidnei Augusto Drovetto Junior
 * @version 1.0
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class Paging
{
	/**
	 * @var int $itemsPerPage Number of page itens 
	 * @access public
	 */
	var $itemsPerPage;
	/**
	 * @var int $itemsCount Number of itens
	 * @access public
	 */
	var $itemsCount;
	/**
	 * @var int $numberOfPages Total pages
	 * @access public
	 */
	var $numberOfPages;
	/**
	 * @var int $currentPage  Actual page
	 * @access public
	 */
	var $currentPage;
	/**
	 * @var array $items itens array
	 * @access public
	 */
	var $items;
    /**
	 * @var string $baseLink  server Url base
	 * @access public
	 */
	var $baseLink;
	/**
	 * @var $newRequest
	 * @access public
	 */
	var $newRequest;
	/**
	 * @var int $nextItem number of next item
	 * @access public
	 */
	var $nextItem;
	/**
	 * @var int $originalRequest
	 * @access public
	 */
	var $originalRequest;

	/**
	 * @var string $SQLOrdenacao
	 * @access public
	 */
	var $SQLOrdenacao;
	/**
	 * Initialize class Paging atributes
	 * @acces public 
	 * 	@return void
 	 */
	function initialize()
	{
		$this->currentPage = 0;
		$this->baseLink = $_SERVER['SCRIPT_NAME'];
		$this->items = array();

		if (!is_null($this->originalRequest))
			$this->parseRequest($this->originalRequest);
	}
	/**
	 * Construtor 
	 * @access public
	 * @return object
 	 */
	function Paging($pItemsPerPage, $request = null)
	{
		$this->itemsPerPage = $pItemsPerPage;
		$this->originalRequest = $request;

		$this->initialize();
	}
	/**
	 * Parse Request
	 * @param string $request request
	 * @return void
	 */
	function parseRequest($request)
	{
		$this->currentPage = (isset($request['p_page'])) ? $request['p_page'] : 0;
		if (is_numeric($this->currentPage))
			$this->currentPage = (int) $this->currentPage;
		else
			$this->currentPage = 0;

		$this->newRequest = $this->_cleanRequest("p_page", $request);

		$this->nextItem = $this->currentPage * $this->itemsPerPage;
	}
	/**
	 * Restrict the number of itens 
	 * @var array $pItems array of page itens 
	 * @var int   $totalItems number of total items
	 * @return array page items
	 * @access public
	 */
	function restrictItems($pItems, $totalItems = null)
	{
		$start = $this->nextItem;
		if (is_null($totalItems))
		{
			$totalItems = count($pItems);
			$end = min($start + $this->itemsPerPage, $totalItems);
			$this->items = array();
			for ($i = $start; $i < $end; ++$i)
				$this->items[] = $pItems[$i];
		}
		else
			$this->items = $pItems;

		$this->itemsCount = $totalItems;
		return $this->items;
	}
    /**
	 * Return  pagination Result
	 * @return array 
	 * @access public
	 */
	function paginationResult()
	{
		$this->numberOfPages = ceil(((double) $this->itemsCount / (double) $this->itemsPerPage));
		$output = array();
		if ($this->numberOfPages < 2)
			return $output;

		$charSeparator = empty($this->newRequest) ? "" : "&amp;";

		for ($i = 0; $i < $this->numberOfPages; ++$i)
		{
			$page = $i;
			$start = ($i * $this->itemsPerPage) + 1;
			$end = min($start - 1 + $this->itemsPerPage, $this->itemsCount);
			$current = array(
				"link" => $this->baseLink . "?" . $this->newRequest . $charSeparator . "p_page=$page",
				"pageNumber" => $page + 1,
				"p_page" => $page,
				"start" => $start,
				"end" => $end);
			$output[] = $current;
		}

		return $output;
	}
    /**
	 * Limit pagination result
	 * @var int $numberofLinks number of links 
	 * @access public
	 * @return array
	 */
	function limitedPaginationResult($numberOfLinks)
	{
		$allPaginationResults = $this->paginationResult();
		$output = array();
		if (count($allPaginationResults) == 0)
			return $output;

		$firstPage = max(0, $this->currentPage - $numberOfLinks);
		$lastPage = min($this->currentPage + $numberOfLinks, $this->numberOfPages);
		for ($i = $firstPage; $i < $lastPage; ++$i)
			$output[] = $allPaginationResults[$i];

		return $output;
	}
    /**
	 * Pagination Result navigation 
	 * @access public
	 * @return array
	 */
	function paginationResultNavigation()
	{
		$allPaginationResults = $this->paginationResult();

		$output = array();
		$output['currentPage'] = $this->currentPage + 1;

		if (($this->currentPage > 0) && isset($allPaginationResults[$this->currentPage - 1]))
			$output['previous'] = $allPaginationResults[$this->currentPage - 1];

		if (($this->currentPage < ($this->numberOfPages - 1)) && isset($allPaginationResults[$this->currentPage + 1]))
			$output['next'] = $allPaginationResults[$this->currentPage + 1];

		return $output;
	}
    /**
	 * Common Links 
	 * @var int $numberOfLinks number of links
	 * @return array $links array of common links 
	 * @access public
	 */
	function commonLinks($numberOfLinks = 10)
	{
		$links = array();
		$paginationLinks = $this->limitedPaginationResult($numberOfLinks);
		$paginationNavigationLinks = $this->paginationResultNavigation();

		if (count($paginationLinks) == 0)
			return $links;
		if (isset($paginationNavigationLinks['previous']))
		{
			$tmp = $paginationNavigationLinks['previous'];
			$tmp['name'] = "anterior";
			$tmp['do_link'] = true;
			$links[] = $tmp;
		}
        $paginationLinks_count = count($paginationLinks);
		for ($i = 0; $i < $paginationLinks_count; ++$i)
		{
			$tmp = $paginationLinks[$i];
			$tmp['name'] = $tmp['pageNumber'];
			$tmp['do_link'] = ($paginationNavigationLinks['currentPage'] != $tmp['pageNumber']) ? true : false;
			$links[] = $tmp;
		}

		if (isset($paginationNavigationLinks['next']))
		{
			$tmp = $paginationNavigationLinks['next'];
			$tmp['name'] = "próximo";
			$tmp['do_link'] = true;
			$links[] = $tmp;
		}

		return $links;
	}
	
    /**
	 * Page Autolinks 
	 * @var int $numberOfLinks number of links
	 * @var string $linkFormat link format
	 * @var string $selectedFormat 
	 * @var string $separator separator
	 * @var int $totalItems number of total items 
	 * @access public
	 */
	function autoLinks($numberOfLinks = 10, $linkFormat = null, $selectedFormat = null, $separator = " ")
	{
		$output = "";
		$linkList = $this->commonLinks($numberOfLinks);

		if (is_null($linkFormat))
			$linkFormat = '<a href="%link%">%name%</a>';

		if (is_null($selectedFormat))
			$selectedFormat = '<strong>%name%</strong>';

		$correspondenceList = array(
			'%name%' => 'name',
			'%link%' => 'link',
			'%pageNumber%' => 'pageNumber',
			'%p_page%' => 'p_page',
			'%start%' => 'start',
			'%end%' => 'end');

		$tmp = array();

		foreach ($linkList as $link)
		{
			$format = $link['do_link'] ? $linkFormat : $selectedFormat;
			foreach ($correspondenceList as $find => $index)
				$format = str_replace($find, $link[$index], $format);
			$tmp[] = $format;
		}

		$output = implode($separator, $tmp);

		return $output;
	}

    /**
	 * Clean Request
	 * @var string $remove
	 * @var string $request
	 * @return string 
	 * @access public
	 */
	function _cleanRequest($remove = "", $request = null)
	{
    	if (is_null($request))
        	$request = $_GET;
	    if (!is_array($remove))
    	    $remove = array($remove);

	    $tmp = array();
    	$flagAdd = true;

		/* remove selected variables from posted data */
	    foreach ($request as $key => $value)
    	{
        	$flagAdd = true;

	        foreach ($remove as $rem)
    	        if (strcasecmp($key, $rem) == 0)
        	        $flagAdd = false;

	        if ($flagAdd)
    	        $tmp[] = $key . "=" . urlencode("$value");
	    }
    	return implode("&amp;", $tmp);
	}
}
?>
