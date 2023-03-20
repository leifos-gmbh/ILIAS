import ACTIONS from "../actions/table-action-types.js";
import PAGE_ACTIONS from "../../page/actions/page-action-types.js";
import TinyWrapper from "../../paragraph/ui/tiny-wrapper.js";
import ParagraphUI from '../../paragraph/ui/paragraph-ui.js';
import TINY_CB from "../../paragraph/ui/tiny-wrapper-cb-types.js";

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
 * table ui
 */
export default class TableUI {


  /**
   * @type {boolean}
   */
  //debug = true;

  /**
   * Model
   * @type {Model}
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
   * @type {TinyWrapper}
   */
  //tinyWrapper;

  /**
   * @type {pageModifier}
   */
  //pageModifier;

  /**
   * @type {ParagraphUI}
   */
  //paragraphUI;

  /**
   * @type {TableModel}
   */
  //tableModel;


  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {PageModel} page_model
   * @param {ToolSlate} toolSlate
   * @param {pageModifier} pageModifier
   * @param {ParagraphUI} paragraphUI
   * @param {TableModel} tableModel
   */
  constructor(client, dispatcher, actionFactory, page_model, toolSlate, pageModifier, paragraphUI, tableModel) {

    this.debug = true;
    this.page_model = {};
    this.uiModel = {};

    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.page_model = page_model;
    this.toolSlate = toolSlate;
    this.pageModifier = pageModifier;
    this.paragraphUI = paragraphUI;
    this.tinyWrapper = paragraphUI.tinyWrapper;
    this.autoSave = paragraphUI.autoSave;
    this.tableModel = tableModel;
    this.in_data_table = false;
    this.head_selection_initialised = false;
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
    this.log("table-ui.init");

    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    const pageModel = this.page_model;

    this.uiModel = uiModel;
    let t = this;

    if (uiModel.initialComponent === "DataTable") {
      this.in_data_table = true;
      pageModel.setCurrentPageComponent("DataTable", uiModel.initialPCId, '');
    }

    if (!this.in_data_table) {
      return;
    }

    // init wrapper in paragraphui
    //this.paragraphUI.initTinyWrapper();

    // init menu in paragraphui
    //this.initMenu();
    this.initCellEditing();
    this.initDropdowns();
    this.autoSave.addOnAutoSave(() => {
      if (pageModel.getCurrentPCName() === "Table") {
        dispatch.dispatch(action.table().editor().autoSave());
      }
    });

    this.initWrapperCallbacks();
    this.refreshUIFromModelState(pageModel, this.tableModel);
  }

  /**
   */
  reInit() {
    this.initCellEditing();
    this.initDropdowns();
  }

  /**
   * Init add buttons
   */
  initHeadSelection() {
    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    const tableModel = this.tableModel;
    const selector = "[data-copg-ed-type='data-column-head'],[data-copg-ed-type='data-row-head']";

    // init add buttons
    document.querySelectorAll(selector).forEach(head => {
      const caption = head.dataset.caption;
      const nr = parseInt(head.dataset.nr) - 1;
      const headType = head.dataset.copgEdType;
      head.innerHTML = caption;
      if (!this.head_selection_initialised) {
        head.addEventListener("click", (event) => {
          if (tableModel.getState() !== tableModel.STATE_CELLS) {
            return;
          }
          event.stopPropagation();
          event.preventDefault();
          document.getSelection().removeAllRanges();
          const expand = (event.shiftKey || event.ctrlKey || event.metaKey);
          if (headType === "data-row-head") {
            dispatch.dispatch(action.table().editor().toggleRow(nr, expand));
          } else {
            dispatch.dispatch(action.table().editor().toggleCol(nr, expand));
          }
        });
      }
    });
    this.head_selection_initialised = true;
  }

