/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Controller (handles editor initialisation process)
 */
export default class PageModel {

  STATE_PAGE = "page";                  // page editing
  STATE_DRAG_DROP = "drag_drop";        // drag drop
  STATE_COMPONENT = "component";        // component editing (in slate)
  STATE_MULTI_ACTION = "multi";         // multi action

  /**
   *
   * @type {*[]}
   */
  states = [];

  dom;

  /**
   * @type {Object}
   */
  model = {
    state: this.STATE_PAGE,
    selectedItems: new Set(),
    currentPCID: null,
    currentHierId: null,
    page_components: []
  };

  constructor() {
    this.dom = document;
    this.states = [this.STATE_PAGE, this.STATE_DRAG_DROP, this.STATE_COMPONENT, this.STATE_MULTI_ACTION ];
  }

  /**
   * @param {string} state
   */
  setState(state) {
    if (this.states.includes(state)) {
      this.model.state = state;
    }
  }

  /**
   * @return {string}
   */
  getState() {
    return this.model.state;
  }

  /**
   *
   * @param {string} pcid
   * @param {string} hierid
   */
  toggleSelect(pcid, hierid) {
    const key = hierid + ":" + pcid;
    if (this.model.selectedItems.has(key)) {
      this.model.selectedItems.delete(key);
    } else {
      this.model.selectedItems.add(key);
    }
  }

  selectNone() {
    this.model.selectedItems.clear();
  }

  selectAll() {
    let key;
    this.dom.querySelectorAll("[data-copg-ed-type='pc-area']").forEach(pc_area => {
      key = pc_area.dataset.hierid + ":" + pc_area.dataset.pcid;
      this.model.selectedItems.add(key);
    });
  }

  /**
   * Do we have selected items?
   * @return {boolean}
   */
  hasSelected() {
    return (this.model.selectedItems.size  > 0);
  }

  /**
   * Get all selected items
   * @return {Set<string>}
   */
  getSelected() {
    return this.model.selectedItems;
  }

  /**
   * @param {string} pcid
   * @param {string} hierid
   */
  setCurrentPageComponent(pcid, hierid = "") {
    this.model.currentPCID = pcid;
    this.model.currentHierId = hierid;
  }

  /**
   * @return {string}
   */
  getCurrentPCId() {
    return this.model.currentPCID;
  }

  /**
   * @return {string}
   */
  getCurrenntHierId() {
    return this.model.currentHierId;
  }

  /**
   * @param {[]} pc_model
   */
  setComponentModel(pc_model) {
    this.model.page_components = pc_model;
  }

  /**
   * @param {string} pcid
   * @return {null|Object}
   */
  getPCModel(pcid) {
    if (pcid in this.model.page_components) {
      return this.model.page_components[pcid];
    }
    return null;
  }

}