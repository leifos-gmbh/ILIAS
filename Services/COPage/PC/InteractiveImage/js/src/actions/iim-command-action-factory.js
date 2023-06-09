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

import ACTIONS from "./iim-action-types.js";

/**
 * COPage command actions being sent to the server
 */
export default class IIMCommandActionFactory {

  /**
   * @type {ClientActionFactory}
   */
  //clientActionFactory;


  /**
   * @param {ClientActionFactory} clientActionFactory
   */
  constructor(clientActionFactory) {
    this.COMPONENT = "InteractiveImage";
    this.clientActionFactory = clientActionFactory;
  }

  /**
   * @param pcid
   * @param content
   * @param redirect
   * @return {CommandAction}
   */
  updateData(pcid, content, redirect) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.UPDATE_DATA, {
      pcid: pcid,
      content: content,
      redirect: redirect
    });
  }
}