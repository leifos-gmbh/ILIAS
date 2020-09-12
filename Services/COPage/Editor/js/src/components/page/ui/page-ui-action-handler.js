/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Page UI action handler
 */
export default class PageUIActionHandler {

  debug = true;

  /**
   * @type {PageUI}
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

  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  /**
   * @param {PageUI} ui
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
   * @param {PageModel} model
   */
  handle(action, model) {

    const dispatcher = this.dispatcher;
    const actionFactory = this.actionFactory;
    const client = this.client;
    let form_sent = false;

    const params = action.getParams();
    switch (action.getType()) {

      case "component.insert":
        if (model.getCurrentPCName() !== "Paragraph") {
          let ctype = this.ui.getPCTypeForName(params.cname);
          client.sendForm(actionFactory.page().command().createLegacy(ctype, params.pcid,
            params.hierid));
          form_sent = true;
        }
        break;

      case "component.edit":
        if (model.getCurrentPCName() !== "Paragraph") {
          client.sendForm(actionFactory.page().command().editLegacy(params.cname, params.pcid,
            params.hierid));
          form_sent = true;
        }
        break;

      case "multi.toggle":
        this.ui.highlightSelected(model.getSelected());
        break;

      case "multi.action":
        let type = params.type;

        // @todo refactor legacy
        if (["delete", "cut", "copy", "characteristic", "activate"].includes(type)) {
          client.sendForm(actionFactory.page().command().multiLegacy(type,
            Array.from(model.getSelected())));
          form_sent = true;
        }
        if (["all", "none"].includes(type)) {
          this.ui.highlightSelected(model.getSelected());
        }
        break;
    }


    // if we sent a (legacy) form, deactivate everything
    if (form_sent === true) {
      this.ui.showPageHelp();
      this.ui.hideAddButtons();
      this.ui.hideDropareas();
      this.ui.disableDragDrop();
    } else {

      this.log("page-ui-action-handler.handle state " + model.getState());

      switch (model.getState()) {
        case model.STATE_PAGE:
          this.ui.showPageHelp();
          this.ui.showAddButtons();
          this.ui.hideDropareas();
          this.ui.enableDragDrop();
          break;

        case model.STATE_MULTI_ACTION:
          this.ui.showMultiButtons();
          this.ui.hideAddButtons();
          this.ui.hideDropareas();
          this.ui.disableDragDrop();
          break;

        case model.STATE_DRAG_DROP:
          this.ui.showPageHelp();
          this.ui.hideAddButtons();
          this.ui.showDropareas();
          break;

        case model.STATE_COMPONENT:
          //this.ui.showPageHelp();
          this.ui.hideAddButtons();
          this.ui.hideDropareas();
          this.ui.disableDragDrop();
          break;
      }
    }
  }
}