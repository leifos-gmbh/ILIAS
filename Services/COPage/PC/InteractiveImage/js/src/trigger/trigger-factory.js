
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

import Trigger from "./trigger.js";
import AreaFactory from "../area/area-factory.js"

/**
 * Shape
 */
export default class TriggerFactory {

  constructor() {
    this.areaFactory = new AreaFactory();
  }

  trigger(
    nr,
    markerX,
    markerY,
    overlay,
    popupNr,
    popupAlign,
    title,
    area
  ) {
    return new Trigger(
      nr,
      area,
      markerX,
      markerY,
      overlay,
      '',
      '',
      popupNr,
      popupAlign,
      title
    );
  }

  /**
   */
  fromPropertiesObject(o, area = null) {
    return new Trigger(
      o.Nr,
      area,
      o.MarkerX,
      o.MarkerY,
      o.Overlay,
      o.PopupNr,
      o.PopupAlign,
      o.Title
    );
  }

  fullTriggerFromModel(nr, model) {
    let trigger = null;
    model.triggers.forEach((tr) => {
      if (tr.Nr == nr) {
        const area = this.areaFactory.fromModelForId(tr.Nr, model);
        trigger = this.fromPropertiesObject(tr, area);
      }
    });
    return trigger;
  }

}
