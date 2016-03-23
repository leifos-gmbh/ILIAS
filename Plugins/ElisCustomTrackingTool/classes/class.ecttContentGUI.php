<?php

class ecttContentGUI
{
	/**
	 * @var ilTemplate
	 */
	private $tpl;

	private $row_top = '';
	private $column_left = '';
	private $column_center = '';
	private $column_right = '';

	public function __construct()
	{
		$this->tpl = new ilTemplate(
			'tpl.ectt_content.html', true, true, PLUGIN_PATH
		);
	}

	public function setTopContent($content)
	{
		$this->row_top = $content;
	}

	public function addTopContent($content)
	{
		$this->row_top .= $content;
	}

	public function setLeftContent($content)
	{
		$this->column_left = $content;
	}

	public function addLeftContent($content)
	{
		$this->column_left .= $content;
	}

	public function setCenterContent($content)
	{
		$this->column_center = $content;
	}

	public function addCenterContent($content)
	{
		$this->column_center .= $content;
	}

	public function setRightContent($content)
	{
		$this->column_right = $content;
	}

	public function addRightContent($content)
	{
		$this->column_right .= $content;
	}

	public function show()
	{
		$left = $center = $right = false;

		if( strlen($this->row_top) )
		{
			$top = true;
			$this->tpl->setCurrentBlock('top_row');
			$this->tpl->setVariable('TOP_ROW', $this->row_top);
			$this->tpl->parseCurrentBlock();
		}

		if( strlen($this->column_left) )
		{
			$left = true;
			$this->tpl->setCurrentBlock('left_column');
			$this->tpl->setVariable('LEFT_COLUMN', $this->column_left);
			$this->tpl->parseCurrentBlock();
		}

		if( strlen($this->column_center) )
		{
			$center = true;
			$this->tpl->setCurrentBlock('center_column');
			$this->tpl->setVariable('CENTER_COLUMN', $this->column_center);
			$this->tpl->parseCurrentBlock();
		}

		if( strlen($this->column_right) )
		{
			$right = true;
			$this->tpl->setCurrentBlock('right_column');
			$this->tpl->setVariable('RIGHT_COLUMN', $this->column_right);
			$this->tpl->parseCurrentBlock();
		}


		switch(true)
		{
			case !$left && $center && !$right:

				//$this->tpl->setCurrentBlock('columns_center');
				//$this->tpl->touchBlock('columns_center');
				//$this->tpl->parseCurrentBlock();
				break;

			case $left && $center && $right:

				$this->tpl->setCurrentBlock('columns_left_center_right');
				$this->tpl->touchBlock('columns_left_center_right');
				$this->tpl->parseCurrentBlock();
				breaK;

			case $left && $center && !$right:

				$this->tpl->setCurrentBlock('columns_left_center');
				$this->tpl->touchBlock('columns_left_center');
				$this->tpl->parseCurrentBlock();
				breaK;

			case !$left && $center && $right:

				$this->tpl->setCurrentBlock('columns_center_right');
				$this->tpl->touchBlock('columns_center_right');
				$this->tpl->parseCurrentBlock();
				breaK;

			case $left && !$center && $right:

				$this->tpl->setCurrentBlock('columns_left_right');
				$this->tpl->touchBlock('columns_left_right');
				$this->tpl->parseCurrentBlock();
				break;
		}

		$this->tpl->setCurrentBlock('content');
		$this->tpl->touchBlock('content');
		$this->tpl->parseCurrentBlock();

		global $tpl;

		$tpl->setVariable('ADM_CONTENT', $this->tpl->get());
	}
}

?>