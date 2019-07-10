#Booking Manager

## Using Booking Manager in Repository Objects

Currently additional features are organised by ilObjectServiceSettingsGUI. You need to integrate this into your settings form initialisation and update procedure:

```
ilObjectServiceSettingsGUI::initServiceSettingsForm(
	$this->object->getId(),
	$form,
	array(
	[...],
		ilObjectServiceSettingsGUI::BOOKING
	)
);
```

```
// after $form initialisation
...
ilObjectServiceSettingsGUI::updateServiceSettingsForm(
	$this->object->getId(),
	$form,
	array(
		[...],
		ilObjectServiceSettingsGUI::BOOKING
	)
);
```


Furthermore you need to a tab to your UI which points to the class ilBookingGatewayGUI:

```
$tabs->addTab("booking", $lng->txt("..."),
	$ctrl->getLinkTargetByClass(array("ilbookinggatewaygui"), ""));
```

The same class needs to be integrated in your executeCommand control flow:

