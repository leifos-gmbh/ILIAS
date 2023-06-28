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

import ACTIONS from "../actions/iim-action-types.js";
import Util from "../../../../../Editor/js/src/ui/util.js";
import ShapeEditor from "../shape-edit/shape-editor.js";
import ActionFactory from "../actions/iim-editor-action-factory.js";

/**
 * interactive image ui
 */
export default class UI {


  /**
   * @type {boolean}
   */
  //debug = true;

  /**
   * Model
   * @type {PageModel}
   */
  //page_model = {};

  /**
   * UI model
   * @type {Object}
   */
  //uiModel = {};

  /**
   * @type {Client}
   */
  //client;

  /**
   * @type {Dispatcher}
   */
  //dispatcher;

  /**
   * @type {ActionFactory}
   */
  //actionFactory;

  /**
   * @type {ToolSlate}
   */
  //toolSlate;

  /**
   * @type {pageModifier}
   */
  //  pageModifier;


  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {IIMModel} page_model
   * @param {ToolSlate} toolSlate
   * @param {PageModifier} pageModifier
   */
  constructor(client, dispatcher, actionFactory, iimModel, uiModel, toolSlate) {
    this.debug = true;
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.iim_model = iimModel;
    this.toolSlate = toolSlate;
    this.uiModel = uiModel;
    this.util = new Util();
    this.shapeEditor = null;
  }

  //
  // Initialisation
  //

  /**
   * @param message
   */
  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }


  /**
   */
  init(uiModel) {
    const action = this.actionFactory;
    const dispatch = this.dispatcher;

    this.uiModel = uiModel;
    let t = this;
    this.showMainScreen();
  }

  /**
   */
  reInit() {
  }

  showMainScreen() {
    this.toolSlate.setContent(this.uiModel.mainSlate);
    this.initSlateActions();
    this.setMainContent(this.uiModel.backgroundImage);
    this.initShapeEditor();
  }

  initSlateActions() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='button']").forEach(button => {
      const act = button.dataset.copgEdAction;
      button.addEventListener("click", (event) => {
        switch (act) {
          case ACTIONS.E_ADD_TRIGGER:
            this.log(action);
            dispatch.dispatch(action.interactiveImage().editor().addTrigger());
            break;
        }
      });
    });
  }


  initShapeEditor() {
    const el = document.getElementById('il-copg-iim-main');
    const mob = el.querySelector(".ilc_Mob");
    console.log("initShapeEditor");
    if (mob) {
      this.shapeEditor = new ShapeEditor(mob);
      const ed = this.shapeEditor;
      //ed.addShape(ed.factory.rect(10,10, 200, 200));
      //ed.addShape(ed.factory.circle(210,210, 230,230));
      //const p = ed.factory.poly();
      //p.addHandle(ed.factory.handle(20,20));
      //p.addHandle(ed.factory.handle(30,200));
      //p.addHandle(ed.factory.handle(110,70));
      //p.addHandle(ed.factory.handle(60,30));
      //ed.addShape(p);
      //ed.repaint();
    }
  }

  setMainContent(html) {
    const el = document.getElementById('il-copg-iim-main');
    this.util.setInnerHTML(el, html);
  }

  addTrigger() {
    const trigger = this.iim_model.getCurrentTrigger();
    this.toolSlate.setContent("Trigger Properties");
    this.shapeEditor.addShape(trigger.getShape());
    this.shapeEditor.repaint();
  }

  /*
  showPlaceholder(pcid) {
    this.pageModifier.showComponent(pcid);
  }
  */
}
