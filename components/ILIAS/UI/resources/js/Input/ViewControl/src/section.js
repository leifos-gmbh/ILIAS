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
 */

export default class Section {
  /**
   * @type {JQueryEventDispatcher}
   */
  #eventDispatcher;

  /**
   * @param {JQueryEventDispatcher} eventDispatcher
   */
  constructor(eventDispatcher) {
    this.#eventDispatcher = eventDispatcher;
  }

  /**
   * @param {HTMLElement} component
   * @param {string} internalSignal
   * @param {string} containerSubmitSignal
   * @return {void}
   */
  init(component, internalSignal, containerSubmitSignal) {
    const select = component.querySelector('select');
    select.addEventListener('change', (event) => {
      this.#eventDispatcher.dispatch(event.target, internalSignal);
    });

    this.#eventDispatcher.register(
      component.ownerDocument,
      internalSignal,
      (event, signalData) => {
        if (signalData.options.value !== undefined) {
          select.value = signalData.options.value;
        }
        this.#eventDispatcher.dispatch(event.target, containerSubmitSignal);
        return false;
      },
    );
  }
}