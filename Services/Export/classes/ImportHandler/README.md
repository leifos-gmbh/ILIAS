# Table of Contents
1. [XML File Handler](#xml-file-handler)
2. [XSD File Handler](#xsd-file-handler)
3. [Namespace](#namespace)
4. [Path](#path)
5. [Schema](#schema)
6. [Parser](#parser)
7. [Validation](#validation)

## XML File Handler <a name="xml-file-handler"></a>
```php
// Get xml file SplFileInfo
$xml_file_spl = new \Hoa\File\SplFileInfo('path to my xml file')

$import = new \ImportHandler\ilFactory()
$xml_file_handler = $import->file()->xml()->withFileInfo($xml_file_spl);
```
## XSD File Handler <a name="xsd-file-handler"></a>
```php
// Get xsd file SplFileInfo
$xsd_file_spl = new \Hoa\File\SplFileInfo('path to my xsd file')

$import = new \ImportHandler\ilFactory()
$xsd_file_handler = $import->file()->xsd()->withFileInfo($xml_file_spl);
```
## Namespace <a name="namespace"></a>
```php
// Get xml file SplFileInfo
$xml_file_spl = new \Hoa\File\SplFileInfo('path to my xml file')

$import = new \ImportHandler\ilFactory()
$xml_file_handler = $import->file()->xml()->withFileInfo($xml_file_spl);

// Add the namespace info to a xml file handler
$xml_file_handler = $xml_file_handler->withAdditionalNamespace(
    $import->file()->namespace()->handler()
        ->withNamespace('http://www.example.com/Dummy1/Dummy2/namespace/4_2')
        ->withPrefix('namespace')
)
```
## Path <a name="path"></a>
```php
$import = new \ImportHandler\ilFactory()

/** @var \ImportHandler\File\Path\ilHandler $path */
$path = $import->file()->path()->handler()
    ->withStartAtRoot(true)
    ->withNode($import->file()->path()->node()->simple()->withName('RootElement'))
    ->withNode($import->file()->path()->node()->simple()->withName('namespace:TargetElement'));
// $xPath_str = '/RootElement/namespace:TargetElement'
$xPath_str = $path->toString();

$path = $import->file()->path()->handler()
    ->withStartAtRoot(true)
    ->withNode($import->file()->path()->node()->simple()->withName('RootElement'))
    ->withNode($import->file()->path()->node()->anyNode());
// $xPath_str = '/RootElement/node()'
$xPath_str = $path->toString();

```
## Schema <a name="schema"></a>
The schema factory is used to receive xsd files located in <ilias_root>/xml/SchemaValidation.
```php
$version = new \ILIAS\Data\Version('1.0.0');
$type = 'grp';
$subtype = 'reference'
$schema = new \Schema\ilXmlSchemaFactory();

# Get by version or latest if xsd with version odes not exist.
# Looks for: ilias_grp_reference_1_0.xsd
$xsd_file_spl = $schema->getByVersionOrLatest($version, $type, $subtype);

# Get by version
# Looks for: ilias_grp_1_0.xsd
$xsd_file_spl = $schema->getByVersion($version, $type, '');

# Get latest file with type and subtype
# Looks for: ilias_grp_x_y.xsd such that x and y are the largest numbers.
$xsd_file_spl = $schema->getLatest($type, $subtype);
```
## Parser <a name="parser"></a>

```php
// Get xml file SplFileInfo
$xml_file_spl = new \Hoa\File\SplFileInfo('path to my xml file')

$import = new \ImportHandler\ilFactory()
$xml_file_handler = $import->file()->xml()->withFileInfo($xml_file_spl);

// Build xPath to xml node
// $path->toString() = '/RootElement/namespace:TargetElement'
/** @var \ImportHandler\File\Path\ilHandler $path */
$path = $import->file()->path()->handler()
    ->withStartAtRoot(true)
    ->withNode($import->file()->path()->node()->simple()->withName('RootElement'))
    ->withNode($import->file()->path()->node()->simple()->withName('namespace:TargetElement'));

// Because the path contains the namespace 'namespace' we have to add the namespace
// info to the xml file handler
$xml_file_handler = $xml_file_handler->withAdditionalNamespace(
    $import->file()->namespace()->handler()
        ->withNamespace('http://www.example.com/Dummy1/Dummy2/namespace/4_2')
        ->withPrefix('namespace')
)

$parser = $import->parser()->DOM()->withFileHandler($xml_file_handler);
/** @var \ImportHandler\File\XML\Node\Info\ilDOMNodeCollection $node_infos */
$node_infos = $parser->getNodeInfoAt($path);

foreach ($node_infos as $node_info) {
    // Do something with the node_info
}
```
## Validation <a name="validation"></a>
### Whole Xml File:
```php
// Get the xml SplFileInfo
$xml_file_spl = new SplFileInfo('path to my xml file')

// Get the xsd SplFileInfo
$xsd_file_spl = new SplFileInfo('path to my xsd file')

// Initialize a xml/xsd file handler
$import = new \ImportHandler\ilFactory();
$xml_file_handler = $import->file()->xml()->withFileInfo($xml_file_spl);
$xsd_file_handler = $import->file()->xsd()->withFileInfo($xsd_file_spl);

/** @var \ImportStatus\ilCollection $validation_results */
// Validate
$validation_results = $import->file()->validation()->handler()->validateXMLFile(
    $xml_file_handler,
    $xsd_file_handler
);

// Check if an import failure occured
if ($validation_results->hasStatusType(\ImportStatus\StatusType::FAILED)) {
    // Do something on failure
}
```

### Xml at Xml Node in a Xml File:
```php
// Get the xml SplFileInfo
$xml_file_spl = new SplFileInfo('path to my xml file')

// Get the xsd SplFileInfo
$xsd_file_spl = new SplFileInfo('path to my xsd file')

// Initialize a xml/xsd file handler
$import = new \ImportHandler\ilFactory();
$xml_file_handler = $import->file()->xml()->withFileInfo($xml_file_spl);
$xsd_file_handler = $import->file()->xsd()->withFileInfo($xsd_file_spl);

// Build xPath to xml node
// $path->toString() = '/RootElement/namespace:TargetElement'
/** @var \ImportHandler\File\Path\ilHandler $path */
$path = $import->file()->path()->handler()
    ->withStartAtRoot(true)
    ->withNode($import->file()->path()->node()->simple()->withName('RootElement'))
    ->withNode($import->file()->path()->node()->simple()->withName('namespace:TargetElement'));

// Because the path contains the namespace 'namespace' we have to add the namespace
// info to the xml file handler
$xml_file_handler = $xml_file_handler->withAdditionalNamespace(
    $import->file()->namespace()->handler()
        ->withNamespace('http://www.example.com/Dummy1/Dummy2/namespace/4_2')
        ->withPrefix('namespace')
)

/** @var \ImportStatus\ilCollection $validation_results */
// Validate
$validation_results = $import->file()->validation()->handler()->validateXMLAtPath(
    $xml_file_handler,
    $xsd_file_handler,
    $path
);

// Check if an import failure occured
if ($validation_results->hasStatusType(\ImportStatus\StatusType::FAILED)) {
    // Do something on failure
}
```
