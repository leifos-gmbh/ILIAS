/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

const ACTIONS = {

  // query actions (being sent to the server to "ask for stuff")
  UI_ALL: "ui.all",

  // command actions (being sent to the server to "change things")
  CREATE_LEGACY: "create.legacy", // calls a legacy creation form for a page component
  EDIT_LEGACY: "edit.legacy",     // calls a legacy edit form for a page component
  MULTI_LEGACY: "multi.legacy",   // performas a multi-selection action the legacy way (send form)

  // editor actions (things happening in the editor client side)
  DND_DRAG: "dnd.drag",           // start dragging
  DND_DROP: "dnd.drop",           // dropping
  CREATE_ADD: "create.add",       // hit add link in add dropdown
  EDIT_OPEN: "edit.open",         // hit componet for editing, opens form in slate or legacy view
  MULTI_TOGGLE: "multi.toggle",   // toggle an element for multi selection
  MULTI_ACTION: "multi.action",   // perform multi action

};
export default ACTIONS;