
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

import ShapeFactory from "./shape-factory.js";

/**
 * Circle
 */
export default class ShapeEditor {

    /**
     * @param Handle center
     * @param Handle point
     */
    constructor(mobElement) {
        this.mobElement = mobElement;
        this.shapes = [];
        this.currentShape = null;
        this.factory = new ShapeFactory();
    }

    factory() {
        return this.factory;
    }

    addShape(shape) {
        if (!shape) {
            shape = this.factory.rect(10,10,50,50);
        }
        this.shapes.push(shape);
        this.currentShape = this.shapes.length - 1;
    }

    removeAllChilds(node) {
        while (node.firstChild) {
            node.removeChild(node.lastChild);
        }
    }

    removeAllChildsOfName(node, name) {
        node.querySelectorAll(name).forEach(n => n.remove());
    }

    getSvg() {
        let svg = document.getElementById("il-copg-iim-svg");
        if (!svg) {
            svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            svg.id = "il-copg-iim-svg";
            svg.style.position = "absolute";
            svg.style.left = "0px";
            svg.style.top = "0px";
            svg.style.width = "100%";
            svg.style.height = "100%";
            this.mobElement.appendChild(svg);
        };
        return svg;
    }

    repaint() {
        this.repaintSvg();
        this.shapes[this.currentShape].getHandles().forEach((h) => {
            h.addHandleToMobElement(this.mobElement);
            h.setOnDrag(() => {
                this.repaintSvg();
            });
        });
    }

    repaintSvg() {
        const svg = this.getSvg();
        this.removeAllChilds(svg);
        let cnt = 0;
        this.shapes.forEach((shape) => {
            shape.addToSvg(cnt++, svg);
        });
    }

}
