/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    switch (action.getType()) {

      case "par.cancel":
        this.pageModel.setState(this.pageModel.STATE_PAGE);
        break;
    }
  }
}