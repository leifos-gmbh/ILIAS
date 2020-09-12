/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Controller (handles editor initialisation process)
 */
export default class PageModel {

  debug = true;

  STATE_PAGE = "page";                  // page editing
  STATE_DRAG_DROP = "drag_drop";        // drag drop
  STATE_COMPONENT = "component";        // component editing (in slate)
  STATE_MULTI_ACTION = "multi";         // multi action

  STATE_COMPONENT_EDIT = "edit";        // component editing
  STATE_COMPONENT_INSERT = "insert";    // component inserting
  STATE_COMPONENT_NONE = "";

  /**
   * @type {*[]}
   */
  states = [];

  /**
   * @type {*[]}
   */
  component_states = [];

  dom;

  /**
   * @type {Object}
   */
  model = {
    state: this.STATE_PAGE,
    component_state: this.STATE_COMPONENT_NONE,
    selectedItems: new Set(),
    currentPCID: null,
    currentHierId: null,
    currentInsertPCID: null,
    page_components: [],
    page_components_undo: []
  };

  constructor() {
    this.dom = document;
    this.states = [this.STATE_PAGE, this.STATE_DRAG_DROP, this.STATE_COMPONENT, this.STATE_MULTI_ACTION];
    this.component_states = [this.STATE_COMPONENT_NONE, this.STATE_COMPONENT_EDIT, this.STATE_COMPONENT_INSERT];
  }

  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  /**
   * @param {string} state
   */
  setState(state) {
    if (this.states.includes(state)) {
      this.log("page-model.setState " + state);
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
   * @param {string} state
   */
  setComponentState(state) {
    if (this.component_states.includes(state)) {
      this.log("page-model.setComponentState " + state);
      this.model.component_state = state;
    }
  }

  /**
   * @return {string}
   */
  getComponentState() {
    return this.model.component_state;
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
   * @param {string} cname
   * @param {string} pcid
   * @param {string} hierid
   */
  setCurrentPageComponent(cname, pcid, hierid = "") {
    this.model.currentPCName = cname;
    this.model.currentPCID = pcid;
    this.model.currentHierId = hierid;
  }

  /**
   * @return {string}
   */
  getCurrentPCName() {
    return this.model.currentPCName;
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
   * @param {string} pcid
   */
  setCurrentInsertPCId(pcid) {
    this.model.currentInsertPCID = pcid;
  }

  /**
   * @return {string}
   */
  getCurrentInsertPCId() {
    return this.model.currentInsertPCID;
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

  /**
   *
   * @param {string} pcid
   * @param {Object} model
   */
  setPCModel(pcid, model) {
    this.model.page_components[pcid] = model;
  }

  /**
   * @param {string} pcid
   * @return {null|Object}
   */
  getUndoPCModel(pcid) {
    this.log("getUndoPCModel");
    if (pcid in this.model.page_components_undo) {
      this.log(pcid);
      this.log(this.model.page_components_undo[pcid]);
      return this.model.page_components_undo[pcid];
    }
    return null;
  }

  /**
   *
   * @param {string} pcid
   * @param {Object} model
   */
  setUndoPCModel(pcid, model) {
    // note: JSON is used here to create a deep copy, there might be better ways with libs (lodash, ...)
    this.model.page_components_undo[pcid] = JSON.parse(JSON.stringify(model));
  }

  undoPCModel(pcid) {
    const undo_model = this.getUndoPCModel(pcid);
    if (undo_model) {
      this.model.page_components[pcid] = JSON.parse(JSON.stringify(undo_model));
    }
  }


  getNewPCId() {
    let vals = new Uint32Array(1);
    window.crypto.getRandomValues(vals);
    return vals[0];
  }

}