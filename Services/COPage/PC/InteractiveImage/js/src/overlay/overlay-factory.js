
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

import Overlay from "./overlay.js";

/**
 * Overlay
 */
export default class OverlayFactory {

  constructor() {
  }

  overlay(
    x,
    y,
    src,
  ) {
    return new Overlay(
      x,
      y,
      src
    );
  }

  fromPropertiesObject(t) {
    return this.overlay(
      t.OverlayX,
      t.OverlayY,
      t.Overlay
    );
  }

  fromModelForNr(nr, model) {
    let overlay = null;
    model.triggers.forEach((t) => {
      if (t.Nr == nr) {
        overlay = this.fromPropertiesObject(t);
      }
    });
    return overlay;
  }

}
