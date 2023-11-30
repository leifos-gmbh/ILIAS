# Table of Contents
1. [Component Import Validation](#import-validation)
   1. [XSD File Naming Convention](#import-file-naming-convention)
   2. [Enable Import Validation](#enable-import-validation)
   3. [Disable Import Validation](#disable-import-validation)
2. [Code Examples](#code-examples)
   1. [XML File Handler](#xml-file-handler)
   2. [XSD File Handler](#xsd-file-handler)
   3. [Namespace](#namespace)
   4. [Path](#path)
   5. [Schema](#schema)
   6. [Parser](#parser)
   7. [Validation](#validation)

<a name="import-validation"></a>
# Component Import Validation
The import validation of a component xml is only enabled if a schema file is available.
It is important that the schema file is located in the directory 'xml/SchemaValidation'
and that the naming convention is followed.

<a name="import-file-naming-convention"></a>
## XSD File Naming Convention
Schema files have to follow the naming convention:

ilias_{type_string}_{version_string}.xsd

'type_string' can either be {type} or {type}_{subtype}.

'version_string' follows the pattern: {major_version_number}_{minor_version_number}.

To determine the matching schema file for a given xml-export file,
the value of 'type_string' is compared with the value of the attribute 'entity' of the 'exp:Export'-node
and the value of 'version_string' is compared with the value of the attribute 'SchemaVersion'
of the 'exp:Export'-Node.

If the xml-export file contains a dataset, the 'entity' attribute of the 'ds:Rec'-nodes is used instead of the 'entity' attribtue of the 'exp:Export'-node.

If the Version numbers do not match, the schema file with the highest version number is used.

For example take a look at 'ilias_grp_reference_9_0.xsd'.
Here 'type_string' is 'grp_reference' with type 'grp' and subtype 'reference'.
'version_string' is '9_0' with 'major_version_number' 9 and a 'minor_version_number' 0.

<a name="enable-import-validation"></a>
## Enable Import Validation
Add the schema file to the directory 'xml/SchemaValidation'.

<a name="disable-import-validation"></a>
## Disable Import Validation
Remove the schema from in the directory 'xml/SchemaValidation'.

<a name="code-examples"></a>
# Code Examples
<a name="xml-file-handler"></a>
## XML File Handler
```php
// Get xml file SplFileInfo
$xml_file_spl = new \Hoa\File\SplFileInfo('path to my xml file')

$import = new \ImportHandler\ilFactory()
$xml_file_handler = $import->file()->xml()->withFileInfo($xml_file_spl);
```
<a name="xsd-file-handler"></a>
## XSD File Handler
```php
// Get xsd file SplFileInfo
$xsd_file_spl = new \Hoa\File\SplFileInfo('path to my xsd file')

$import = new \ImportHandler\ilFactory()
$xsd_file_handler = $import->file()->xsd()->withFileInfo($xml_file_spl);
```
<a name="namespace"></a>
## Namespace
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
<a name="path"></a>
## Path
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
<a name="schema"></a>
## Schema
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
<a name="parser"></a>
## Parser
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
<a name="validation"></a>
## Validation
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
