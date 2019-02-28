<?php

use Datto\JsonRpc\Server;

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Page editor json rpc server
 *
 * @author @leifos.de
 * @ingroup 
 */
class ilPageEditorRpcServer
{
	/**
	 * @var ilLogger
	 */
	protected $log;

	/**
	 * @var string
	 */
	protected $json_rpc_request;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->log = ilLoggerFactory::getLogger("copg");
		$this->json_rpc_request = "";
	}

	/**
	 * Get request
	 */
	public function getRequest()
	{
		global $DIC;

		$raw_content = $DIC->http()->request()->getBody()->getContents();

		// @todo: check for valid json?

		$this->json_rpc_request = $raw_content;
		$this->log->debug("json_rpc_request: ".$this->json_rpc_request);
	}
	
	
	/**
	 * Send reply
	 */
	public function reply()
	{
		$api = new ilPageEditorRpcApi();
		$server = new Server($api);
		$reply = $server->reply($this->json_rpc_request);
		echo $reply, "\n";
		exit;
	}
}