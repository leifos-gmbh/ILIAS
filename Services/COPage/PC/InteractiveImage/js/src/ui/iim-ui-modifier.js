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

/**
 * Page modifier is an adapter for components to
 *
 */
export default class IIMUIModifier {

  /**
   *
   * @type {PageUI}
   */
  //pageUI = null;

  /**
   * @param {ToolSlate} toolSlate
   */
  constructor() {
  }

  setUIModel(uiModel) {
    this.uiModel = uiModel;
  }

  showModal(title, content, button_txt, onclick) {
    const uiModel = this.uiModel;

    $("#il-copg-ed-modal").remove();
    let modal_template = uiModel.modal.template;
    modal_template = modal_template.replace("#title#", title);
    modal_template = modal_template.replace("#content#", content);
    modal_template = modal_template.replace("#button_title#", button_txt);

    $("body").append("<div id='il-copg-ed-modal'>" + modal_template + "</div>");

    $(document).trigger(
      uiModel.modal.signal,
      {
        'id': uiModel.modal.signal,
        'triggerer': $(this),
        'options': JSON.parse('[]')
      }
    );

    if (button_txt) {
      const b = document.querySelector("#il-copg-ed-modal .modal-footer button");
      b.addEventListener("click", onclick);
    } else {
      document.querySelectorAll("#il-copg-ed-modal .modal-footer").forEach((b) => {
        b.remove();
      });
    }
  }
}
