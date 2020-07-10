/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import EditorAction from "../../../actions/editor-action.js";
import ACTIONS from "./page-action-types.js";

/**
 * COPage action factory
 *
 */
export default class PageEditorActionFactory {

  COMPONENT = "page";

  /**
   * @type {EditorActionFactory}
   */
  editorActionFactory;

  /**
   *
   * @param {EditorActionFactory} editorActionFactory
   */
  constructor(editorActionFactory) {
    this.editorActionFactory = editorActionFactory;
  }

  /**
   * @returns {EditorAction}
   */
  dndDrag() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.DND_DRAG);
  }

  /**
   * @returns {EditorAction}
   */
  dndDrop() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.DND_DROP);
  }

  /**
   * @returns {EditorAction}
   */
  createAdd(ctype, pcid, hierid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.CREATE_ADD, {
      ctype: ctype,
      pcid: pcid,
      hierid: hierid
    });
  }

  /**
   * @returns {EditorAction}
   */
  editOpen(cname, pcid, hierid, is_switch) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.EDIT_OPEN, {
      cname: cname,
      pcid: pcid,
      hierid: hierid,
      switch: is_switch
    });
  }

  /**
   * @returns {EditorAction}
   */
  multiToggle(ctype, pcid, hierid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.MULTI_TOGGLE, {
      ctype: ctype,
      pcid: pcid,
      hierid: hierid
    });
  }

  /**
   * @returns {EditorAction}
   */
  multiAction(type) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.MULTI_ACTION, {
      type: type
    });
  }

}