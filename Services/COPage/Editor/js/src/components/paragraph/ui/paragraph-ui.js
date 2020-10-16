import ACTIONS from "../actions/paragraph-action-types.js";
import PAGE_ACTIONS from "../../page/actions/page-action-types.js";
import TinyWrapper from "./tiny-wrapper.js";
import AutoSave from "./auto-save.js";

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * paragraph ui
 */
export default class ParagraphUI {


  /**
   * @type {boolean}
   */
  debug = true;

  /**
   * Model
   * @type {Model}
   */
  page_model = {};

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
   * @type {ToolSlate}
   */
  toolSlate;

  /**
   * @type {TinyWrapper}
   */
  tinyWrapper;

  /**
   * @type {pageModifier}
   */
  pageModifier;

  /**
   *
   * @type {AutoSave}
   */
  autoSave;

  /**
   * @type {Object}
   */
  text_formats = {
    Strong: {inline : 'span', classes : 'ilc_text_inline_Strong'},
    Emph: {inline : 'span', classes : 'ilc_text_inline_Emph'},
    Important: {inline : 'span', classes : 'ilc_text_inline_Important'},
    Comment: {inline : 'span', classes : 'ilc_text_inline_Comment'},
    Quotation: {inline : 'span', classes : 'ilc_text_inline_Quotation'},
    Accent: {inline : 'span', classes : 'ilc_text_inline_Accent'},
    Sup: {inline : 'sup', classes : 'ilc_sup_Sup'},
    Sub: {inline : 'sub', classes : 'ilc_sub_Sub'}
  };

  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {PageModel} page_model
   * @param {ToolSlate} toolSlate
   */
  constructor(client, dispatcher, actionFactory, page_model, toolSlate, pageModifier, autosave) {
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.page_model = page_model;
    this.toolSlate = toolSlate;
    this.tinyWrapper = new TinyWrapper((tiny, contents) => {
      dispatcher.dispatch(actionFactory.paragraph().editor().
        splitParagraph(this.page_model.getCurrentPCId(), tiny.getText(), tiny.getCharacteristic(), contents));
    });
    this.pageModifier = pageModifier;
    this.autoSave = autosave;
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
    this.log("paragraph-ui.init");

    const action = this.actionFactory;
    const dispatch = this.dispatcher;

    this.uiModel = uiModel;
    let t = this;
    const wrapper = this.tinyWrapper;

    this.uiModel.config.text_formats.forEach(f =>
      wrapper.addTextFormat(f)
    );

    il.Util.addOnLoad(function () {
      $(window).resize(() => {
        wrapper.autoResize();
      });
    });

    wrapper.setContentCss(this.uiModel.config.content_css);

    this.log("css: " + this.uiModel.config.content_css);

    this.initMenu();

    this.log("set interval: " + this.uiModel.autoSaveInterval);
    this.autoSave.setInterval(this.uiModel.autoSaveInterval);
    this.autoSave.setOnAutoSave(() => {
      dispatch.dispatch(action.paragraph().editor().autoSave(wrapper.getText(), wrapper.getCharacteristic()));
    });
  }

  /**
   */
  reInit() {
  }

  initMenu() {
    const action = this.actionFactory;
    const dispatch = this.dispatcher;

    document.querySelectorAll("[data-copg-ed-type='par-button']").forEach(parButton => {
      switch (parButton.dataset.paction) {
        case "cancel":
          parButton.addEventListener("click", (event) => {
            dispatch.dispatch(action.page().editor().componentCancel());
          });
          break;
      }
    });

    // characteristic selection
    document.querySelectorAll("#ilAdvSelListTable_style_selection li").forEach(li => {
      let cl;
      li.removeAttribute("onclick");
      li.addEventListener("click", (event) => {
        cl = li.querySelector(".ilCOPgEditStyleSelectionItem").querySelector("h1,h2,h3,div").classList[0];
        console.log(cl);
        cl = cl.split("_");
        cl = cl[cl.length - 1];
        this.setParagraphClass(cl);
      });
    });
  }

  //
  // PORTED STUFF
  //

  content_css = '';
  current_td = "";
  edit_ghost = null;
  pc_id_str = '';
  tds = {};
  tinyinit = false;
  ed_para = null;


  ////
  //// Text editor commands
  ////

