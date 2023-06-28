/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

import ACTIONS from "../actions/iim-action-types.js";


/**
 * Model action handler
 */
export default class ModelActionHandler {

  /**
   * {Model}
   */
  //model;

  /**
   *
   * @param {Model} model
   */
  constructor(model) {
    this.model = model;
  }


  /**
   * @return {Model}
   */
  getModel() {
    return this.model;
  }

  /**
   * @param {EditorAction} action
   */
  handle(action) {

    const params = action.getParams();

    switch (action.getType()) {

      case ACTIONS.E_ADD_TRIGGER:
        this.model.addStandardTrigger();
        this.model.setState(this.model.STATE_TRIGGER_PROPERTIES);
        break;
    }
  }

}