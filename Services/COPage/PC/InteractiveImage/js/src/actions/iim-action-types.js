/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

const ACTIONS = {

  // query actions (being sent to the server to "ask for stuff")
  Q_INIT: "init",

  // command actions (being sent to the server to "change things")
  C_SAVE_PROPERTIES: "save.properties",

  // editor actions (things happening in the editor client side)
  E_ADD_TRIGGER: "add.trigger",
  E_TRIGGER_PROPERTIES: "trigger.properties",
  E_TRIGGER_OVERLAY: "trigger.overlay",
  E_TRIGGER_POPUP: "trigger.popup",
  E_TRIGGER_BACK: "trigger.back",
  E_SWITCH_SETTINGS: "switch.settings",
  E_SWITCH_OVERLAYS: "switch.overlays",
  E_SWITCH_POPUPS: "switch.popups"
};
export default ACTIONS;