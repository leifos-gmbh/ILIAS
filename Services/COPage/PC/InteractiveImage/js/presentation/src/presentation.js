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

import Util from "../../common/src/util.js";
import Area from "../../editor/src/area/area.js";
import AreaFactory from "../../editor/src/area/area-factory.js";

const presentation = (function () {

  function init(node) {
    let iimId;
    let mobEl;
    let areaEl;
    let svg;
    //let popupEl;
    let popupNr;
    const util = new Util;
    const areaFactory = new AreaFactory();
    let topContainer;

    // find all triggers within the node
    node.querySelectorAll("[data-copg-iim-data-type='trigger']").forEach((tr) => {
      topContainer = tr.closest('.ilc_page_cont_PageContainer');
      // get map area of trigger
      iimId = tr.getAttribute("data-copg-iim-tr-id");
      popupNr = tr.getAttribute("data-copg-iim-popup-nr");
      mobEl = tr.parentNode.querySelector(".ilc_Mob");
      areaEl = document.getElementById("marea_" + iimId);
      svg = util.getOverlaySvg(mobEl);
      console.log("----");
      console.log(iimId);
      console.log(popupNr);
      let popupEl = null;
      if (popupNr) {
        popupEl = mobEl.parentNode.parentNode.parentNode.querySelector("[data-copg-cont-type='iim-popup'][data-copg-popup-nr='" + popupNr + "']");
      }

      if (areaEl) {
        console.log(areaEl.getAttribute("shape"));
        console.log(areaEl.getAttribute("coords"));
        console.log(popupEl);

        const area = areaFactory.area(
          areaEl.getAttribute("shape"),
          areaEl.getAttribute("coords")
        );
        const shape = area.getShape();
        const shapeEl = shape.addToSvg(svg);

        // on click => toggle popup
        if (popupEl) {

          util.attachPopupToShape(topContainer, mobEl, popupEl, shapeEl);

          shapeEl.addEventListener("click", () => {
            util.lastClicked(popupEl, shapeEl);
            if (popupEl.style.display === "none") {
              popupEl.style.display = "";
              util.refreshPopupPosition(topContainer, mobEl, popupEl, shapeEl);
            } else {
              popupEl.style.display = "none";
            }
          });
        }
      }
    });

  }

  return {
    init
  };
})();
window.addEventListener('load', function () {
  presentation.init(document);
}, false);