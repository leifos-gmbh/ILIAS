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

import ACTIONS from "./iim-action-types.js";

/**
 * COPage action factory
 *
 */
export default class IIMEditorActionFactory {

  /**
   * @type {EditorActionFactory}
   */
  //editorActionFactory;

  /**
   *
   * @param {EditorActionFactory} editorActionFactory
   */
  constructor(editorActionFactory) {
    this.COMPONENT = "InteractiveImage";
    this.editorActionFactory = editorActionFactory;
  }

  /**
   * @returns {EditorAction}
   */
  addTrigger() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_ADD_TRIGGER, {});
  }

  editTrigger(nr) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_EDIT_TRIGGER, {
      triggerNr : nr
    });
  }

  /**
   * @returns {EditorAction}
   */
  triggerProperties() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_PROPERTIES, {});
  }

  /**
   * @returns {EditorAction}
   */
  triggerOverlay() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_OVERLAY, {});
  }

  /**
   * @returns {EditorAction}
   */
  triggerPopup() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_POPUP, {});
  }

  /**
   * @returns {EditorAction}
   */
  triggerBack() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_TRIGGER_BACK, {});
  }

  /**
   * @returns {EditorAction}
   */
  switchSettings() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_SWITCH_SETTINGS, {});
  }

  /**
   * @returns {EditorAction}
   */
  switchOverlays() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_SWITCH_OVERLAYS, {});
  }

  /**
   * @returns {EditorAction}
   */
  switchPopups() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.E_SWITCH_POPUPS, {});
  }

}