
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

import Handle from "./handle.js";

/**
 * Shape
 */
export default class Shape {

    /**
     * @param Handle[] coords
     */
    constructor(handles = []) {
        this.handles = handles;
    }

    /**
     * @return Handle[]
     */
    getHandles() {
        return this.handles;
    }

    getStyle () {
        return "stroke:red; stroke-width:1; fill:none;";
    }

    createSvgElement(name) {
        return document.createElementNS("http://www.w3.org/2000/svg", name);
    }

    getElementId (nr) {
        return "il-copg-iim-shape-" + nr;
    }

    /**
     * @param int nr
     */
    addToSvg(nr, svg) {
    }

}