  cmdCancel()
  {
    const pcId = this.page_model.getCurrentPCId();
    const undo_pc_model = this.page_model.getUndoPCModel(pcId);

    if (this.page_model.getComponentState() === this.page_model.STATE_COMPONENT_EDIT) {
      this.setParagraphClass(undo_pc_model.characteristic);
      this.tinyWrapper.setContent(
        undo_pc_model.text,
        undo_pc_model.characteristic
      );
    }

    this.tinyWrapper.stopEditing();
  }

  cmdSpan(t) {
    this.log("paragraph-ui.cmdSpan " + t);
    this.tinyWrapper.toggleFormat(t);
  }

  cmdSup() {
    this.tinyWrapper.toggleFormat('Sup');
  }

  cmdSub() {
    this.tinyWrapper.toggleFormat('Sub');
  }

  cmdRemoveFormat() {
    this.tinyWrapper.removeFormat();
  }

  cmdIntLink(b, e, content)
  {
    this.addBBCode(b, e, false, content);
  }

  getSelection(){
    let ed = tinyMCE.get('tinytarget');
    ed.focus();
    return ed.selection.getContent();
  }

  addBBCode(stag, etag, clearselection, content)
  {
    let ed = tinyMCE.get('tinytarget'), r, rcopy;
    ed.focus();
    if (!content) {
      content = "";
    }
    if (ed.selection.getContent() === "")
    {
      stag = stag + content;
      rcopy = ed.selection.getRng(true).cloneRange();
      var nc = stag + ed.selection.getContent() + etag;
      ed.selection.setContent(nc);
      ed.focus();
      r =  ed.dom.createRng();
      if (rcopy.endContainer.nextSibling) // usual text node
      {
        if (rcopy.endContainer.nextSibling.nodeName !== "P")
        {
          r.setEnd(rcopy.endContainer.nextSibling, stag.length);
          r.setStart(rcopy.startContainer.nextSibling, stag.length);
          ed.selection.setRng(r);
        }
        else
        {
          r.setStart(rcopy.endContainer.firstChild, stag.length);
          r.setEnd(rcopy.endContainer.firstChild, stag.length);
          ed.selection.setRng(r);
        }
      }
      else if (rcopy.endContainer.firstChild) // e.g. when being in an empty list node
      {
        r.setEnd(rcopy.endContainer.firstChild, stag.length);
        r.setStart(rcopy.startContainer.firstChild, stag.length);
        ed.selection.setRng(r);
      }
      ed.selection.setRng(r);
    }
    else
    {
      if (clearselection) {
        ed.selection.setContent(stag + etag);
      }
      else {
        ed.selection.setContent(stag + ed.selection.getContent() + etag);
      }
    }
    this.autoResize(ed);
  }

  cmdWikiLink()
  {
    this.addBBCode('[[', ']]');
  }

  cmdTex()
  {
    this.addBBCode('[tex]', '[/tex]');
  }

  cmdFn()
  {
    this.addBBCode('[fn]', '[/fn]');
  }

  cmdKeyword()
  {
    this.addBBCode('[kw]', '[/kw]');
  }

  cmdExtLink()
  {
    this.addBBCode('[xln url="http://"]', '[/xln]');
  }

  cmdUserLink()
  {
    this.addBBCode('[iln user="' + this.uiModel.config.user + '"/]', '');
  }

  cmdAnc()
  {
    this.addBBCode('[anc name=""]', '[/anc]');
  }

  cmdBList() {
    this.tinyWrapper.bulletList();
  }

  cmdNList() {
    this.tinyWrapper.numberedList();
  }

  cmdListIndent() {
    this.tinyWrapper.listIndent();
  }

  cmdListOutdent() {
    this.tinyWrapper.listOutdent();
  }

  setParagraphClass(i) {
    this.log("setParagraphClass");
    this.log(i);
    const fc = document.querySelector(".ilTinyParagraphClassSelector .dropdown button");
    console.log(fc);
    if (fc) {
      console.log("SETTin DROP DOWN BUTTON: " + i)
      fc.firstChild.textContent = i + " ";
    }
    this.tinyWrapper.setParagraphClass(i);
  }

  ////
  //// Content modifier
  ////

