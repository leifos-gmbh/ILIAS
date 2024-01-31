# Roadmap

## Short Term

## Mid Term

### Improve Stability and Code Quality

#### ID for Select Options

Enum entries (for select and multiselect fields) should get an actual ID.
The current ID acts more like a ordering parameter, which makes mapping entries
during editing of the fields unnecessarily tedious and error prone.

#### Repository for Records, Field Definitions, and Values

The data structure of AdvancedMetaData consists mainly of three hierarchical
entities: records, which contain field definitions, which contain values.
The latter are also always related to an ILIAS Object (or subobject).

There should be one collective repository covering records, field definitions,
and values s.t. consistency in the data (e.g. on deletion) can be maintained
easily. This repository should not always work its way from records as the
root all the way down to the values; in most of the use cases, one does not
need the whole hierarchy, and building it up anyways would be detrimental to
performance.

The main use cases seem to require either records with their fields, or values
for a group of objects with their respective fields. The former is relevant
for managing records, either in the Administration or in the context of
specific objects (see e.g. `ilAdvancedMDSettingsGUI`), and the latter for
managing and displaying metadata values of objects (see e.g.
`ilAdvancedMDRecordGUI`).

Auxiliary data like translations, record scopes, record selection of objects,
etc. should not be understood as their own entities, but rather as properties
of the main entities, and should be treated as such.

As the collective repository then requires a reasonable amount of internal
infrastructure, it should delegate to smaller classes. Overall, an
assembly-like structure seems sensible.

#### Data Objects for Records, Field Definitions, and Values

There already are data objects that one can continue to make use of
(`ilAdvancedMDRecord`, `ilAdvancedMDFieldDefinition`, the classes in ADT).
Those objects can be gradually refactored to fit into the repository
pattern.

As a first step, one could even have their static `getInstance` methods
call the new repository, to avoid refactoring consumers.

#### Type-specific Properties of Field Definitions

Most field definitions have a few bespoke configuration options. Some
of these options are even persisted in a type-specific table in the 
database (see `adv_mdf_enum` and e.g. `ilAdvancedMDFieldDefinitionSelect`).
In order to distribute all field definitions from a single source, but
not bloat that source too much, reading and manipulating these options
should be done in type-specific classes.

The process for this could be as follows, e.g. when reading out
a field definition: a central class reads out all universal properties
of the definition, and compiles them as a dummy object. This dummy object
is then passed to one of a number of refinery-like classes according to
the type of the definition, which read out type-specific properties.
This class then takes the universal properties from the dummy object,
the type-specific properties it reads out itself, and creates from
them the actual data object for the field definition.

## Long Term
