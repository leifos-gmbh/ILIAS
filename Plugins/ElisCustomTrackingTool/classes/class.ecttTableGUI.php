<?php

require_once('Services/Table/classes/class.ilTable2GUI.php');

abstract class ecttTableGUI extends ilTable2GUI
{
	protected $ROW_TEMPLATE = 'tpl.dynamic_table_row.html';

	public function __construct($link_target, $parent_cmd)
	{
		parent::__construct(null, null);

		$this->link_target = $link_target;
		$this->parent_cmd = $parent_cmd;

		$this->setFormAction($this->link_target);

		$this->setRowTemplate($this->ROW_TEMPLATE, PLUGIN_PATH);
	}

	protected function fillRow($set)
	{
		foreach($set as $field => $content)
		{
			$this->tpl->setCurrentBlock('table_cell');

			$class = $this->getFieldClass($field);
			$this->tpl->setVariable('TABLE_CELL_CLASS', $class);

			$content = $this->formatField($field, $content);
			$this->tpl->setVariable('TABLE_CELL_CONTENT', $content);

			$this->tpl->parseCurrentBlock();
		}
	}

	public function setContentData($contentdata)
	{
		$this->resetColumns();


		if( count($contentdata) > 0 )
		{
			if( !$this->columnsInitialised() )
			{
				$fields = array_keys(current($contentdata));
				$this->initColumns($fields);
			}
		}
		else $this->handleEmptyData();

		$this->setData($contentdata);

		return $this;
	}

	abstract protected function formatField($field, $content);

	abstract protected function getFieldClass($field);

	abstract protected function initColumns($cols);

	private function resetColumns()
	{
		$this->column = array();
		return $this;
	}

	private function columnsInitialised()
	{
		return ( is_array($this->column) && count($this->column) );
	}

	private function handleEmptyData()
	{
		$this->disable('title');
		$this->disable('header');
		$this->disable('footer');
	}

	/**
	 * method has to be public, because
	 * the overwritten one is public as well
	 */
	public function setOrderLink($sort_field, $order_dir)
	{
		global $ilUser;

		$hash = "";
		if (is_object($ilUser) && $ilUser->prefs["screen_reader_optimization"])
		{
			$hash = "#".$this->getTopAnchor();
		}

		// build nav parameter
		$nav_param = '&'.$this->getNavParameter().'='.
						$sort_field.":".$order_dir.":".$this->offset;

		// build order link
		$order_link = $this->link_target.'&cmd='.$this->parent_cmd.$nav_param.$hash;

		// set order link
		$this->tpl->setVariable("TBL_ORDER_LINK", $order_link);
	}

	public function getLinkbar($a_num)
	{
		global $ilCtrl, $lng, $ilUser;

		$hash = "";
		if (is_object($ilUser) && $ilUser->prefs["screen_reader_optimization"])
		{
			$hash = "#".$this->getTopAnchor();
		}

		$link = $this->link_target.'&cmd='.$this->parent_cmd."&".$this->getNavParameter()."=".
				$this->getOrderField().":".$this->getOrderDirection().":";

		$LinkBar = "";
		$layout_prev = $lng->txt("previous");
		$layout_next = $lng->txt("next");

		// if more entries then entries per page -> show link bar
		if ($this->max_count > $this->getLimit() || $this->custom_prev_next)
		{
			// previous link
			if ($this->custom_prev_next && $this->custom_prev != "")
			{
				$LinkBar .= "<a href=\"".$this->custom_prev.$hash."\">".$layout_prev."&nbsp;</a>";
			}
			else if ($this->getOffset() >= 1 && !$this->custom_prev_next)
			{
				$prevoffset = $this->getOffset() - $this->getLimit();
				$LinkBar .= "<a href=\"".$link.$prevoffset.$hash."\">".$layout_prev."&nbsp;</a>";
			}
			else
			{
				$LinkBar .= '<span class="ilTableFootLight">'.$layout_prev."&nbsp;</span>";
			}

			// current value
			if ($a_num == "1")
			{
				$LinkBar .= '<input type="hidden" name="'.$this->getNavParameter().
					'" value="'.$this->getOrderField().":".$this->getOrderDirection().":".$this->getOffset().'" />';
			}

			// calculate number of pages
			$pages = intval($this->max_count / $this->getLimit());

			// add a page if a rest remains
			if (($this->max_count % $this->getLimit()))
				$pages++;

			// links to other pages
			$offset_arr = array();
			for ($i = 1 ;$i <= $pages ; $i++)
			{
				$newoffset = $this->getLimit() * ($i-1);

				$nav_value = $this->getOrderField().":".$this->getOrderDirection().":".$newoffset;
				$offset_arr[$nav_value] = $i;
				if ($newoffset == $this->getOffset())
				{
				//	$LinkBar .= "[".$i."] ";
				}
				else
				{
				//	$LinkBar .= '<a '.$layout_link.' href="'.
				//		$link.$newoffset.'">['.$i.']</a> ';
				}
			}

			// show next link (if not last page)
			if ($this->custom_prev_next && $this->custom_next != "")
			{
				if ($LinkBar != "")
					$LinkBar .= "<span> | </span>";
				$LinkBar .= "<a href=\"".$this->custom_next.$hash."\">&nbsp;".$layout_next."</a>";
			}
			else if (! ( ($this->getOffset() / $this->getLimit())==($pages-1) ) && ($pages!=1) &&
				!$this->custom_prev_next)
			{
				if ($LinkBar != "")
					$LinkBar .= "<span> | </span>";
				$newoffset = $this->getOffset() + $this->getLimit();
				$LinkBar .= "<a href=\"".$link.$newoffset.$hash."\">&nbsp;".$layout_next."</a>";
			}
			else
			{
				if ($LinkBar != "")
					$LinkBar .= "<span > | </span>";
				$LinkBar .= '<span class="ilTableFootLight">&nbsp;'.$layout_next."</span>";
			}

			if (count($offset_arr) && !$this->getDisplayAsBlock() && !$this->custom_prev_next)
			{
				$LinkBar .= "&nbsp;&nbsp;&nbsp;&nbsp;".
					'<label for="tab_page_sel_'.$a_num.'">'.$lng->txt("select_page").'</label> '.
					ilUtil::formSelect($this->nav_value,
					$this->getNavParameter().$a_num, $offset_arr, false, true, 0, "ilEditSelect",
					array("id" => "tab_page_sel_".$a_num)).
					' <input class="ilEditSubmit" type="submit" name="cmd['.$this->parent_cmd.']" value="'.
					$lng->txt("ok").'" /> ';
			}

			return $LinkBar;
		}
		else
		{
			return false;
		}
	}

}

?>