  /**
   * Get content to be sent per ajax to server.
   */
  getContentForSaving()
  {
    let ed = tinyMCE.get('tinytarget');
    let cl = ed.dom.getRoot().className;
    let c = ed.getContent();

    c = this.p2br(c);

    // add wrapping div with style class
    c = "<div id='" + this.pc_id_str + "' class='" + cl + "'>" + c + "</div>";

    return c;
  }




  ////
  //// Tiny/text area/menu handling
  ////






  updateMenuButtons()
  {
    return;                                       // characteristic should be set from model
    let ed = tinyMCE.get('tinytarget');
    // update buttons
    let cnode = ed.selection.getNode();
    while (cnode)
    {
      if (cnode.parentNode &&
        cnode.parentNode.nodeName.toLowerCase() === "body" &&
        cnode.nodeName.toLowerCase() === "div")
      {
        var st = cnode.className.substring(15);
        il.AdvancedSelectionList.selectItem('style_selection', st);
      }
      cnode = cnode.parentNode;
    }
  }

  ////
  //// Table editing
  ////

  editTD(id)
  {
    this.editParagraph(id, 'td', false);
  }

  editNextCell()
  {
    // check whether next cell exists
    let cdiv = this.current_td.split("_");
    let next = "cell_" + cdiv[1] + "_" + (parseInt(cdiv[2]) + 1);
    let nobj = document.getElementById("div_" + next);
    if (nobj == null)
    {
      next = "cell_" + (parseInt(cdiv[1]) + 1) + "_0";
      nobj = document.getElementById("div_" + next);
    }
    if (nobj != null)
    {
      this.editParagraph(next, "td", false);
    }
  }

  editPreviousCell()
  {
    // check whether next cell exists
    let prev = "";
    let cdiv = this.current_td.split("_");
    if (parseInt(cdiv[2]) > 0)
    {
      prev = "cell_" + cdiv[1] + "_" + (parseInt(cdiv[2]) - 1);
      let pobj = document.getElementById("div_" + prev);
    }
    else if (parseInt(cdiv[1]) > 0)
    {
      let p = "cell_" + (parseInt(cdiv[1]) - 1) + "_0";
      let o = document.getElementById("div_" + p);
      let i = 0;
      while (o != null)
      {
        pobj = o;
        prev = p;
        p = "cell_" + (parseInt(cdiv[1]) - 1) + "_" + i;
        o = document.getElementById("div_" + p);
        i++;
      }
    }
    if (prev !== "")
    {
      var pobj = document.getElementById("div_" + prev);
      if (pobj != null)
      {
        this.editParagraph(prev, "td", false);
      }
    }
  }

  handleDataTableCommand(type, command)
  {
    let pars = this.tds;
    pars["tab_cmd_type"] = type;
    pars["tab_cmd"] = command;
    pars["tab_cmd_id"] = current_row_col;
    this.sendCmdRequest("saveDataTable", this.ed_para, null,
      pars,
      false, null, null);
  }



  // we got the content for editing per ajax
  loadCurrentParagraphIntoTiny(switched) {
    const pcId = this.page_model.getCurrentPCId();
    const pc_model = this.page_model.getPCModel(pcId);
    this.pc_id_str = pcId;
    this.tinyWrapper.setContent(pc_model.text);
    this.setParagraphClass(pc_model.characteristic);
  }


  reInitUI() {
    il.Tooltip.init();
    il.COPagePres.updateQuestionOverviews();
    if (il.AdvancedSelectionList != null)
    {
      il.AdvancedSelectionList.init['style_selection']();
      il.AdvancedSelectionList.init['char_style_selection']();
    }
    il.copg.editor.reInitUI();
  }

  // default callback for successfull ajax request, reloads page content
  pageReloadAjaxSuccess(o)
  {
    if(o.responseText !== undefined)
    {
      let edit_div = document.getElementById('il_EditPage');

      if (typeof il == 'undefined'){
        il = o.argument.il;
      }
      removeToolbar();
      $("#ilPageEditTopActionBar").css("visibility", "");
      $('#il_EditPage').replaceWith(o.responseText);
      this.reInitUI();
      il.IntLink.refresh();
      if (o.argument.osd_text && o.argument.osd_text != "") {
        OSDNotifier = OSDNotifications({
          initialNotifications: [{
            notification_osd_id: 123,
            valid_until: 0,
            visible_for: 3,
            data: {
              title: "",
              link: false,
              iconPath: false,
              shortDescription: o.argument.osd_text,
              handlerParams: {
                osd: {
                  closable: false
                }
              }
            }
          }]
        });
      }
    }
  }

