
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

import ShapeFactory from "../shape-edit/shape-factory.js";

/**
 * Area
 */
export default class Area {

    /**
     */
    constructor(
      shapeType,
      coords,
      hClass,
      hMode,
      id = 0,
      link = null
    ) {
        this.shapeType = shapeType;
        this.coords = coords;
        this.hClass = hClass;
        this.hMode = hMode;
        this.overlayY = '';
        this.id = id;
        this.link = link;
        this.shapeFactory = new ShapeFactory();
    }

    toPropertiesObject(nr) {
        const link = (this.link === null)
            ? null
            : this.link.toPropertiesObject();
        return {
            Coords: this.coords,
            HighlightClass: this.hClass,
            HighlightMode: this.hMode,
            Id: this.id,
            Link: link,
            Nr: nr,
            Shape: this.shapeType
        };
    }

    getShape(triggerNr = null) {
        const coords = this.coords.split(",");
        switch (this.shapeType) {
            case "Rect":
                return this.shapeFactory.rect(
                  parseInt(coords[0]),
                  parseInt(coords[1]),
                  parseInt(coords[2]),
                  parseInt(coords[3]),
                  {triggerNr : triggerNr, copgEdType : 'shape'}
                );
            case "Circle":
                return this.shapeFactory.circle(
                  parseInt(coords[0]),
                  parseInt(coords[1]),
                  parseInt(coords[0]) + parseInt(coords[2]),
                  parseInt(coords[1]),
                  {triggerNr : triggerNr, copgEdType : 'shape'}
                );
            case "Poly":
                let co = [];
                coords.forEach((c) => {
                    co.push(parseInt(c));
                });
                return this.shapeFactory.poly(
                  co,
                  {
                      triggerNr : triggerNr,
                      copgEdType : 'shape'
                  });
        }
        return null;
    }
}
