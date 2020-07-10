/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import EditorAction from "../../../actions/editor-action.js";
import ACTIONS from "./paragraph-action-types.js";

/**
 * COPage action factory
 *
 */
export default class ParagraphEditorActionFactory {

  COMPONENT = "par";

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
  cancel() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.PAR_CANCEL);
  }
}