  insertJSAtPlaceholder(cmd_id)
  {
    clickcmdid = cmd_id;
    let pl = document.getElementById('CONTENT' + cmd_id);
    pl.style.display = 'none';
    doActionForm('cmd[exec]', 'command', 'insert_par', '', 'PageContent', '');
  }

  ////
  //// Various stuff, needs to be reorganised
  ////

  renderQuestions() {
    // get all spans
    obj = document.getElementsByTagName('div')

    // run through them
    for (var i = 0; i < obj.length; i++) {
      // find all questions
      if (/ilc_question_/.test(obj[i].className)) {
        var id = obj[i].id;
        if (id.substr(0, 9) == "container") {
          // re-draw
          id = id.substr(9);
          eval("renderILQuestion" + id + "()");
        }
      }
    }
  }

  removeRedundantContent() {
    let k, d,
      darr = this.pc_id_str.split(";");

    for (k in darr) {
      if (darr[k] !== this.ed_para) {
        d = document.getElementById("CONTENT" + darr[k]);
        if (d != null) {
          d.style.display = 'none';
        }
        d = document.getElementById("TARGET" + darr[k]);
        if (d != null) {
          d.style.display = 'none';
        }
      }
    }
  }

  extractHierId(id)
  {
    var i = id.indexOf(":");
    if (i > 0)
    {
      id = id.substr(0, i);
    }

    return id;
  }


  insertParagraph(pcid, after_pcid, content = "", characteristic = "Standard") {
    this.log("paragraph-ui.insertParagraph");
    this.pageModifier.insertComponentAfter(after_pcid, pcid, "Paragraph", content, "Paragraph");
    let content_el = document.querySelector("[data-copg-ed-type='pc-area'][data-pcid='" + pcid + "']");
    this.showToolbar();
    this.tinyWrapper.initInsert(content_el, () => {
      this.tinyWrapper.setContent(content, characteristic);
      this.setParagraphClass(characteristic);
      this.setSectionClassSelector(this.getSectionClass(pcid));
    }, () => {
      this.autoSave.handleAutoSaveKeyPressed();
    }, () => {
      this.switchToPrevious();
    }, () => {
      this.switchToNext();
    });
    this.updateMenuButtons();
    this.tinyinit = true;
  }

  performAutoSplit(pcid, text, characteristic, newParagraphs) {
    let afterPcid = pcid;
    this.tinyWrapper.setContent(text, characteristic);
    for (let k = 0; k < newParagraphs.length; k++) {
      this.tinyWrapper.stopEditing();
      this.insertParagraph(newParagraphs[k].pcid, afterPcid, newParagraphs[k].model.text, characteristic);
      afterPcid = newParagraphs[k].pcid;
    }
  }

  handleSaveOnInsert() {
    this.tinyWrapper.stopEditing();
  }

  handleSaveOnEdit() {
    this.handleSaveOnInsert();
  }

  getSwitchParameters() {
    return {
      text: this.tinyWrapper.getText(),
      characteristic: this.tinyWrapper.getCharacteristic()
    }
  }

  switchToPrevious() {
    this.log("paragraph-ui switchToPrevious");
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    const cpcid = this.page_model.getCurrentPCId();
    let found = false;
    let previousPcid = null;
    let previousHierId = null;
    document.querySelectorAll("[data-copg-ed-type='pc-area']").forEach((el) => {
      const pcid = el.dataset.pcid;
      const hierid = el.dataset.hierid;
      const cname = el.dataset.cname;
      if (cname === "Paragraph") {
        if (!found && cpcid === pcid) {
          found = true;
        }
        if (!found) {
          previousPcid = pcid;
          previousHierId = hierid;
        }
      }
    });
    if (previousPcid) {
      dispatch.dispatch(action.page().editor().componentSwitch(
        "Paragraph",
        this.page_model.getComponentState(),
        this.page_model.getCurrentPCId(),
        this.getSwitchParameters(),
        previousPcid,
        previousHierId));
    }
  }