      /**
   * Init add buttons
   */
  initDropdowns() {
    const action = this.actionFactory;

    const selector = "[data-copg-ed-type='data-column-head'],[data-copg-ed-type='data-row-head']";

    // init add buttons
    document.querySelectorAll(selector).forEach(head => {

      const headType = head.dataset.copgEdType;
      const nr = head.dataset.nr;
      const caption = head.dataset.caption;
      const cellPcid = head.dataset.pcid;

      const table = head.closest("table");
      const tablePcid = table.dataset.pcid;

      const uiModel = this.uiModel;
      let li, li_templ, ul;

      head.innerHTML = uiModel.dropdown;


      const model = this.model;

      const af = action.table().editor();

      // add dropdown
      head.querySelectorAll("div.dropdown > button").forEach(b => {
        //b.classList.add("copg-add");
        b.innerHTML = caption + b.innerHTML;
        b.addEventListener("click", (event) => {

          ul = b.parentNode.querySelector("ul");
          li_templ = ul.querySelector("li").cloneNode(true);
          ul.innerHTML = "";

            if (headType === "data-column-head") {
              const th = b.closest("th");
              const first = !(th.previousElementSibling.previousElementSibling);
              const last = !(th.nextElementSibling);
              this.addDropdownAction(li_templ, ul, "cont_ed_new_col_before", af.colBefore(nr, cellPcid, tablePcid));
              this.addDropdownAction(li_templ, ul, "cont_ed_new_col_after", af.colAfter(nr, cellPcid, tablePcid));
              if (!first) {
                this.addDropdownAction(li_templ, ul, "cont_ed_col_left", af.colLeft(nr, cellPcid, tablePcid));
              }
              if (!last) {
                this.addDropdownAction(li_templ, ul, "cont_ed_col_right", af.colRight(nr, cellPcid, tablePcid));
              }
              this.addDropdownAction(li_templ, ul, "cont_ed_delete_col", af.colDelete(nr, cellPcid, tablePcid));
            } else {
              const tr = b.closest("tr");
              const first = !(tr.previousElementSibling.previousElementSibling);
              const last = !(tr.nextElementSibling);
              this.addDropdownAction(li_templ, ul, "cont_ed_new_row_before", af.rowBefore(nr, cellPcid, tablePcid));
              this.addDropdownAction(li_templ, ul, "cont_ed_new_row_after", af.rowAfter(nr, cellPcid, tablePcid));
              if (!first) {
                this.addDropdownAction(li_templ, ul, "cont_ed_row_up", af.rowUp(nr, cellPcid, tablePcid));
              }
              if (!last) {
                this.addDropdownAction(li_templ, ul, "cont_ed_row_down", af.rowDown(nr, cellPcid, tablePcid));
              }
              this.addDropdownAction(li_templ, ul, "cont_ed_delete_row", af.rowDelete(nr, cellPcid, tablePcid));
            }
        });
      });
    });
  }

  addDropdownAction(li_templ, ul, txtKey, action) {
    const dispatch = this.dispatcher;
    const li = li_templ.cloneNode(true);

    li.querySelector("a").innerHTML = il.Language.txt(txtKey);
    li.querySelector("a").addEventListener("click", (event) => {
      dispatch.dispatch(action);
    });
    ul.appendChild(li);
  }

