/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * page ui
 */
export default class PageUI {

  /**
   * @type {boolean}
   */
  debug = true;

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
   * @type {pageModifier}
   */
  pageModifier;

  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {Model} model
   * @param {ToolSlate} toolSlate
   * @param {PageModifier} pageModifier
   */
  constructor(client, dispatcher, actionFactory, model, toolSlate
    , pageModifier) {
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.model = model;
    this.toolSlate = toolSlate;
    this.pageModifier = pageModifier;
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
  initAddButtons(selector) {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    if (!selector) {
      selector = "[data-copg-ed-type='add-area']"
    }

    // init add buttons
    document.querySelectorAll(selector).forEach(area => {

      const uiModel = this.uiModel;
      let li, li_templ, ul;
      area.innerHTML = this.droparea + uiModel.addDropdown;

      const model = this.model;

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

          this.log("add dropdown: click");
          this.log(model);

          const multiCutOrCopy = ((model.getState() === model.STATE_MULTI_ACTION) &&
            ([model.STATE_MULTI_CUT, model.STATE_MULTI_COPY].includes(model.getMultiState())));

          if (multiCutOrCopy) {
            // multi-action cut or copy
            li = li_templ.cloneNode(true);
            li.querySelector("a").innerHTML = il.Language.txt("paste");
            li.querySelector("a").addEventListener("click", (event) => {
              event.isDropDownSelectionEvent = true;
              dispatch.dispatch(action.page().editor().multiPaste(
                area.dataset.pcid,
                area.dataset.hierid,
                model.getMultiState()));
            });
            ul.appendChild(li);
          } else {
            // add each components
            for (const [ctype, txt] of Object.entries(uiModel.addCommands)) {
              li = li_templ.cloneNode(true);
              li.querySelector("a").innerHTML = txt;
              let cname = this.getPCNameForType(ctype);
              li.querySelector("a").addEventListener("click", (event) => {
                event.isDropDownSelectionEvent = true;
                dispatch.dispatch(action.page().editor().componentInsert(cname,
                  area.dataset.pcid,
                  area.dataset.hierid));
              });
              ul.appendChild(li);
            }
          }
        });
      });
    });
  }

  getPCTypeForName(name) {
    return this.uiModel.pcDefinition.types[name];
  }

  getPCNameForType(type) {
    return this.uiModel.pcDefinition.names[type];
  }

  /**
   * Click and DBlClick is not naturally supported on browsers (click is also fired on
   * dblclick, time period for dblclick varies)
   */
  initComponentClick(selector) {
    let clickMap = this.clickMap;
    let period = 400;

    if (!selector) {
      selector = "[data-copg-ed-type='pc-area']";
    }

    // init add buttons
    document.querySelectorAll(selector).forEach(area => {
      area.addEventListener("click", (event) => {
        if (event.isDropDownToggleEvent === true ||
          event.isDropDownSelectionEvent === true) {
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

  initComponentEditing(selector) {

    if (!selector) {
      selector = "[data-copg-ed-type='pc-area']";
    }

    // init add buttons
    document.querySelectorAll(selector).forEach(area => {
      const dispatch = this.dispatcher;
      const action = this.actionFactory;

      area.addEventListener("areaClick", (event) => {
        // temp disable switching
        if (this.model.getState() !== this.model.STATE_PAGE) {
          return;
        }
        let is_switch = (this.model.getState() === this.model.STATE_COMPONENT);
        dispatch.dispatch(action.page().editor().componentEdit(area.dataset.cname,
          area.dataset.pcid,
          area.dataset.hierid,
          is_switch));
      });
    });
  }

  /**
   * Init drag and drop handling
   */
  initDragDrop(draggableSelector, droppableSelector) {

    this.log("pag-ui.initDragDrop");

    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    if (!draggableSelector) {
      draggableSelector = ".il_editarea";
    }

    if (!droppableSelector) {
      droppableSelector = ".il_droparea";
    }

    $(draggableSelector).draggable({
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

        },
        helper: (() => {
          return $("<div style='width: 40px; border: 1px solid blue;'>&nbsp;</div>");
        })		/* temp helper */
      }
    );

    $(droppableSelector).droppable({
      drop: (event, ui) => {
        ui.draggable.draggable( 'option', 'revert', false );



        // @todo: remove legacy
        const target_id = event.target.id.substr(6);
        const source_id = ui.draggable[0].id.substr(7);

        dispatch.dispatch(action.page().editor().dndDrop(target_id, source_id));
      }
    });

    // this is needed to make scrolling while dragging with helper possible
    $("main.il-layout-page-content").css("position", "relative");

    this.hideDropareas();
  }

  /**
   * Init multi selection
   */
  initMultiSelection(selector) {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    if (!selector) {
      selector = "[data-copg-ed-type='pc-area']";
    }

    document.querySelectorAll(selector).forEach(pc_area => {
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

  initFormatButtons() {

    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    const model = this.model;


    this.toolSlate.setContent(this.uiModel.formatSelection);

    document.querySelectorAll("[data-copg-ed-type='format']").forEach(multi_button => {
      const act = multi_button.dataset.copgEdAction;
      const format = multi_button.dataset.copgEdParFormat;

      switch (act) {

        case "format.paragraph":
          multi_button.addEventListener("click", (event) => {
            dispatch.dispatch(action.page().editor().formatParagraph(format));
          });
          break;

        case "format.section":
          multi_button.addEventListener("click", (event) => {
            dispatch.dispatch(action.page().editor().formatSection(format));
          });
          break;

        case "format.save":
          multi_button.addEventListener("click", (event) => {
            const pcids = new Set(this.model.getSelected());
            dispatch.dispatch(action.page().editor().formatSave(
              pcids,
              model.getParagraphFormat(),
              model.getSectionFormat()
            ));
          });
          break;
      }
    });

    // get first values and dispatch their selection
    const b1 = document.querySelector("#il-copg-format-paragraph div.dropdown ul li button");
    const f1 = b1.dataset.copgEdParFormat;
    if (f1) {
      dispatch.dispatch(action.page().editor().formatParagraph(f1));
    }
    const b2 = document.querySelector("#il-copg-format-section div.dropdown ul li button");
    const f2 = b2.dataset.copgEdParFormat;
    if (f2) {
      dispatch.dispatch(action.page().editor().formatSection(f2));
    }
  }

  setParagraphFormat(format) {
    console.log("setParagraphFormat " + format);
    const b1 = document.querySelector("#il-copg-format-paragraph div.dropdown > button");
    console.log(b1);
    if (b1) {
      b1.firstChild.textContent = format + " ";
    }
  }

  setSectionFormat(format) {
    const b2 = document.querySelector("#il-copg-format-section div.dropdown > button");
    if (b2) {
      b2.firstChild.textContent = format + " ";
    }
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
    const model = this.model;

    switch (model.getMultiState()) {
      case model.STATE_MULTI_CUT:
        this.toolSlate.setContent(this.uiModel.cutConfirm);
        break;
      case model.STATE_MULTI_COPY:
        this.toolSlate.setContent(this.uiModel.copyConfirm);
        break;

      case model.STATE_MULTI_CHARACTERISTIC:
        break;

      default:
        this.toolSlate.setContent(this.uiModel.multiActions);
        this.initMultiButtons();
        break;
    }


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

  // default callback for successfull ajax request, reloads page content
  handlePageReloadResponse(result)
  {
    const pl = result.getPayload();
    this.log("handlePageReloadResponse");

    if(pl.renderedContent !== undefined)
    {
      $('#il_center_col').html(pl.renderedContent);
      il.IntLink.refresh();
      this.reInit();
    }
  }


}
