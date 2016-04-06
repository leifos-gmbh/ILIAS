<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI patch class for general settings
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class rolGeneralSettingsGUI
{
	/**
	 * @var array
	 */
	protected $post = array();


	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->post = $_POST;
		$this->settings = new ilSetting("raiffrol");
	}

	/**
	 * Modify general settings form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	function modifyGeneralSettingsForm($a_form)
	{
		// certificate recipient
		$ti = new ilTextInputGUI("ROL Empfänger Zertifikats-E-Mails", "cert_email");
		$ti->setMaxLength(200);
		//$ti->setSize();
		//$ti->setInfo($this->lng->txt(""));
		$ti->setValue($this->settings->get("cert_email"));
		$a_form->addItem($ti);

		// certificate directory
		$ti = new ilTextInputGUI("ROL Verzeichnis für Zertifikatsablage", "cert_dir");
		$ti->setMaxLength(200);
		//$ti->setSize();
		$ti->setValue($this->settings->get("cert_dir"));
		$ti->setInfo("z.B. /opt/cert_data");
		$a_form->addItem($ti);
		
		// abi udf field
		include_once("./Services/User/classes/class.ilUserDefinedFields.php");
		$i = ilUserDefinedFields::_getInstance();
		$options = array("" => "-- Bitte auswählen --");
		foreach ($i->getDefinitions() as $d)
		{
			$options[$d["field_id"]] = $d["field_name"];
		}
		$si = new ilSelectInputGUI("ROL Benutzerfeld für ABI", "abi_udf");
		$si->setOptions($options);
		$si->setValue($this->settings->get("abi_udf"));
		$a_form->addItem($si);
		
	}


	/**
	 * Update info
	 */
	function updateGeneralSettings()
	{
		$this->settings->set("cert_email", $this->post["cert_email"]);
		$this->settings->set("cert_dir", $this->post["cert_dir"]);
		$this->settings->set("abi_udf", $this->post["abi_udf"]);
	}


}

?>