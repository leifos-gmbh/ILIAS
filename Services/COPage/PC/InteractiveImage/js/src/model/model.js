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

/**
 * Interactive Image Model
 */
export default class Model {

  constructor() {
    this.debug = true;

    this.STATE_OVERVIEW = "overview";                 // overview
    this.STATE_TRIGGER_PROPERTIES = "trigger_prop";   // drag drop

    this.model = {
      state: this.STATE_PAGE,
      selectedItems: new Set(),
    };
    this.states = [
      this.STATE_OVERVIEW,
      this.STATE_TRIGGER_PROPERTIES,
    ];
  }

  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  /**
   * @param {string} state
   */
  setState(state) {
    if (this.states.includes(state)) {
      this.log("page-model.setState " + state);
      this.model.state = state;
    }
  }

  /**
   * @return {string}
   */
  getState() {
    return this.model.state;
  }

}