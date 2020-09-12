/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "./page-action-types.js";

/**
 * COPage command actions being sent to the server
 */
export default class PageCommandActionFactory {

  COMPONENT = "Page";

  /**
   * @type {ClientActionFactory}
   */
  clientActionFactory;

  /**
   * @param {ClientActionFactory} clientActionFactory
   */
  constructor(clientActionFactory) {
    this.clientActionFactory = clientActionFactory;
  }

  /**
   * @param {string} ctype
   * @param {string} pcid
   * @param {string} hier_id
   * @return {CommandAction}
   */
  createLegacy(ctype, pcid, hier_id) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.CREATE_LEGACY, {
      cmd: "insert",
      ctype: ctype,
      pcid: pcid,
      hier_id: hier_id
    });
  }

  /**
   * @param {string} cname
   * @param {string} pcid
   * @param {string} hier_id
   * @return {CommandAction}
   */
  editLegacy(cname, pcid, hier_id) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.EDIT_LEGACY, {
      cmd: "edit",
      cname: cname,
      pcid: pcid,
      hier_id: hier_id
    });
  }

  /**
   * @param {string} type
   * @param {[]} ids
   * @return {CommandAction}
   */
  multiLegacy(type, ids) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.MULTI_LEGACY, {
      cmd: type,
      ids: ids
    });
  }
}