  switchToNext() {
    this.log("paragraph-ui switchToNext");
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    const cpcid = this.page_model.getCurrentPCId();
    let found = false;
    let nextPcid = null;
    let nextHierId = null;
    document.querySelectorAll("[data-copg-ed-type='pc-area']").forEach((el) => {
      const pcid = el.dataset.pcid;
      const hierid = el.dataset.hierid;
      const cname = el.dataset.cname;
      if (cname === "Paragraph") {
        if (found && !nextPcid) {
          nextPcid = pcid;
          nextHierId = hierid;
        }
        if (!found && cpcid === pcid) {
          found = true;
        }
      }
    });
    if (nextPcid) {
      dispatch.dispatch(action.page().editor().componentSwitch(
        "Paragraph",
        this.page_model.getComponentState(),
        this.page_model.getCurrentPCId(),
        this.getSwitchParameters(),
        nextPcid,
        nextHierId));
    }
  }

  editParagraph(pcId, hierId, mode, switched)
  {
    this.log("paragraph-ui.editParagraph");
    let content_el = document.querySelector("[data-copg-ed-type='pc-area'][data-pcid='" + pcId + "']");
    let pc_model = this.page_model.getPCModel(pcId);
    this.tinyWrapper.initEdit(content_el, pc_model.text, pc_model.characteristic, () => {
      this.showToolbar();
      this.setParagraphClass(pc_model.characteristic);
      this.updateMenuButtons();
      this.setSectionClassSelector(this.getSectionClass(pcId));
    }, () => {
      this.autoSave.handleAutoSaveKeyPressed();
    }, () => {
      this.switchToPrevious();
    }, () => {
      this.switchToNext();
    });
  }

  eventT(ed)
  {
    // window vs document
    //	console.log(window);
    //	console.log(tinymce.dom.Event);
    tinymce.dom.Event.add(tinymce.dom.doc, 'mousedown',
      function() {console.log("mouse down");}
      , false);
  }

  doActionForm(cmd, command, value, target, type, char)
  {
//    if (cmd_called) return;
    //alert("-" + cmd + "-" + command + "-" + value + "-" + target + "-"+ type + "-" + char + "-");
    //alert(clickcmdid);
    //-cmd[exec]-command-edit--
    doCloseContextMenuCounter = 2;

    if(cmd=="cmd[exec]")
    {
      cmd = "cmd[exec_"+clickcmdid+"]";
    }

    if (command=="command")
    {
      command += this.extractHierId(clickcmdid);
    }

    if (value=="edit" && type=="Paragraph" && char != "Code")
    {
      this.editParagraph(clickcmdid, 'edit', false);
      return false;
    }

    if (value == 'insert_par')
    {
      this.editParagraph(clickcmdid, 'insert', false);
      return false;
    }

    if (value=="delete")
    {
      if(!confirm(confirm_delete))
      {
        menuBlocked = true;
        setTimeout("nextMenuClick()",500);
        return;
      }
      menuBlocked = true;
      setTimeout("nextMenuClick()",500);
    }

    obj = document.getElementById("cmform");
    let hid_target = document.getElementById("cmform_target");
    hid_target.value = target;
    let hid_cmd = document.getElementById("cmform_cmd");
    hid_cmd.name = command;
    hid_cmd.value = value;
    let hid_exec = document.getElementById("cmform_exec");
    hid_exec.name = cmd;

//    cmd_called = true;

    if (ccell)
    {
      var loadergif = document.createElement('img');
      loadergif.src = "./templates/default/images/loader.svg";
      loadergif.border = 0;
      //loadergif.style.position = 'absolute';
      ccell.bgColor='';
      ccell.appendChild(loadergif);
    }
    if (value === 'cut' || value === 'delete' || value === 'paste' || value === 'copy' || value === 'activate' || value === 'deactivate') {
//      cmd_called = false;
      var args= {};
      if (value === 'cut') {
        args = {osd_text: il.Language.txt("cont_sel_el_cut_use_paste")};
      }
      if (value === 'copy') {
        args = {osd_text: il.Language.txt("cont_sel_el_copied_use_paste")};
      }
      this.sendCmdRequest(value, clickcmdid, "", {},
        true, args, this.pageReloadAjaxSuccess);
      //		console.log(value);
      //		console.log(obj);
      return;
    }

    obj.submit();
  }

