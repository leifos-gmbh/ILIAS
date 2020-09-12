/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "../actions/paragraph-action-types.js";

/**
 * Paragraph model action handler
 */
export default class ParagraphModelActionHandler {

  /**
   * {Model}
   */
  pageModel;

  /**
   *
   * @param {Model} model
   */
  constructor(pageModel) {
    this.pageModel = pageModel;
  }


  /**
   * @param {EditorAction} action
   */
  handle(action) {

    const params = action.getParams();

    if (action.getComponent() === "Paragraph") {

      switch (action.getType()) {

        case ACTIONS.PARAGRAPH_CLASS:
          const pcmodel = this.pageModel.getPCModel(this.pageModel.getCurrentPCId());
          if (pcmodel) {
            pcmodel.characteristic = params.characteristic;
          }
          this.pageModel.setPCModel(this.pageModel.getCurrentPCId(), pcmodel);
          break;

        case ACTIONS.SAVE_RETURN:
          this.pageModel.setState(this.pageModel.STATE_PAGE);
          this.pageModel.setPCModel(this.pageModel.getCurrentPCId(), {
            text: params.text,
            characteristic: params.characteristic
          });
          // note: we keep the component state and current component here, so that handlers
          // can use this
          break;

      }
    }
  }
}