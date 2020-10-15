/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "../actions/page-action-types.js";

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
        // legacy
        console.log(model.getCurrentPCName());

        if (!["Paragraph", "Grid", "MediaObject", "Section"].includes(model.getCurrentPCName())) {
          let ctype = this.ui.getPCTypeForName(params.cname);
          client.sendForm(actionFactory.page().command().createLegacy(ctype, params.pcid,
            params.hierid));
          form_sent = true;
        }
        // generic
        if (["Grid", "MediaObject", "Section"].includes(model.getCurrentPCName())) {
          this.ui.showGenericCreationForm();
        }
        break;

      case "component.cancel":
        if (model.getComponentState() === model.STATE_COMPONENT_INSERT) {
          if (model.getCurrentPCName() !== "Paragraph") {
            const pcid = model.getCurrentPCId();
            this.ui.removeInsertedComponent(pcid);
          }
        }
        break;

      case "component.save":
        this.sendInsertCommand(params);
        break;

      case "component.edit":
        if (model.getCurrentPCName() !== "Paragraph") {
          client.sendForm(actionFactory.page().command().editLegacy(params.cname, params.pcid,
            params.hierid));
          form_sent = true;
        }
        break;

      case ACTIONS.SWITCH_MULTI:
      case ACTIONS.SWITCH_SINGLE:
        this.ui.refreshModeSelector();
        this.ui.highlightSelected(model.getSelected());
        break;

      case "multi.toggle":
        this.ui.highlightSelected(model.getSelected());
        break;

      case "multi.paste":
        this.sendPasteCommand(model, params);
        break;

      case ACTIONS.DND_DROP:
        this.sendDropCommand(params);
        break;

      case "multi.action":
        let type = params.type;

        if (["all", "none", "cut", "copy"].includes(type)) {
          this.ui.highlightSelected(model.getSelected());
        }
        switch (type) {
          case "cut":
            this.ui.pageModifier.cut(model.getCutItems());
            break;

          case "characteristic":
            this.ui.initFormatButtons();
            break;

          case "delete":
            this.ui.showDeleteConfirmation();
            break;

          case "activate":
            this.sendActivateCommand();
            break;
        }
        break;

      case "format.paragraph":
        this.ui.setParagraphFormat(model.getParagraphFormat());
        break;

      case "format.section":
        this.ui.setSectionFormat(model.getSectionFormat());
        break;

      case "format.save":
        this.sendFormatCommand(params);
        break;

      case "multi.delete":
        this.ui.hideDeleteConfirmation();
        this.sendDeleteCommand(params);
        break;

      case "multi.activate":
        this.sendActivateCommand(params);
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
          this.ui.showEditPage();
          this.ui.showAddButtons();
          this.ui.hideDropareas();
          this.ui.enableDragDrop();
          break;

        case model.STATE_MULTI_ACTION:
          if ([model.STATE_MULTI_CUT, model.STATE_MULTI_COPY].includes(model.getMultiState())) {
            this.ui.showAddButtons();
          } else {
            this.ui.hideAddButtons();
          }
          this.ui.showMultiButtons();
          this.ui.hideDropareas();
          this.ui.disableDragDrop();
          break;

        case model.STATE_DRAG_DROP:
          this.ui.showEditPage();
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

  sendPasteCommand(model, params) {
    let paste_action;
    const af = this.actionFactory;

    if (params.mode === model.STATE_MULTI_CUT) {

      const cutPcIds = Array.from(
        model.getCutItems()).map(x => (x.split(":")[1])
      );

      paste_action = af.page().command().cutPaste(
        cutPcIds,
        params.pcid,
      );
    } else if (params.mode === model.STATE_MULTI_COPY) {
      const copyPcIds = Array.from(
        model.getCopyItems()).map(x => (x.split(":")[1])
      );

      paste_action = af.page().command().copyPaste(
        copyPcIds,
        params.pcid,
      );
    }

    this.client.sendCommand(paste_action).then(result => {
      this.ui.handlePageReloadResponse(result);
    });

  }

  sendDropCommand(params) {
    let drop_action;
    const af = this.actionFactory;

    drop_action = af.page().command().dragDrop(
      params.target,
      params.source
    );

    this.client.sendCommand(drop_action).then(result => {
      this.ui.handlePageReloadResponse(result);
    });
  }

  sendFormatCommand(params) {
    let drop_action;
    const af = this.actionFactory;
    const pcids = Array.from(
      params.pcids).map(x => (x.split(":")[1])
    );

    drop_action = af.page().command().format(
      pcids,
      params.parFormat,
      params.secFormat
    );

    this.client.sendCommand(drop_action).then(result => {
      this.ui.handlePageReloadResponse(result);
    });
  }

  sendDeleteCommand(params) {
    let delete_action;
    const af = this.actionFactory;
    const pcids = Array.from(
      params.pcids).map(x => (x.split(":")[1])
    );

    delete_action = af.page().command().delete(
      pcids
    );

    this.client.sendCommand(delete_action).then(result => {
      this.ui.handlePageReloadResponse(result);
    });
  }

  sendActivateCommand(params) {
    let activate_action;
    const af = this.actionFactory;
    const pcids = Array.from(
      params.pcids).map(x => (x.split(":")[1])
    );

    activate_action = af.page().command().activate(
      pcids
    );

    this.client.sendCommand(activate_action).then(result => {
      this.ui.handlePageReloadResponse(result);
    });
  }

  sendInsertCommand(params) {
    let insert_action;
    const af = this.actionFactory;

    insert_action = af.page().command().insert(
      params.afterPcid,
      params.pcid,
      params.component,
      params.data
  );

    this.client.sendCommand(insert_action).then(result => {
      this.ui.handlePageReloadResponse(result);
    });
  }

}