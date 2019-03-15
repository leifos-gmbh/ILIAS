<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use Datto\JsonRpc\Evaluator;
use Datto\JsonRpc\Exceptions\ArgumentException;
use Datto\JsonRpc\Exceptions\MethodException;

class ilPageEditorRpcApi implements Evaluator
{
	/**
	 * @var ilLogger
	 */
	protected $log;

	/**
	 * @var ilPageObjectGUI
	 */
	protected $page_gui;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * Constructor
	 * @param ilPageObjectGUI $page_gui
	 * @param \ILIAS\DI\UIServices $ui
	 */
	public function __construct(ilPageObjectGUI $page_gui, \ILIAS\DI\UIServices $ui)
	{
		$this->log = ilLoggerFactory::getLogger("copg");
		$this->ui = $ui;
		$this->page_gui = $page_gui;
	}


	/**
	 * @param string $method
	 * @param array $arguments
	 * @return int|mixed
	 * @throws ArgumentException
	 * @throws MethodException
	 */
	public function evaluate($method, $arguments)
	{

		switch ($method)
		{
			case "ui/all":
				return $this->handleUIAll();
				break;

			case "page/html":
				return $this->handlePageHtml();
				break;

			case "page/model":
				return $this->handlePageModel();
				break;
		}

		throw new MethodException();
	}

	/**
	 * Handle page/html
	 *
	 * @return string
	 */
	protected function handlePageHtml(): string
	{
		$page_gui = $this->page_gui;
		$page_gui->getPageObject()->buildDom();
		$page_gui->getPageObject()->addHierIDs();
		$page_gui->setEdit2(true);
		return $page_gui->getHTML();
	}

	/**
	 * Handle page/model
	 *
	 * @return string (json encoded)
	 */
	protected function handlePageModel(): string
	{
		$model = [];
		$page_gui = $this->page_gui;
		$page = $page_gui->getPageObject();
		$page->addHierIDs();
		$dom = $page->getDomDoc();
		foreach ($page->getAllPCIds(true) as $id)
		{
			$id = explode(":", $id);
			$pc_model = new \stdClass;
			$pc_model->hier_id = $id[0];
			$pc_model->pc_id = $id[1];
			try
			{
				$pc_component = $page->getContentObject($id[0], $id[1]);
				$pc_model->pc_type = $pc_component->getType();
				$pc_model->pc_model = $pc_component->getModel();
			}
			catch (Exception $e)
			{

			}

			//$this->log->debug(print_r($pc_node, true));




			$model[] = $pc_model;
		}

		return json_encode($model);
	}


	/**
	 * Handle ui/all
	 *
	 * @return string (json encoded)
	 */
	protected function handleUIAll(): string
	{
		$page_gui = $this->page_gui;
		$page = $page_gui->getPageObject();

		$ui = $this->ui;
		$f = $ui->factory();
		$r = $ui->renderer();

		$ret = new \stdClass;

		// buttons...
		$ret->buttons = new \stdClass;

		// ... add button
		$add_button = $f->button()->bulky(
			$f->glyph()->add(), "Add", "#");
		$ret->buttons->add = $r->render($add_button);

		// dropdowns...
		$ret->dropdowns = new \stdClass;

		// ... add dropdown
		$add_dropdown = $f->dropdown()->standard([
			$f->button()->shy("Add Paragraph", "#par"),
			$f->button()->shy("Add Image", "#media.image"),
			$f->button()->shy("Add Video", "#media.video")
		]);
		$ret->dropdowns->add = $r->render($add_dropdown);

		// glyphs...
		$ret->glyphs = new \stdClass;

		// ... add glyph
		$add_glyph = $f->glyph()->add();
		$ret->glyphs->add = $r->render($add_glyph);

		// forms...
		$ret->forms = [];
		// for all page components...
		foreach (ilCOPagePCDef::getPCDefinitions() as $def)
		{
			if (in_array($def["pc_class"],["ilPCTab"]))
			{
				continue;
			}
			$pc_gui_class = $def["pc_gui_class"];
			$pc_class = $def["pc_class"];
			/** @var ilPageContent $pc_gui */
			$pc = new $pc_class($page);
			$pc_gui = new $pc_gui_class($page, $pc, "");
			$ret->forms[$pc->getType()]["create"] = $pc_gui->getCreationForm();
			$ret->forms[$pc->getType()]["edit"] = $pc_gui->getEditForm();
		}

		return json_encode($ret);
	}

}
