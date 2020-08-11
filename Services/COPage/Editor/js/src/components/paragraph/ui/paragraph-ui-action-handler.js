/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "../actions/paragraph-action-types.js";

/**
 * Paragraph UI action handler
 */
export default class ParagraphUIActionHandler {

  /**
   * @type {ParagraphUI}
   */
  ui;

  /**
   * @type {ActionFactory}
   */
  actionFactory;

  /**
   * @type {Dispatcher}
   */
  dispatcher;

  /**
   * @type {Client}
   */
  client;

  /**
   * @param {ActionFactory} actionFactory
   * @param {Client} client
   */
  constructor(actionFactory, client) {
    this.actionFactory = actionFactory;
    this.client = client;
  }

  /**
   * @param {ParagraphUI} ui
   */
  setUI(ui) {
    this.ui = ui;
  }

  /**
   * @param {Dispatcher} dispatcher
   */
  setDispatcher(dispatcher) {
    this.dispatcher = dispatcher;
  }

  /**
   * @param {EditorAction} action
   * @param {Model} model
   */
  handle(action, page_model) {

    const dispatcher = this.dispatcher;
    const actionFactory = this.actionFactory;
    const client = this.client;
    let form_sent = false;

    const params = action.getParams();
    switch (action.getType()) {

      case "create.add":
        if (params.ctype === "par") {
          // @todo refactor legacy
          this.ui.editParagraph(params.pcid, params.hierid, 'insert', false);
        }
        break;

      case "edit.open":
        if (params.cname === "Paragraph") {
          // @todo refactor legacy
          this.ui.editParagraph(params.pcid, params.hierid, 'edit', params.switch);
        }
        break;

      case ACTIONS.PAR_CANCEL:
        this.ui.cmdCancel();
        break;

      case ACTIONS.SELECTION_FORMAT:
        this.ui.cmdSpan(params.format);
        break;

      case ACTIONS.SELECTION_REMOVE_FORMAT:
        this.ui.cmdRemoveFormat();
        break;

      case ACTIONS.SELECTION_KEYWORD:
        this.ui.cmdKeyword();
        break;

      case ACTIONS.SELECTION_TEX:
        this.ui.cmdTex();
        break;

      case ACTIONS.SELECTION_ANCHOR:
        this.ui.cmdAnc();
        break;

      case ACTIONS.LIST_BULLET:
        this.ui.cmdBList();
        break;

      case ACTIONS.LIST_NUMBER:
        this.ui.cmdNList();
        break;

      case ACTIONS.LIST_OUTDENT:
        this.ui.cmdListOutdent();
        break;

      case ACTIONS.LIST_INDENT:
        this.ui.cmdListIndent();
        break;

      case ACTIONS.LINK_WIKI_SELECTION:
        //this.ui.cmdListIndent();
        break;

      case ACTIONS.LINK_WIKI:
        //this.ui.cmdListIndent();
        break;

      case ACTIONS.LINK_INTERNAL:
        //this.ui.cmdExtLink();
        break;

      case ACTIONS.LINK_EXTERNAL:
        this.ui.cmdExtLink();
        break;

      case ACTIONS.LINK_USER:
        //this.ui.cmdListIndent();
        break;

    }

  }
}