
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

import Overlay from "../overlay/overlay.js";

/**
 * Shape
 */
export default class Trigger {

    /**
     */
    constructor(
      nr,
      area = null,
      markerX = "",
      markerY= "",
      overlay= null,
      popupNr= "",
      popupPosition= "",
      title= "",
    ) {
        this.nr = nr;
        this.markerX = markerX;
        this.markerY = markerY;
        this.overlay = overlay;
        this.popupNr = popupNr;
        this.popupPosition = popupPosition;
        this.title = title;
        this.area = area;
    }


    toPropertiesObject() {
        const type = (this.area === null)
            ? 'Marker'
            : 'Area';
        return {
            MarkerX: this.markerX,
            MarkerY: this.markerY,
            Nr: this.nr,
            Overlay: this.overlay.getSrc(),
            OverlayX: this.overlay.getX(),
            OverlayY: this.overlay.getY(),
            PopupHeight: '',
            PopupNr: this.popupNr,
            PopupWidth: '',
            PopupX: '',
            PopupY: '',
            PopupPosition: this.popupPosition,
            Title: this.title,
            Type: type
        };
    }

    setArea(area) {
        this.area = area;
    }

    setOverlay(overlay) {
        this.overlay = overlay;
    }

    getShape() {
        if (this.area){
            return this.area.getShape(this.nr);
        }
    }

    getOverlay() {
        return this.overlay;
    }

    getPopupNr() {
        return this.popupNr;
    }

    getPopupPosition() {
        return this.popupPosition;
    }

}