  initCellEditing() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
console.log("INIT CELL EDITING")
    document.querySelectorAll("[data-copg-ed-type='data-cell']").forEach((el) => {
      const column = el.dataset.column;
      const row = el.dataset.row;
      const table = el.closest("table");
      const table_pcid = table.dataset.pcid;
      const table_hierid = table.dataset.hierid;
      const tableModel = this.tableModel;
      console.log(el.dataset);
      el.addEventListener("click", (event) => {
        if (tableModel.getState() !== tableModel.STATE_CELLS) {
          dispatch.dispatch(action.table().editor().editCell(
            table_pcid,
            table_hierid,
            row,
            column
          ));
        } else {
          event.stopPropagation();
          event.preventDefault();
          document.getSelection().removeAllRanges();
          const expand = (event.shiftKey || event.ctrlKey || event.metaKey);
          dispatch.dispatch(action.table().editor().toggleCell(
            column,
            row,
            expand
          ));
        }
      });
    });
  }

  editCell(pcid, row, col) {
    this.tinyWrapper.setDataTableMode(true);
    this.paragraphUI.setDataTableMode(true);
    const tableModel = this.tableModel;
    const wrapper = this.tinyWrapper;
    let content_el = document.querySelector("[data-copg-ed-type='data-cell'][data-row='" + tableModel.getCurrentRow() + "'][data-column='" + tableModel.getCurrentColumn() + "']");

    wrapper.stopEditing();
    wrapper.initEdit(content_el, "", "");
  }

  initWrapperCallbacks() {
    const wrapper = this.tinyWrapper;
    const tableUI = this;
    const tableModel = this.tableModel;
    const pageModel = this.page_model;
    wrapper.addCallback(TINY_CB.SWITCH_LEFT, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(-1,0);
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_UP, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(0,-1);
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_RIGHT, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(1,0);
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_DOWN, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(0,1);
      }
    });
    wrapper.addCallback(TINY_CB.TAB, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(1,0);
      }
    });
    wrapper.addCallback(TINY_CB.SHIFT_TAB, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(-1,0);
      }
    });
    wrapper.addCallback(TINY_CB.KEY_UP, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        let pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
        pcModel.content[tableModel.getCurrentRow()][tableModel.getCurrentColumn()] = wrapper.getText();
        tableUI.paragraphUI.autoSave.handleAutoSaveKeyPressed();
      }
    });
    wrapper.addCallback(TINY_CB.AFTER_INIT, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        let pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
        let content = pcModel.content[tableModel.getCurrentRow()][tableModel.getCurrentColumn()];
        tableUI.paragraphUI.showToolbar(false, false);
        wrapper.initContent(content, "");
      }
    });
  }

  cellExists (col, row) {
    const pageModel = this.page_model;
    const pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
    return (row in pcModel.content && col in pcModel.content[row]);
  }

  updateModelFromCell() {
    const pageModel = this.page_model;
    const pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
    const tableModel = this.tableModel;
    const wrapper = this.tinyWrapper;
    if (tableModel.getCurrentRow() == null) {
      return;
    }
    pcModel.content[tableModel.getCurrentRow()][tableModel.getCurrentColumn()] = wrapper.getText();
  }

  switchEditingCell(colDiff, rowDiff) {
    const pageModel = this.page_model;
    const pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    const tableModel = this.tableModel;
    let newCol = tableModel.getCurrentColumn() + colDiff;
    let newRow = tableModel.getCurrentRow() + rowDiff;

    this.updateModelFromCell();

    // move to beginning of next row, if end of row is reached
    if (rowDiff === 0 && colDiff === 1) {
      if (!this.cellExists(newCol, newRow)) {
        newCol = 0;
        newRow = tableModel.getCurrentRow() + 1;
      }
    }

    // move to end of previous row, if beginning of row is reached
    if (rowDiff === 0 && colDiff === -1) {
      if (!this.cellExists(newCol, newRow)) {
        newCol = 0;
        newRow = tableModel.getCurrentRow() - 1;
        if (newRow >= 0) {
          newCol = pcModel.content[newRow].length - 1;
        }
      }
    }

    if (this.cellExists(newCol, newRow)) {
      dispatch.dispatch(action.table().editor().editCell(
          pageModel.getCurrentPCId(),
          pageModel.getCurrenntHierId(),
          newRow,
          newCol
      ));
    }
  }

  refreshUIFromModelState(pageModel, table_model) {
    console.log("REFRESH");
    console.log(table_model.getState());
    switch (table_model.getState()) {
      case table_model.STATE_TABLE:
        this.showTableProperties();
        break;
      case table_model.STATE_CELLS:
        this.showCellProperties();
        break;
    }
  }

  showTableProperties() {
    const dispatcher = this.dispatcher;
    const actionFactory = this.actionFactory;

    dispatcher.dispatch(actionFactory.page().editor().componentForm(
      "DataTable",
      this.uiModel.initialPCId,
      ""));
  }

  initAfterFormLoaded() {
    this.initTopActions();
    this.refreshModeSelector();
  }

  showCellProperties() {
    let add = "";
    if (this.tableModel.hasSelected()) {
      add = this.uiModel.components["DataTable"]["cell_actions"];
    } else {
      add = this.uiModel.components["DataTable"]["cell_info"];
    }
    this.toolSlate.setContent(this.uiModel.components["DataTable"]["top_actions"] + add);
    this.initTopActions();
    this.refreshModeSelector();
    this.initCellPropertiesForm(this.page_model, this.tableModel);
  }

  initCellPropertiesForm(pageModel,tableModel) {

    document.querySelectorAll("#copg-editor-slate-content [data-copg-ed-type='form-button']").forEach(form_button => {
      const dispatch = this.dispatcher;
      const action = this.actionFactory;
      const act = form_button.dataset.copgEdAction;
      const cname = form_button.dataset.copgEdComponent;
      if (cname === "Table") {
        console.log("ATTACHING EVENT TO FORM BUTTON");
        console.log(form_button);
        form_button.addEventListener("click", (event) => {
          event.preventDefault();
          switch (act) {
            case "properties.set":
              const uform = form_button.closest("form");
              const uform_data = new FormData(uform);
              dispatch.dispatch(action.table().editor().propertiesSet(
                pageModel.getCurrentPCId(),
                tableModel.getSelected(),
                uform_data
              ));
              break;

            case "toggle.merge":
              dispatch.dispatch(action.table().editor().toggleMerge(
                pageModel.getCurrentPCId(),
                tableModel.getSelected()
              ));
              break;
          }
        });
      }
    });
  }


  initTopActions() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='view-control']").forEach(button => {
      const act = button.dataset.copgEdAction;
      button.addEventListener("click", (event) => {
        switch (act) {
          case ACTIONS.SWITCH_EDIT_TABLE:
            dispatch.dispatch(action.table().editor().switchEditTable());
            break;
          case ACTIONS.SWITCH_FORMAT_CELLS:
            dispatch.dispatch(action.table().editor().switchFormatCells());
            break;
        }
      });
    });
  }

  refreshModeSelector() {
    const model = this.tableModel;
    const table = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='switch.edit.table']");
    const cells = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='switch.format.cells']");
    table.classList.remove("engaged");
    cells.classList.remove("engaged");
    if (model.getState() === model.STATE_TABLE) {
      table.classList.add("engaged");
    } else if (model.getState() === model.STATE_CELLS) {
      cells.classList.add("engaged");
    }
  }

  markSelectedCells() {
    const selected = this.tableModel.getSelected();
    console.log("MARK SELECTED");
    console.log(selected);
    document.querySelectorAll("[data-copg-ed-type='data-cell']").forEach((el) => {
      const col = el.dataset.column;
      const row = el.dataset.row;
      el.classList.remove("il-copg-cell-selected");
      if (selected.top <= row &&
        selected.bottom >= row &&
        selected.left <= col &&
        selected.right >= col) {
        el.classList.add("il-copg-cell-selected");
      }
    });
  }

}
