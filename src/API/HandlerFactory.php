<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API;

/**
 * Handler factory
 *
 * @author killing@leifos.de
 */
class HandlerFactory
{
	/**
	 * Constructor
	 */
	public function __construct()
	{

	}

	/**
	 * Get handler
	 * @param Int\CommandFactory $command_factory
	 * @return Int\CommandHandler
	 */
	public function getHandlerForCommandFactory(Int\CommandFactory $command_factory): Int\CommandHandler
	{
		// this is a shurtcut currently, we might need a map
		$handler_class = "\\".str_replace("CommandFactory", "CommandHandler", get_class($command_factory));
		return new $handler_class($command_factory->getParameters());
	}


}