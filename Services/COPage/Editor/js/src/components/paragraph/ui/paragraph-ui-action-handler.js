/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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

      case "par.cancel":
        this.ui.cmdCancel();
        break;
    }

  }
}