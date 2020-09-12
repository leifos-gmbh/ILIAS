/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Page modifier is an adapter for components to
 *
 */
export default class PageModifier {

  /**
   *
   * @type {PageUI}
   */
  pageUI = null;

  /**
   */
  constructor() {
  }

  setPageUI(pageUI) {
    this.pageUI = pageUI;
  }

  /**
   *
   * @param {string} after_pcid
   * @param {string} after_hierid
   * @param {string} pcid
   * @param {string} cname
   * @param {string} content
   * @param {string} label
   */
  insertComponentAfter(after_pcid, pcid, cname, content, label) {
    const addArea = document.querySelector("[data-copg-ed-type='add-area'][data-pcid='" + after_pcid + "']");
    let d = document.createElement("div");

    // insert after addArea
    addArea.parentNode.insertBefore(d, addArea.nextSibling);
    d.innerHTML =
      '<div data-copg-ed-type="pc-area" class="il_editarea" id="CONTENT:' + pcid + '"  data-pcid="' + pcid + '" data-cname="' + cname + '"><div class="ilEditLabel">' + label + '<!--Dummy--></div>' + content + '</div></div>' +
      '<div data-copg-ed-type="add-area" data-pcid="' + pcid + '"></div>';

    let addSelector = "[data-copg-ed-type='add-area'][data-pcid='" + pcid + "']";
    let pcSelector = "[data-copg-ed-type='pc-area'][data-pcid='" + pcid + "']";

    this.pageUI.initComponentClick(pcSelector);
    this.pageUI.initAddButtons(addSelector);
    this.pageUI.initDragDrop(pcSelector, addSelector + " .il_droparea");
    this.pageUI.initMultiSelection(pcSelector);
    this.pageUI.initComponentEditing(pcSelector);

    this.pageUI.hideAddButtons();
    this.pageUI.hideDropareas();


  }


}
