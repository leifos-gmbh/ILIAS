
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
      overlay= "",
      popupNr= "",
      popupAlign= "",
      title= "",
    ) {
        this.nr = nr;
        this.markerX = markerX;
        this.markerY = markerY;
        this.overlay = overlay;
        this.overlayX = '';
        this.overlayY = '';
        this.popupNr = popupNr;
        this.popupAlign = popupAlign;
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
            Overlay: this.overlay,
            OverlayX: this.overlayX,
            OverlayY: this.overlayY,
            PopupHeight: '',
            PopupNr: this.popupNr,
            PopupWidth: '',
            PopupX: '',
            PopupY: '',
            PopupAlign: this.popupAlign,
            Title: this.title,
            Type: type
        };
    }

    getShape() {
        if (this.area){
            return this.area.getShape(this.nr);
        }
    }
}
