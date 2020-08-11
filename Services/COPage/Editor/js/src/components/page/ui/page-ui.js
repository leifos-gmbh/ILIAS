/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * page ui
 */
export default class PageUI {

  /**
   * temp legacy code
   * @type {string}
   */
  droparea = "<div class='il_droparea'></div>";
  add = "<span class='glyphicon glyphicon-plus'></span>";

  /**
   * Model
   * @type {Model}
   */
  model = {};

  /**
   * UI model
   * @type {Object}
   */
  uiModel = {};

  /**
   * @type {Client}
   */
  client;

  /**
   * @type {Dispatcher}
   */
  dispatcher;

  /**
   * @type {ActionFactory}
   */
  actionFactory;

  /**
   * @type {Map<any, any>}
   */
  clickMap = new Map();

  /**
   * @type {ToolSlate}
   */
  toolSlate;

  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {Model} model
   * @param {ToolSlate} toolSlate
   */
  constructor(client, dispatcher, actionFactory, model, toolSlate) {
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.model = model;
    this.toolSlate = toolSlate;
  }

  //
  // Initialisation
  //

  /**
   */
  init(uiModel) {
    this.uiModel = uiModel;
    this.initComponentClick();
    this.initAddButtons();
    this.initDragDrop();
    this.initMultiSelection();
    this.initComponentEditing();
  }

  /**
   */
  reInit() {
    this.initComponentClick();
    this.initAddButtons();
    this.initDragDrop();
    this.initMultiSelection();
    this.initComponentEditing();
  }

