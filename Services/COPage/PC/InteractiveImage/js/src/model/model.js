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

import AreaFactory from "../area/area-factory.js";
import TriggerFactory from "../trigger/trigger-factory.js";

/**
 * Interactive Image Model
 */
export default class Model {

  constructor() {
    this.debug = true;

    this.STATE_OVERVIEW = "overview";                 // overview
    this.STATE_TRIGGER_PROPERTIES = "trigger_prop";   // trigger properties
    this.STATE_TRIGGER_OVERLAY = "trigger_overlay";   // trigger overlay
    this.STATE_TRIGGER_POPUP = "trigger_popup";   // trigger popup
    this.STATE_SETTINGS = "settings";   // settings
    this.STATE_OVERLAYS = "overlays";   // settings
    this.STATE_POPUPS = "popups";   // settings

    this.model = {
      state: this.STATE_OVERVIEW,
      areaNr: 0,
      iim: null,
      currentTrigger: null
    };
    this.states = [
      this.STATE_OVERVIEW,
      this.STATE_TRIGGER_PROPERTIES,
      this.STATE_TRIGGER_OVERLAY,
      this.STATE_TRIGGER_POPUP,
      this.STATE_SETTINGS,
      this.STATE_OVERLAYS,
      this.STATE_POPUPS
    ];
    this.areaFactory = new AreaFactory();
    this.triggerFactory = new TriggerFactory();
  }

  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  /**
   * Note: area.Id = trigger.Nr
   */
  initModel(iimModel) {
    this.model.iim = iimModel;
  }

  /**
   * @param {string} state
   */
  setState(state) {
    if (this.states.includes(state)) {
      this.log("model.setState " + state);
      this.model.state = state;
    }
  }

  /**
   * @return {string}
   */
  getState() {
    return this.model.state;
  }

  getNextTriggerNr() {
    let maxNr = 0;
    this.model.iim.triggers.forEach((a) => {
      maxNr = Math.max(maxNr, a.Nr);
    });
    return maxNr + 1;
  }

  addStandardTrigger() {
    const area = this.areaFactory.area(
      "Rect",
      "10,10,50,50"
    );
    this.model.currentTrigger = this.triggerFactory.trigger(
      this.getNextTriggerNr(),
      area
    );
    this.log("addStandardTrigger");
  }

  setTriggerByNr(triggerNr) {
    this.model.currentTrigger = this.triggerFactory.fullTriggerFromModel(
      triggerNr,
      this.model.iim
    );
  }

  getCurrentTrigger() {
    return this.model.currentTrigger;
  }
}