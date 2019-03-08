<?php

use Datto\JsonRpc\Client;
use GuzzleHttp\Psr7\Request;


/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir("../../../..");

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

// http://scorsese.local/page_editor/Services/COPage/Editor/tests/class.ilPageEditorMockRpcClient.php

/**
 *
 *
 * @author @leifos.de
 * @ingroup
 */
class ilPageEditorMockRpcClient
{
	/**
	 * @var \Datto\JsonRpc\Client
	 */
	protected $json_rpc_client;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var string
	 */
	protected $ilias_path;

	/**
	 * @var int
	 */
	protected $cat_ref_id;

	/**
	 * @var \ILIAS\DI\HTTPServices
	 */
	protected $http;

	/**
	 * Constructor
	 */
	public function __construct(string $ilias_path, int $cat_ref_id)
	{
		global $DIC;
		$this->ilias_path = $ilias_path;
		$this->cat_ref_id = $cat_ref_id;
		$this->json_rpc_client = new Client();
		$this->ctrl = $DIC->ctrl();
		$this->http_service = $DIC->http();
	}

	/**
	 * Get add query
	 *
	 * @param int $id
	 * @param string $method
	 * @param $params
	 */
	public function addQuery(int $id, string $method, $params)
	{
		$this->log("Add Query, id: $id, method: $method, params: ".print_r($params, true));
		$this->json_rpc_client->query($id, $method, $params);
	}

	/**
	 * Get rpc message
	 *
	 * @return string
	 */
	public function getRpcMessage(): string
	{
		return $this->json_rpc_client->encode();
	}

	/**
	 * Gets the server uri (currently page editing in a category)
	 */
	public function getServerUri()
	{
		$ctrl = $this->ctrl;

		$_GET["ref_id"] = $this->cat_ref_id;
		$_GET["baseClass"] = "ilRepositoryGUI";
		$ctrl->setTargetScript("ilias.php");
		$ctrl->saveParameterByClass("ilRepositoryGUI", "ref_id");
		$uri = $ctrl->getLinkTargetByClass(["ilRepositoryGUI", "ilObjCategoryGUI", "ilContainerPageGUI", "ilPageEditor2GUI", "ilPageEditorRpcAdapterGUI"], "", "", false, false);
		return $this->ilias_path."/".$uri;
	}

	/**
	 * Send json rpc request
	 *
	 * @param string $message
	 * @param string $uri
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	protected function sendJsonRpcRequest(string $message, string $uri): \Psr\Http\Message\ResponseInterface
	{
		// set cookies
		$r = $this->http_service->request()->getCookieParams();
		$cookies = [
			new \GuzzleHttp\Cookie\SetCookie(['Domain' => "scorsese.local",'Name' => "ilClientId",'Value' => $r["ilClientId"], 'Discard' => true]),
			new \GuzzleHttp\Cookie\SetCookie(['Domain' => "scorsese.local",'Name' => "PHPSESSID",'Value' => $r["PHPSESSID"], 'Discard' => true]),
			new \GuzzleHttp\Cookie\SetCookie(['Domain' => "scorsese.local",'Name' => "SESSID",'Value' => $r["SESSID"], 'Discard' => true])
		];
		$jar = new \GuzzleHttp\Cookie\CookieJar(false, $cookies);

		// get client
		$client = new \GuzzleHttp\Client(["cookies" => $jar]);

		// send request
		return $client->post($uri, [
			GuzzleHttp\RequestOptions::BODY => $message
		]);
	}

	/**
	 * Run test
	 */
	public function send()
	{
		$message = $this->getRpcMessage();
		$this->log("Send Message: ".$message);
		$uri = $this->getServerUri();
		$response = $this->sendJsonRpcRequest($message, $uri);

		$this->log("Response ".print_r($response->getBody()->getContents(), true));
		$this->log("");
	}

	/**
	 * log
	 *
	 * @param
	 * @return
	 */
	protected function log($string)
	{
		echo $string."\n<br>";
	}

	
}

// run test
$mock_client = new ilPageEditorMockRpcClient("http://scorsese.local/page_editor", 69);

$mock_client->addQuery(1, 'add', array(1, 6));
$mock_client->send();

$mock_client->addQuery(1, 'add', array('a', 'b'));
$mock_client->send();

$mock_client->addQuery(1, 'add', array(1, 6));
$mock_client->addQuery(2, 'add', array('a', 'b'));
$mock_client->send();