  ccell = null;

  M_in(cell)
  {
//    if (cmd_called) return;
    doCloseContextMenuCounter=-1;
    ccell = cell;
  }

  M_out(cell)
  {
//    if (cmd_called) return;
    doCloseContextMenuCounter=5;
    ccell = null;
  }

  ilEditMultiAction(cmd)
  {
    if (cmd === "selectAll")
    {
      let divs = $("div.il_editarea");
      if (divs.length > 0)
      {
        for (var i = 0; i < divs.length; i++)
        {
          sel_edit_areas[divs[i].id] = true;
          divs[i].className = "il_editarea_selected";
        }
      }
      else
      {
        divs = $("div.il_editarea_selected");
        for (var i = 0; i < divs.length; i++)
        {
          sel_edit_areas[divs[i].id] = false;
          divs[i].className = "il_editarea";
        }
      }

      return false;
    }


    let hid_exec = document.getElementById("cmform_exec");
    hid_exec.name = "cmd[" + cmd + "]";
    let hid_cmd = document.getElementById("cmform_cmd");
    hid_cmd.name = cmd;
    form = document.getElementById("cmform");

    var sel_ids = "";
    var delim = "";
    for (var key in sel_edit_areas)
    {
      if (sel_edit_areas[key])
      {
        sel_ids = sel_ids + delim + key.substr(7);
        delim = ";";
      }
    }

    let hid_target = document.getElementById("cmform_target");
    hid_target.value = sel_ids;

    form.submit();

    return false;
  }

  //
  // js paragraph editing
  //

  // copied from TinyMCE editor_template_src.js
  showToolbar() {
    let obj;
    const tiny = this.tinyWrapper;
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    const ef = action.paragraph().editor();

    //#0017152
    $('#tinytarget_ifr').contents().find("html").attr('lang', $('html').attr('lang'));
    $('#tinytarget_ifr').contents().find("html").attr('dir', $('html').attr('dir'));

//    $("#tinytarget_ifr").parent().css("border-width", "0px");
//    $("#tinytarget_ifr").parent().parent().parent().css("border-width", "0px");


    this.toolSlate.setContentFromComponent("Paragraph", "menu");

    document.querySelectorAll("[data-copg-ed-type='par-action']").forEach(char_button => {
      const actionType = char_button.dataset.copgEdAction;
      switch (actionType) {

        case ACTIONS.SELECTION_FORMAT:
          const format = char_button.dataset.copgEdParFormat;
          char_button.addEventListener("click", (event) => {
            dispatch.dispatch(action.paragraph().editor().selectionFormat(format));
          });
          break;

        case ACTIONS.PARAGRAPH_CLASS:
          const par_class = char_button.dataset.copgEdParClass;
          char_button.addEventListener("click", (event) => {
            dispatch.dispatch(action.paragraph().editor().paragraphClass(par_class));
          });
          break;

        case ACTIONS.SECTION_CLASS:
          const sec_class = char_button.dataset.copgEdParClass;
          char_button.addEventListener("click", (event) => {
            dispatch.dispatch(action.paragraph().editor().sectionClass(
              this.tinyWrapper.getText(),
              this.tinyWrapper.getCharacteristic(),
              this.getSectionClass(this.page_model.getCurrentPCId()),
              sec_class
            ));
          });
          break;

        case ACTIONS.SAVE_RETURN:
          char_button.addEventListener("click", (event) => {
            dispatch.dispatch(ef.saveReturn(tiny.getText(), tiny.getCharacteristic()));
          });
          break;

        default:
          let map = {};
          map[ACTIONS.SELECTION_REMOVE_FORMAT] = ef.selectionRemoveFormat();
          map[ACTIONS.SELECTION_KEYWORD] = ef.selectionKeyword();
          map[ACTIONS.SELECTION_TEX] = ef.selectionTex();
          map[ACTIONS.SELECTION_ANCHOR] = ef.selectionAnchor();
          map[ACTIONS.LIST_BULLET] = ef.listBullet();
          map[ACTIONS.LIST_NUMBER] = ef.listNumber();
          map[ACTIONS.LIST_OUTDENT] = ef.listOutdent();
          map[ACTIONS.LIST_INDENT] = ef.listIndent();
          map[ACTIONS.LINK_WIKI_SELECTION] = ef.linkWikiSelection();
          map[ACTIONS.LINK_WIKI] = ef.linkWiki();
          map[ACTIONS.LINK_INTERNAL] = ef.linkInternal();
          map[ACTIONS.LINK_EXTERNAL] = ef.linkExternal();
          map[ACTIONS.LINK_USER] = ef.linkUser();
          map[PAGE_ACTIONS.COMPONENT_CANCEL] = action.page().editor().componentCancel();
          char_button.addEventListener("click", (event) => {
            dispatch.dispatch(map[actionType]);
          });
          break;
      }
    });
  }


