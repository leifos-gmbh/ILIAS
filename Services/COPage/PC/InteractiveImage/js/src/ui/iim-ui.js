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
import TriggerFactory from "../trigger/trigger-factory.js";
import Poly from "../shape-edit/poly.js";
import IIMUIModifier from "./iim-ui-modifier.js";

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
   * @param {IIMUIModifier} uiModifier
   */
  constructor(client, dispatcher, actionFactory, iimModel, uiModel, toolSlate, uiModifier) {
    this.debug = true;
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.iimModel = iimModel;
    this.toolSlate = toolSlate;
    this.uiModel = uiModel;
    this.util = new Util();
    this.shapeEditor = null;
    this.triggerFactory = new TriggerFactory();
    this.uiModifier = uiModifier;
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
    this.initMainScreenActions();
    this.setMainContent(this.uiModel.backgroundImage);
    this.initShapeEditor();
    this.showAllShapes();
  }

  initMainScreenActions() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='button']").forEach(button => {
      const act = button.dataset.copgEdAction;
      button.addEventListener("click", (event) => {
        switch (act) {
          case ACTIONS.E_ADD_TRIGGER:
            dispatch.dispatch(action.interactiveImage().editor().addTrigger());
            break;
        }
      });
    });
    document.querySelectorAll("[data-copg-ed-type='link']").forEach(button => {
      const act = button.dataset.copgEdAction;
      button.addEventListener("click", (event) => {
        switch (act) {
          case ACTIONS.E_SWITCH_SETTINGS:
            dispatch.dispatch(action.interactiveImage().editor().switchSettings());
            break;
          case ACTIONS.E_SWITCH_OVERLAYS:
            dispatch.dispatch(action.interactiveImage().editor().switchOverlays());
            break;
          case ACTIONS.E_SWITCH_POPUPS:
            dispatch.dispatch(action.interactiveImage().editor().switchPopups());
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

  showAllShapes() {
    const m = this.iimModel.model.iim;
    m.triggers.forEach((tr) => {
      const trigger = this.triggerFactory.fullTriggerFromModel(tr.Nr, m);
      if (trigger) {
        console.log("ADDING SHAPE");
        console.log(trigger.getShape());
        this.shapeEditor.addShape(trigger.getShape());
      }
    });
    this.shapeEditor.repaint();
    this.initShapes();
  }

  initShapes() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    document.querySelectorAll("[data-copg-ed-type='shape']").forEach(shape => {
      shape.addEventListener("click", (event) => {
          dispatch.dispatch(action.interactiveImage().editor().editTrigger(
            shape.dataset.triggerNr
          ));
      });
    });
  }

  setMainContent(html) {
    const el = document.getElementById('il-copg-iim-main');
    this.util.setInnerHTML(el, html);
  }

  addTrigger() {
    const trigger = this.iimModel.getCurrentTrigger();
    this.showTriggerProperties();
    this.shapeEditor.addShape(trigger.getShape());
    this.shapeEditor.repaint();
  }

  editTrigger(nr) {
    const trigger = this.iimModel.getCurrentTrigger();
    this.showTriggerProperties();
    this.setEditorAddMode();
    this.shapeEditor.removeAllShapes();
    this.shapeEditor.addShape(trigger.getShape(), true);
    this.shapeEditor.repaint();
  }

  setEditorAddMode () {
    const trigger = this.iimModel.getCurrentTrigger();
    this.shapeEditor.setAllowAdd(false);
    if (this.iimModel.getActionState() === this.iimModel.ACTION_STATE_ADD &&
      trigger.getShape() instanceof Poly) {
      this.shapeEditor.setAllowAdd(true);
    }
  }

  repaintTrigger() {
    const trigger = this.iimModel.getCurrentTrigger();
    this.setEditorAddMode();
    this.shapeEditor.removeAllShapes();
    this.shapeEditor.addShape(trigger.getShape(), true);
    this.shapeEditor.repaint();
  }

  showTriggerProperties() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    const tr = this.iimModel.getCurrentTrigger();
    this.toolSlate.setContent(this.uiModel.triggerProperties);
    this.setInputValueByName('#copg-iim-trigger-prop-form', 'form_input_1', tr.title);
    this.setInputValueByName('#copg-iim-trigger-prop-form', 'form_input_2', tr.area.shapeType);
    this.initTriggerViewControl();
    this.initBackButton();
    model = this.iimModel;
    document.querySelectorAll("form [data-copg-ed-type='form-button']").forEach(button => {
      const act = button.dataset.copgEdAction;
      button.addEventListener("click", (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_PROPERTIES_SAVE:
            event.preventDefault();
            dispatch.dispatch(action.interactiveImage().editor().saveTriggerProperties(
              model.getCurrentTrigger().nr,
              this.getInputValueByName('form_input_1'),
              this.getInputValueByName('form_input_2'),
              model.getCurrentTrigger().getShape().getAreaCoordsString()
            ));
            break;
        }
      });
    });
    document.querySelectorAll("form [name='form_input_2']").forEach(select => {
      select.addEventListener("change", (event) => {
        dispatch.dispatch(action.interactiveImage().editor().changeTriggerShape(
          this.getInputValueByName('form_input_2')
        ));
      });
    });
    this.showCurrentShape(true);
  }

  getInputValueByName(name) {
    const path = "#copg-iim-trigger-prop-form input[name='" + name + "'],select[name='" + name + "']";
    const el = document.querySelector(path);
    if (el) {
      return el.value;
    }
    return null;
  }

  setInputValueByName(sel, name, value) {
    const path = sel + " input[name='" + name + "'],select[name='" + name + "']";
    const el = document.querySelector(path);
    if (el) {
      console.log(":::");
      console.log(el);
      console.log(value);
      el.value = value;
    }
  }

  setSelectOptions(sel, name, options, selected = null) {
    let op;
    const path = sel + " select[name='" + name + "']";
    const el = document.querySelector(path);
    if (el) {
      el.innerHTML = null;
      for (const [key, value] of Object.entries(options)) {
        op = document.createElement("option");
        op.value = key;
        op.innerHTML = value;
        el.appendChild(op);
      }
    }
    if (selected) {
      el.value = selected;
    }
  }

  showTriggerOverlay() {
    this.toolSlate.setContent(this.uiModel.triggerOverlay);
    const tr = this.iimModel.getCurrentTrigger();
    const overlay = tr.getOverlay();
    console.log("***");
    if (overlay) {
      console.log("1");
      console.log(overlay);
      console.log(overlay.getSrc());
      this.setInputValueByName('#copg-iim-trigger-overlay-form', 'form_input_1', overlay.getSrc());
    }
    this.initTriggerViewControl();
    this.initBackButton();
    this.initTriggerOverlay();
    this.showCurrentShape();
  }

  showCurrentShape(edit = false) {
    const trigger = this.iimModel.getCurrentTrigger();
    this.shapeEditor.removeAllShapes();
    this.shapeEditor.addShape(trigger.getShape(), edit);
    this.shapeEditor.repaint();
  }

  initTriggerOverlay() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    document.querySelectorAll("[data-copg-ed-type='button']").forEach(button => {
      const act = button.dataset.copgEdAction;
      button.addEventListener("click", (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_OVERLAY_ADD:
            dispatch.dispatch(action.interactiveImage().editor().addTriggerOverlay());
            break;
        }
      });
    });
    document.querySelectorAll("form [data-copg-ed-type='form-button']").forEach(button => {
      const act = button.dataset.copgEdAction;
      button.addEventListener("click", (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_OVERLAY_SAVE:
            event.preventDefault();
            dispatch.dispatch(action.interactiveImage().editor().saveTriggerOverlay(
              model.getCurrentTrigger().nr,
              this.getInputValueByName('form_input_1'),
              model.getCurrentTrigger().getOverlay().getCoordsString()
            ));
            break;
        }
      });
    });
    let options = {};
    options[''] = ' - ';
    this.iimModel.getOverlays().forEach((ov) => {
      console.log(ov);
      options[ov.name] = ov.name;
    });
    this.setSelectOptions("#copg-editor-slate-content", "form_input_1", options);
  }

  showTriggerPopup() {
    this.toolSlate.setContent(this.uiModel.triggerPopup);
    this.initTriggerViewControl();
    this.initBackButton();
    this.showCurrentShape();
  }

  initBackButton() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    document.querySelectorAll("[data-copg-ed-type='button']").forEach(button => {
      const act = button.dataset.copgEdAction;
      button.addEventListener("click", (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_BACK:
            dispatch.dispatch(action.interactiveImage().editor().triggerBack());
            break;
        }
      });
    });
  }

  initTriggerViewControl() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='view-control']").forEach(button => {
      const act = button.dataset.copgEdAction;
      button.addEventListener("click", (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_PROPERTIES:
            dispatch.dispatch(action.interactiveImage().editor().triggerProperties());
            break;
          case ACTIONS.E_TRIGGER_OVERLAY:
            dispatch.dispatch(action.interactiveImage().editor().triggerOverlay());
            break;
          case ACTIONS.E_TRIGGER_POPUP:
            dispatch.dispatch(action.interactiveImage().editor().triggerPopup());
            break;
        }
      });
    });
    this.refreshTriggerViewControl();
  }

  refreshTriggerViewControl() {
    const model = this.iimModel;
    const prop = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='trigger.properties']");
    const ov = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='trigger.overlay']");
    const pop = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='trigger.popup']");
    prop.classList.remove("engaged");
    ov.classList.remove("engaged");
    pop.classList.remove("engaged");
    prop.disabled = false;
    ov.disabled = false;
    pop.disabled = false;
    if (model.getState() === model.STATE_TRIGGER_PROPERTIES) {
      prop.disabled = true;
      prop.classList.add("engaged");
    } else if (model.getState() === model.STATE_TRIGGER_OVERLAY) {
      ov.disabled = true;
      ov.classList.add("engaged");
    } else if (model.getState() === model.STATE_TRIGGER_POPUP) {
      pop.disabled = true;
      pop.classList.add("engaged");
    }
  }

  showSettings() {
    this.toolSlate.setContent(this.uiModel.backgroundProperties);
    this.initBackButton();
  }

  showOverlays() {
    this.toolSlate.setContent(this.uiModel.overlayOverview);
    this.initBackButton();
    this.initOverlayList();
  }

  initOverlayList() {
    const overlays = this.iimModel.getOverlays();
    const action = this.actionFactory;
    let items = [];
    overlays.forEach((ov) => {
      items.push({
        placeholders: {
          'item-title': ov.name,
          'img-alt': ov.name,
          'img-src': ov.webpath
        },
        actions: [
          {
            action: action.interactiveImage().editor().deleteOverlay(ov.name),
            txt: il.Language.txt('delete')
          }
        ]
      }
    );
    });
    this.fillItemList(items);
  }

  fillItemList(items) {
    let newNode, newLiNode, liTempl, liParent;
    const dispatch = this.dispatcher;
    const templEl = document.querySelector("#copg-editor-slate-content .il-std-item-container");
    const parent = templEl.parentNode;
    items.forEach((item) => {
      newNode = templEl.cloneNode(true);
      for (const [key, value] of Object.entries(item.placeholders)) {
        newNode.innerHTML = newNode.innerHTML.replace(
          "#" + key + "#",
          value
        );
      }
      liTempl = newNode.querySelector(".dropdown-menu li");
      liParent = liTempl.parentNode;
      item.actions.forEach((action) => {
        newLiNode = liTempl.cloneNode(true);
        newLiNode.innerHTML = newLiNode.innerHTML.replace(
          "#link-label#",
          action.txt
        );
        newLiNode = liParent.appendChild(newLiNode);
        newLiNode.addEventListener("click", () => {
          dispatch.dispatch(action.action);
        })
      });
      liTempl.remove();
      parent.appendChild(newNode);
    });
    templEl.remove();
  }

  showPopups() {
    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    this.toolSlate.setContent(this.uiModel.popupOverview);
    this.initBackButton();
    this.initPopupList();
    document.querySelectorAll("[data-copg-ed-type='button']").forEach(button => {
      const act = button.dataset.copgEdAction;
      button.addEventListener("click", (event) => {
        switch (act) {
          case ACTIONS.E_TRIGGER_POPUP_ADD:
            dispatch.dispatch(action.interactiveImage().editor().addTriggerPopup());
            break;
        }
      });
    });
  }

  initPopupList() {
    const popups = this.iimModel.getPopups();
    const action = this.actionFactory;
    let items = [];
    popups.forEach((pop) => {
      items.push({
          placeholders: {
            'item-title': pop.title,
          },
          actions: [
            {
              action: action.interactiveImage().editor().renamePopup(pop.nr),
              txt: il.Language.txt('rename')
            },
            {
              action: action.interactiveImage().editor().deletePopup(pop.nr),
              txt: il.Language.txt('delete')
            }
          ]
        }
      );
    });
    this.fillItemList(items);
  }


  showOverlayModal() {
    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    this.util.showModal(
      this.uiModel.modal,
      il.Language.txt("cont_add_overlay"),
      this.uiModel.overlayUpload,
      il.Language.txt("add"),
      (e) => {
        const form = document.querySelector("#il-copg-ed-modal form");

        //after_pcid, pcid, component, data
        dispatch.dispatch(action.interactiveImage().editor().uploadOverlay(
          {
            form:form
          }
        ));
      });
  }

  showPopupModal(params = null, model = null) {
    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    const nr = (params) ? params.nr : '';
    this.util.showModal(
      this.uiModel.modal,
      il.Language.txt("cont_add_popup"),
      this.uiModel.popupForm,
      il.Language.txt("save"),
      (e) => {
        const form = document.querySelector("#il-copg-ed-modal form");

        //after_pcid, pcid, component, data
        dispatch.dispatch(action.interactiveImage().editor().savePopup(
          {
            form:form
          },
          nr
        ));
      });
    if (params) {
      this.setInputValueByName('.modal-content', 'form_input_1', model.getPopupTitle(params.nr));
    }
  }

}
