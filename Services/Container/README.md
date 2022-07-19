# Container

# User Documentation

## Presentation of Resource Lists / Item Groups

### Business Rules

- Resource lists and item groups are currently presented underneath the content page, if they are not included in active(!) elements of the page (see bug report #9080, #26011). If resource should be hidden from users rbac or activation settings need to be used.

## Presentation of Tiles

### Business Rules

- All properties in the tile view are hidden, except alerts, https://mantis.ilias.de/view.php?id=25903#c63314
- If READ permission is given but access restricted due to timings or preconditions, users still can click on object title but are re-directed to the Info screen where related restrictions of availability are presented, https://mantis.ilias.de/view.php?id=25903#c63314 (see also Services/InfoScreen)

# Technical Documentation

## Presentation Control Flow

### (1) ilCategoryGUI extends ilContainerGUI (and similar classes)

- renderObject
  - getContentGUI() gets ilContainerContentGUI instance
  - -> ilContainerContentGUI::setOutput
  - outputs tabs, administration panel, "Add" dropdown, filter, "Edit Page" button, permalink

### (2) ilContainerByTypeContentGUI extends ilContainerContentGUI (and similar classes)

- uses ItemManager, ItemPresentationManager
- setOutput -> getRightColumnHTML, getCenterColumnHTML -> getMainContent -> renderItemList
- renderItemList
  - -> initRenderer() gets ilContainerRenderer instance 
  - -> ilContainerRenderer::renderItemBlockSequence(ItemPresentationManager::getItemBlockSequence());

### (3) ilContainerRenderer

- uses ItemPresentationManager, ItemRenderer, ObjectiveRenderer
- renderItemBlockSequence
  - initialises block template
  - iterates over ItemBlockSequence::getBlocks()
    - determine block ID and position
    - adds block with ID
    - -> ItemRenderer::renderItem()
    - adds item HTML to block
    - -> renderHelperCustomBlock, renderHelperTypeBlock (render block into block template)
  - -> renderDetails() (needed ???)