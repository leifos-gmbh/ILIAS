/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "./paragraph-action-types.js";

/**
 * COPage command actions being sent to the server
 */
export default class ParagraphCommandActionFactory {

  /**
   * @type {ClientActionFactory}
   */
  clientActionFactory;

  COMPONENT = "Paragraph";

  /**
   * @param {ClientActionFactory} clientActionFactory
   */
  constructor(clientActionFactory) {
    this.clientActionFactory = clientActionFactory;
  }

  /**
   * @param after_pcid
   * @param pcid
   * @param content
   * @param characteristic
   * @return {CommandAction}
   */
  insert(after_pcid, pcid, content, characteristic) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.INSERT, {
      after_pcid: after_pcid,
      pcid: pcid,
      content: content,
      characteristic: characteristic
    });
  }

  /**
   * @param pcid
   * @param content
   * @param characteristic
   * @return {CommandAction}
   */
  update(pcid, content, characteristic) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.UPDATE, {
      pcid: pcid,
      content: content,
      characteristic: characteristic
    });
  }

  /**
   * @param pcid
   * @param content
   * @param characteristic
   * @return {CommandAction}
   */
  autoSave(pcid, content, characteristic) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.UPDATE_AUTO, {
      pcid: pcid,
      content: content,
      characteristic: characteristic
    });
  }

  /**
   * @param after_pcid
   * @param pcid
   * @param content
   * @param characteristic
   * @return {CommandAction}
   */
  autoInsert(after_pcid, pcid, content, characteristic) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.INSERT_AUTO, {
      after_pcid: after_pcid,
      pcid: pcid,
      content: content,
      characteristic: characteristic
    });
  }


}