  removeToolbar () {
    //console.log("removing toolbar");
    if (this.menu_panel) {
      let obj = document.getElementById('iltinymenu');
      $(obj).remove();
      $("#copg-editor-help").css("display", "");
      $(".il_droparea").css('visibility', '');

      this.menu_panel = null;

      // this element exists, if internal link panel has been clicked
      obj = document.getElementById('ilEditorPanel_c');
      if (obj && obj.parentNode) {
        $(obj.parentNode).remove();
      }

      // this element still exists, if interna link panel has not been clicked
      obj = document.getElementById('ilEditorPanel');
      if (obj && obj.parentNode) {
        $(obj.parentNode).remove();
      }
    }
  }

  autoSaveStarted() {
    document.querySelector("[data-copg-ed-action='save.return']").disabled = true;
    document.querySelector("[data-copg-ed-action='component.cancel']").disabled = true;
    this.autoSave.displayAutoSave(il.Language.txt("cont_saving"));
  }

  autoSaveEnded() {
    document.querySelector("[data-copg-ed-action='save.return']").disabled = false;
    document.querySelector("[data-copg-ed-action='component.cancel']").disabled = false;
    this.autoSave.displayAutoSave("&nbsp;");
  }

  replaceRenderedParagraph(pcid, content) {
    const pcarea = document.querySelector("[data-copg-ed-type='pc-area'][data-pcid='" + pcid + "']");
    const children = Array.from(pcarea.childNodes);
    let cnt = 0;

    // we remove all children except the first one which is the EditLabel div
    children.forEach(function(item){
      if (cnt > 0) {
        item.remove();
      }
      cnt++;
    });
    /*
    pcarea.querySelectorAll("div", (d) => {
    })
    const contentDiv = pcarea.getElementsByTagName('div')[1];
    console.log(pcid);
    console.log(pcarea);
    console.log(contentDiv);
    contentDiv.remove();*/
    pcarea.innerHTML = pcarea.innerHTML + content;
  }

  showLastUpdate(last_update) {
    this.autoSave.displayAutoSave(last_update);
  }

  setSectionClass(pcid, characteristic) {
    const currentPar = document.querySelector("[data-copg-ed-type='pc-area'][data-pcid='" + pcid + "']");
    console.log(currentPar);
    const parentComp = currentPar.parentNode.closest("[data-copg-ed-type='pc-area']");
    console.log(parentComp);
    if (parentComp && parentComp.dataset.cname === "Section") {
      parentComp.childNodes[1].className = "ilc_section_" + characteristic + " ilCOPageSection";
    }
    this.setSectionClassSelector(characteristic);
  }

  /**
   * Get outer section class for paragraph
   * @param {string} pcid paragraph pcid
   */
  getSectionClass(pcid) {
    let secClass = "";
    const currentPar = document.querySelector("[data-copg-ed-type='pc-area'][data-pcid='" + pcid + "']");
    const parentComp = currentPar.parentNode.closest("[data-copg-ed-type='pc-area']");
    if (parentComp && parentComp.dataset.cname === "Section") {
      parentComp.childNodes[1].classList.forEach((c) => {
        if (c.substr(0, 12) === "ilc_section_") {
          secClass = c.substr(12);
        }
      });
    }
    return secClass;
  }

  setSectionClassSelector(i) {
    if (i === "") {
      i = il.Language.txt("cont_no_block");
    }
    const fc = document.querySelector(".ilSectionClassSelector .dropdown button");
    if (fc) {
      fc.firstChild.textContent = i + " ";
    }
  }

}
