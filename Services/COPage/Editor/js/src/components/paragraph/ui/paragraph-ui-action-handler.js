/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "../actions/paragraph-action-types.js";
import PAGE_ACTIONS from "../../page/actions/page-action-types.js";

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
   * @param {PageModel} page_model
   */
  handle(action, page_model) {

    const dispatcher = this.dispatcher;
    const actionFactory = this.actionFactory;
    const client = this.client;
    let form_sent = false;

    const params = action.getParams();

    // page actions
    if (action.getComponent() === "Page" && page_model.getCurrentPCName() === "Paragraph") {
      switch (action.getType()) {

        case PAGE_ACTIONS.COMPONENT_INSERT:
          this.ui.insertParagraph(page_model.getCurrentPCId(), page_model.getCurrentInsertPCId());
          break;

        case PAGE_ACTIONS.COMPONENT_EDIT:
          this.ui.editParagraph(page_model.getCurrentPCId());
          break;

        case PAGE_ACTIONS.COMPONENT_CANCEL:
          this.ui.cmdCancel();
          break;

        case PAGE_ACTIONS.COMPONENT_SWITCH:
          if (params.oldComponentState === page_model.STATE_COMPONENT_INSERT) {
            this.sendInsertCommand(
              params.oldPcid,
              page_model.getCurrentInsertPCId(),
              page_model.getPCModel(params.oldPcid),
              page_model
            );
            this.ui.handleSaveOnInsert();
          } else {
            this.sendUpdateCommand(
              params.oldPcid,
              page_model.getPCModel(params.oldPcid),
              page_model
            );
            this.ui.handleSaveOnEdit();
          }
          this.ui.editParagraph(page_model.getCurrentPCId());
          break;
      }
    }

    if (action.getComponent() === "Paragraph") {
      switch (action.getType()) {


        case ACTIONS.PARAGRAPH_CLASS:
          this.ui.setParagraphClass(params.characteristic);
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

        case ACTIONS.SAVE_RETURN:
          if (page_model.getComponentState() === page_model.STATE_COMPONENT_INSERT) {
            this.sendInsertCommand(
              page_model.getCurrentPCId(),
              page_model.getCurrentInsertPCId(),
              page_model.getPCModel(page_model.getCurrentPCId()),
              page_model
            );
            this.ui.handleSaveOnInsert();
          } else {
            this.sendUpdateCommand(
              page_model.getCurrentPCId(),
              page_model.getPCModel(page_model.getCurrentPCId()),
              page_model
            );
            this.ui.handleSaveOnEdit();
          }
          break;

        case ACTIONS.AUTO_SAVE:
          if (page_model.getState() === page_model.STATE_COMPONENT) {
            if (page_model.getComponentState() === page_model.STATE_COMPONENT_INSERT) {
              this.sendAutoInsertCommand(
                page_model.getCurrentPCId(),
                page_model.getCurrentInsertPCId(),
                page_model.getPCModel(page_model.getCurrentPCId()),
                page_model
              );
            } else {
              this.sendAutoSaveCommand(
                page_model.getCurrentPCId(),
                page_model.getPCModel(page_model.getCurrentPCId()),
                page_model
              );
            }
          }
          break;

      }
    }
  }

  sendInsertCommand(pcid, target_pcid, pcmodel, page_model) {
    const af = this.actionFactory;
    const insert_action = af.paragraph().command().insert(
      target_pcid,
      pcid,
      pcmodel.text,
      pcmodel.characteristic
    );
    this.client.sendCommand(insert_action).then(result => {
      const pl = result.getPayload();
      this.handleSaveResponse(pcid, pl);
    });
  }

  sendAutoInsertCommand(pcid, target_pcid, pcmodel, page_model) {
    const af = this.actionFactory;
    const dispatch = this.dispatcher;
    const insert_action = af.paragraph().command().autoInsert(
      target_pcid,
      pcid,
      pcmodel.text,
      pcmodel.characteristic
    );
    this.ui.autoSaveStarted();
    this.client.sendCommand(insert_action).then(result => {
      this.ui.autoSaveEnded();
      const pl = result.getPayload();

      dispatch.dispatch(af.paragraph().editor().autoInsertPostProcessing());

      this.handleSaveResponse(pcid, pl, page_model);
    });
  }

  sendUpdateCommand(pcid, pcmodel, page_model) {
    const af = this.actionFactory;
    const update_action = af.paragraph().command().update(
      pcid,
      pcmodel.text,
      pcmodel.characteristic
    );
    console.log(this.client);
    this.client.sendCommand(update_action).then(result => {
      const pl = result.getPayload();
      this.handleSaveResponse(pcid, pl, page_model);
    });
  }

  sendAutoSaveCommand(pcid, pcmodel, page_model) {
    const af = this.actionFactory;
    const auto_save_action = af.paragraph().command().autoSave(
      pcid,
      pcmodel.text,
      pcmodel.characteristic
    );
    this.ui.autoSaveStarted();
    this.client.sendCommand(auto_save_action).then(result => {
      this.ui.autoSaveEnded();
      const pl = result.getPayload();
      this.handleSaveResponse(pcid, pl, page_model);
    });
  }

  handleSaveResponse(pcid, pl, page_model) {
    const still_editing = (pcid === page_model.getCurrentPCId() && page_model.getState() === page_model.STATE_COMPONENT);
    if (pl.renderedContent && !still_editing) {
      this.ui.replaceRenderedParagraph(pcid, pl.renderedContent);
    }
    if (pl.last_update && still_editing) {
      this.ui.showLastUpdate(pl.last_update);
    }
  }


}