  /**
   * Init add buttons
   */
  initAddButtons() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    // init add buttons
    document.querySelectorAll("[data-copg-ed-type='add-area']").forEach(area => {

      const uiModel = this.uiModel;
      let li, li_templ, ul;
      area.innerHTML = this.droparea + uiModel.addDropdown;

      // droparea
      const drop = area.firstChild;
      drop.id = "TARGET" + area.dataset.hierid + ":" + (area.dataset.pcid || "");

      // add dropdown
      area.querySelectorAll("div.dropdown > button").forEach(b => {
        b.classList.add("copg-add");
        b.innerHTML = this.add;
        b.addEventListener("click", (event) => {

          // we need that to "filter" out these events on the single clicks
          // on editareas
          event.isDropDownToggleEvent = true;

          ul = b.parentNode.querySelector("ul");
          li_templ = ul.querySelector("li").cloneNode(true);
          ul.innerHTML = "";
          for (const [ctype, txt] of Object.entries(uiModel.addCommands)) {
            li = li_templ.cloneNode(true);
            li.querySelector("a").innerHTML = txt;
            li.querySelector("a").addEventListener("click", (event) => {
              dispatch.dispatch(action.page().editor().createAdd(ctype,
                area.dataset.pcid,
                area.dataset.hierid));
            });
            ul.appendChild(li);
          }
        });
      });
    });
  }


  /**
   * Click and DBlClick is not naturally supported on browsers (click is also fired on
   * dblclick, time period for dblclick varies)
   */
  initComponentClick() {
    let clickMap = this.clickMap;
    let period = 400;
    // init add buttons
    document.querySelectorAll("[data-copg-ed-type='pc-area']").forEach(area => {
      area.addEventListener("click", (event) => {
        if (event.isDropDownToggleEvent === true) {
          return;
        }
        event.stopPropagation();
        if(!clickMap.has(area)) {
          clickMap.set(area, 0);
        }
        if (clickMap.get(area) < 2) {
          clickMap.set(area, clickMap.get(area) + 1);
        }
        if (clickMap.get(area) === 1) {
          setTimeout(() => {
            if (clickMap.get(area) === 1) {
              console.log("areaClick");
              area.dispatchEvent(new Event("areaClick"));
            } else if (clickMap.get(area) === 2) {
              console.log("areaDblClick");
              area.dispatchEvent(new Event("areaDblClick"));
            }
            clickMap.set(area, 0);
          }, period);
        }
      });
    });
  }

  initComponentEditing() {

    // init add buttons
    document.querySelectorAll("[data-copg-ed-type='pc-area']").forEach(area => {
      const dispatch = this.dispatcher;
      const action = this.actionFactory;

      area.addEventListener("areaClick", (event) => {
        // temp disable switching
        if (this.model.getState() !== this.model.STATE_PAGE) {
          return;
        }
        let is_switch = (this.model.getState() === this.model.STATE_COMPONENT);
        dispatch.dispatch(action.page().editor().editOpen(area.dataset.cname,
          area.dataset.pcid,
          area.dataset.hierid,
          is_switch));
      });
    });
  }

  /**
   * Init drag and drop handling
   */
  initDragDrop() {

    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    $(".il_editarea").draggable({
        cursor: 'move',
        revert: true,
        scroll: true,
        cursorAt: { top: 5, left:20 },
        snap: true,
        snapMode: 'outer',
        start: function( event, ui ) {
          dispatch.dispatch(action.page().editor().dndDrag());
        },
        stop: function( event, ui ) {
          dispatch.dispatch(action.page().editor().dndDrop());
        },
        helper: (() => {
          return $("<div style='width: 40px; border: 1px solid blue;'>&nbsp;</div>");
        })		/* temp helper */
      }
    );

    $(".il_droparea").droppable({
      drop: (event, ui) => {
        ui.draggable.draggable( 'option', 'revert', false );

        // @todo: remove legacy
        const target_id = event.target.id.substr(6);
        const source_id = ui.draggable[0].id.substr(7);
        if (source_id !== target_id) {
          ilCOPage.sendCmdRequest("moveAfter", source_id, target_id, {},
            true, {}, ilCOPage.pageReloadAjaxSuccess);
        }
      }
    });

    // this is needed to make scrolling while dragging with helper possible
    $("main.il-layout-page-content").css("position", "relative");

    this.hideDropareas();
  }

  /**
   * Init multi selection
   */
  initMultiSelection() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='pc-area']").forEach(pc_area => {
      const pcid = pc_area.dataset.pcid;
      const hierid = pc_area.dataset.hierid;
      const ctype = pc_area.dataset.ctype;
      pc_area.addEventListener("areaDblClick", (event) => {
        if (this.model.getState() !== this.model.STATE_PAGE &&
          this.model.getState() !== this.model.STATE_MULTI_ACTION
        ) {
          return;
        }
        dispatch.dispatch(action.page().editor().multiToggle(ctype, pcid, hierid));
      });
    });
  }

  initMultiButtons() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='multi']").forEach(multi_button => {
      const type = multi_button.dataset.copgEdAction;
      multi_button.addEventListener("click", (event) => {
        dispatch.dispatch(action.page().editor().multiAction(type));
      });
    });
  }

  //
  // Show/Hide single elements
  //

  enableDragDrop() {
    $('.il_editarea').draggable("enable");
  }

  disableDragDrop() {
    $('.il_editarea').draggable("disable");
  }

  showAddButtons() {
    document.querySelectorAll("button.copg-add").forEach(el => {
      el.style.display = "";
    });
  }

  hideAddButtons() {
    document.querySelectorAll("button.copg-add").forEach(el => {
      el.style.display = "none";
    });
  }

  showDropareas() {
    document.querySelectorAll("#il_EditPage .il_droparea").forEach(el => {
      el.style.display = "";
    });
  }

  hideDropareas() {
    document.querySelectorAll("#il_EditPage .il_droparea").forEach(el => {
      el.style.display = "none";
    });
  }

  showPageHelp() {
    this.toolSlate.setContent(this.uiModel.pageHelp);
  }

  showMultiButtons() {
    this.toolSlate.setContent(this.uiModel.multiActions);
    this.initMultiButtons();
  }

  /**
   * @param {Set<string>} items
   */
  highlightSelected(items) {
    document.querySelectorAll("[data-copg-ed-type='pc-area']").forEach(el => {
      const key = el.dataset.hierid + ":" + (el.dataset.pcid || "");
      if (items.has(key)) {
        el.classList.add("il_editarea_selected");
      } else {
        el.classList.remove("il_editarea_selected");
      }
    });
  }

}
