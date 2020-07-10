/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ParagraphEditorActionFactory from './paragraph-editor-action-factory.js';
import EditorActionFactory from '../../../actions/editor-action-factory.js';

/**
 * action factory for calling the server
 */
export default class PageActionFactory {

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
   * @returns {PageEditorActionFactory}
   */
  editor() {
    return new ParagraphEditorActionFactory(this.editorActionFactory);
  }

}