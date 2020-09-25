import ACTIONS from "../actions/paragraph-action-types.js";
import PAGE_ACTIONS from "../../page/actions/page-action-types.js";
import TinyWrapper from "./tiny-wrapper.js";

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
  constructor(client, dispatcher, actionFactory, page_model, toolSlate, pageModifier) {
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.page_model = page_model;
    this.toolSlate = toolSlate;
    this.tinyWrapper = new TinyWrapper();
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
    this.log("paragraph-ui.init");

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
  edit_status = false;
  insert_status = false;
  current_td = "";
  edit_ghost = null;
  ghost_debugged = false;
  quick_insert_id = null;
  pc_id_str = '';
  pasting = false;
  tds = {};
  tinyinit = false;
  ed_para = null;


  ////
  //// Debug/Error Functions
  ////

  switchDebugGhost() {
    if (!this.ghost_debugged) {
      $("#tinytarget_ifr").parent().parent().parent().parent().addClass("ilNoDisplay");
      this.ghost_debugged = true;
    } else {
      $("#tinytarget_ifr").parent().parent().parent().parent().removeClass("ilNoDisplay");
      this.ghost_debugged = false;
    }
  }

  debugContent() {
    let content = tinyMCE.get('tinytarget').getContent();
    alert(content);
    alert(this.getContentForSaving());
  }

  displayError(str) {
    // build error string
    let estr, show_content = true;
    estr = "";

    if (this.error_str.substr(0, 10) == "nocontent#") {
      this.error_str = this.error_str.substr(10);
      show_content = false;
    }
    estr = estr + this.error_str;
    if (show_content) {
      estr = estr + "<p><b>Content</b></p>";
      let content = tinyMCE.get('tinytarget').getContent();
      content = content.split("<").join("&lt;");
      content = content.split(">").join("&gt;");
      estr = estr + content;
    }

    il.Modal.dialogue({
      id: "il_pg_error_modal",
      show: true,
      header: il.Language.txt("cont_error"),
      buttons: {}
    });
    $("#il_pg_error_modal .modal-body").html(estr + "<br />");
  }

  ////
  //// Setters/getters
  ////


  setEditStatus(status) {
    this.edit_status = status;
  }

  getEditStatus() {
    return this.edit_status;
  }

  setInsertStatus(status) {
    if (status) {
      this.quick_insert_id = null;
    }
    this.insert_status = status;
  }

  getInsertStatus() {
    return this.insert_status;
  }

  ////
  //// Text editor commands
  ////

  cmdSave(switch_to)
  {
    $('#ilsaving').removeClass("ilNoDisplay");

    // table editing
    if (this.current_td !== "")
    {
      var ed = tinyMCE.get('tinytarget');
      this.autoResize(ed);
      this.setEditStatus(false);
      //ilFormSend("saveDataTable", ed_para, null, null);
      var pars = this.tds;
      this.sendCmdRequest("saveDataTable", this.ed_para, null,
        pars,
        false, null, null);
      return;
    }

    if (this.getInsertStatus())
    {
      let content = this.getContentForSaving();
      let style_class = il.AdvancedSelectionList.getHiddenInput('style_selection');

      if (this.ed_para === "")
      {
        alert("Error: Calling insertJS without ed_para.");
        return;
      }

      this.sendCmdRequest("insertJS", this.ed_para, null,
        {ajaxform_content: content,
          ajaxform_char: style_class,
          insert_at_id: this.ed_para,
          quick_save: 1},
        true, {switch_to: switch_to}, this.quickInsertAjaxSuccess);
    }
    else
    {
      let content = this.getContentForSaving();
      let style_class = il.AdvancedSelectionList.getHiddenInput('style_selection');

      if (this.pc_id_str === "")
      {
        alert("Error: Calling saveJS without pc_id_str.");
        return;
      }
      this.sendCmdRequest("saveJS", this.pc_id_str, null,
        {ajaxform_content: content,
          pc_id_str: this.pc_id_str,
          ajaxform_char: style_class,
          quick_save: 1},
        true, {switch_to: switch_to}, this.quickSavingAjaxSuccess);

    }
  }

  cmdSaveReturn(and_new)
  {
    $('#ilsaving').removeClass("ilNoDisplay");

    let ed = tinyMCE.get('tinytarget');
    this.autoResize(ed);
    this.setEditStatus(false);
    if (this.current_td !== "")
    {
      //ilFormSend("saveDataTable", ed_para, null, null);
      var pars = this.tds;
      pars.save_return = 1;
      this.sendCmdRequest("saveDataTable", this.ed_para, null,
        pars,
        false, null, null);
    }
    else if (this.getInsertStatus() && !this.quick_insert_id)
    {
      let content = this.getContentForSaving();
      let style_class = il.AdvancedSelectionList.getHiddenInput('style_selection');

      if (this.ed_para === "")
      {
        alert("Error2: Calling insertJS without ed_para.");
        return;
      }

      this.sendCmdRequest("insertJS", this.ed_para, null,
        {ajaxform_content: content,
          pc_id_str: this.pc_id_str,
          insert_at_id: this.ed_para,
          ajaxform_char: style_class},
        true, {and_new: and_new}, this.saveReturnAjaxSuccess);
    }
    else
    {
      let content = this.getContentForSaving();
      let style_class = il.AdvancedSelectionList.getHiddenInput('style_selection');

      if (this.pc_id_str === "")
      {
        alert("Error2: Calling saveJS without pc_id_str.");
        return;
      }

      this.sendCmdRequest("saveJS", this.pc_id_str, null,
        {ajaxform_content: content,
          pc_id_str: this.pc_id_str,
          ajaxform_char: style_class},
        true, {and_new: and_new}, this.saveReturnAjaxSuccess);
    }
  }

  switchTo(pc_id)
  {
    this.cmdSave(pc_id);
  }

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

    let ed = tinyMCE.get('tinytarget');
    //this.autoResize(ed);
    this.tinyWrapper.copyInputToGhost(false);
    //this.tinyWrapper.removeTiny();
    this.tinyWrapper.hide();
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

  cmdBList()
  {
    let ed = tinyMCE.get('tinytarget');
    ed.focus();
    ed.execCommand('InsertUnorderedList', false);
    this.fixListClasses(true);
    this.autoResize(ed);
  }

  cmdNList()
  {
    let ed = tinyMCE.get('tinytarget');
    ed.focus();
    ed.execCommand('InsertOrderedList', false);
    this.fixListClasses(true);
    this.autoResize(ed);
  }

  cmdListIndent()
  {
    let blockq = false, range, ed = tinyMCE.get('tinytarget');

    ed.focus();
    ed.execCommand('Indent', false);
    range = ed.selection.getRng(true);

    // if path contains blockquote, top level list has been indented -> undo, see bug #0016243
    let cnode = range.startContainer;
    while (cnode = cnode.parentNode) {
      if (cnode.nodeName === "BLOCKQUOTE") {
        blockq = true;
      }
    }
    if (blockq) {
      ed.execCommand('Undo', false);
    }

    //tinyMCE.execCommand('mceCleanup', false, 'tinytarget');
    this.fixListClasses(false);
    this.autoResize(ed);
  }

  cmdListOutdent()
  {
    let ed = tinyMCE.get('tinytarget');
    ed.focus();
    ed.execCommand('Outdent', false);
    this.fixListClasses(true);
    this.autoResize(ed);
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

  ////
  //// Ajax calls functions
  ////

  sendCmdRequest(cmd, source_id, target_id, par, ajax, args, success_cb)
  {
    par['ajaxform_hier_id'] = this.extractHierId(source_id);
    par['command' + this.extractHierId(source_id)] = cmd;
    par['target[]'] = target_id;
    if (cmd === "insertJS")
    {
      par['cmd[create_par]'] = "OK";
    }
    else if (cmd !== "saveDataTable")
    {
      par['cmd[exec_' + source_id + ']'] = "OK";
    }
    this.sendFormRequest(par, ajax, args, success_cb);
  }

  // send request
  //sendRequest: function(cmd, ("command" + extractHierId(source_id) = cmd)
  // source_id, ("ajaxform_hier_id" = extractHierId(source_id);
  // target_id (target[] = target_id), mode)
  // insertJS: "cmd[create_par] = "OK"", ansonsten (außer "saveDataTable"): "cmd[exec_" + source_id + "]" = "OK"
  // saveJS, insertJS: "ajaxform_content" = tinyMCE.get('tinytarget').getContent();
  // saveJS, insertJS: "ajaxform_char" = il.AdvancedSelectionList.getHiddenInput('style_selection');
  //
  // 'saveDataTable': ajax false, ansonsten true
  sendFormRequest(par, ajax, args, success_cb)
  {
    let f = document.getElementById("ajaxform2");
    let k, par_el;

    while (f.hasChildNodes())
    {
      f.removeChild(f.firstChild);
    }

    console.log(par);

    for (k in par)
    {
      par_el = document.createElement('input');
      par_el.type = 'hidden';
      par_el.name = k;
      par_el.value = par[k];
      f.appendChild(par_el);
    }

    let url = f.action;

    if (!ajax)
    {
      // normal submit for submitting the whole form
      return f.submit();
    }
    else
    {
      // ajax saving
      var r = this.sendAjaxPostRequest('ajaxform2', url, args, success_cb);
    }
    return r;
  }

  // send request per ajax
  sendAjaxPostRequest(form_id, url, args, success_cb)
  {
    args.il = il;
    let cb =
      {
        success: success_cb,
        failure: this.handleAjaxFailure,
        argument: args
      };
    let form_str = YAHOO.util.Connect.setForm(form_id);
    let request = YAHOO.util.Connect.asyncRequest('POST', url, cb);

    return false;
  }

  handleAjaxFailure(o)
  {
  }


  // we got the content for editing per ajax
  loadCurrentParagraphIntoTiny(switched) {
    const pcId = this.page_model.getCurrentPCId();
    const pc_model = this.page_model.getPCModel(pcId);
    this.pc_id_str = pcId;
    this.tinyWrapper.setContent(pc_model.text);
    this.setParagraphClass(pc_model.characteristic);
  }


  // extract pc ids
  extractPCIdsFromResponse(str)
  {
    this.error_str = "";
    if (str.substr(0,3) === "###")
    {
      let end = str.indexOf("###", 3);
      this.pc_id_str = str.substr(3,
        end - 3);
      str = str.substr(end + 3,
        str.length - (end + 3));
    }
    else
    {
      this.error_str = str;
    }
    return str;
  }


  // quick saving has been done
  quickSavingAjaxSuccess(o)
  {
    $('#ilsaving').addClass("ilNoDisplay");
    this.extractPCIdsFromResponse(o.responseText);
    if (this.pc_id_str != "")
    {
      this.ed_para = this.pc_id_str;
    }
    if (this.error_str != "")
    {
      this.displayError(this.error_str);
    }
    else
    {
      if (typeof o.argument.switch_to !== 'undefined' &&
        o.argument.switch_to != null)
      {
        //console.log(o.argument.switch_to);
        this.copyInputToGhost(false);

        tinyMCE.get('tinytarget').setContent('');

        this.removeTiny();

        this.editParagraph(o.argument.switch_to, 'edit', true);
      }
    }
  }

  // quick insert has been done
  quickInsertAjaxSuccess(o)
  {
    $('#ilsaving').addClass("ilNoDisplay");
    if(o.responseText !== undefined)
    {
      this.extractPCIdsFromResponse(o.responseText);
      var pc_arr = this.pc_id_str.split(";");
      if (this.error_str !== "")
      {
        this.displayError(this.error_str);
      }
      else
      {
        this.setInsertStatus(false);
      }
    }
  }

  // default callback for successfull ajax request, reloads page content
  saveReturnAjaxSuccess(o)
  {
    if(o.responseText !== undefined)
    {
      let c = this.extractPCIdsFromResponse(o.responseText);

      if (this.pc_id_str != "")
      {
        this.ed_para = this.pc_id_str;
      }

      $('#ilsaving').addClass("ilNoDisplay");
      $("#ilPageEditTopActionBar").css("visibility", "");

      if (this.error_str != "")
      {
        this.displayError(this.error_str);
      }
      else
      {
        this.copyInputToGhost(false);
        this.removeTiny();
        removeToolbar();
        this.setInsertStatus(false);

        var edit_div = document.getElementById('il_EditPage');
        $('#il_EditPage').replaceWith(c);
        this.reInitUI();


        // we do not need this anymore, otherwise it will lead to multiple events on the iln button, see bug #21704
        //il.IntLink.refresh();

        // perform direct insert
        if (o.argument.and_new) {
          clickcmdid = this.ed_para;
          doActionForm('cmd[exec]', 'command', 'insert_par', '', 'PageContent', '');
        }
      }
    }
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


  insertParagraph(pcid, after_pcid) {
    this.log("paragraph-ui.insertParagraph");
    this.pageModifier.insertComponentAfter(after_pcid, pcid, "Paragraph", "", "Paragraph");
    let content_el = document.querySelector("[data-copg-ed-type='pc-area'][data-pcid='" + pcid + "']");
    this.showToolbar();
    this.tinyWrapper.initInsert(content_el, () => {
      this.setParagraphClass("Standard");
    });
    this.updateMenuButtons();
    this.tinyinit = true;
  }

  handleSaveOnInsert() {
    this.tinyWrapper.copyInputToGhost(false);
    this.tinyWrapper.hide();
  }

  handleSaveOnEdit() {
    this.handleSaveOnInsert();
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
    });
    return;


    //this.setInsertStatus(false);

    // table editing mode (td)
    var moved = false;		// is edit area currently move from one td to another?
    if (mode === 'td')                                                              // MISSING
    {
      // if current_td already set, we must move editor to new td
      if (this.current_td !== "")
      {
        this.copyInputToGhost(true);
        this.copyInputToGhost(false);
        pdiv = document.getElementById('div_' + this.current_td);
        pdiv.style.minHeight = '';
        pdiv.style.minWidth = '';
        moved = true;
      }

      // get placeholder div
      pdiv = document.getElementById('div_' + div_id);
      pdiv_reg = YAHOO.util.Region.getRegion(pdiv);
      this.current_td = div_id;
      this.edit_ghost = "div_" + this.current_td;
    }

    if (switched)                                                                     // MISSING
    {
      let ta = document.getElementById('tinytarget');
      if (ta != null)
      {
        let ta_par = ta.parentNode;
        ta_par.removeChild(ta);
      }
    }

    // init tiny
    let resize = false;
    let show_path = false;
    let statusbar = false;


    var tinytarget = document.getElementById("tinytarget");

    let par_ui = this;

    // create new text area for tiny
    if (!moved)
    {
      this.showToolbar();
      this.updateMenuButtons();
      this.tinyinit = true;
    }
    else	// moved (table editing)
    {
      //prepareTinyForEditing;
      // this code line has been commented out
      // with 5.0, not really sure why it has been needed before
      //		tinyMCE.execCommand('mceToggleEditor', false, 'tinytarget');
      var ed = tinyMCE.get('tinytarget');
      ed.setContent(pdiv.innerHTML);
      this.splitBR();
      this.synchInputRegion();
      this.focusTiny(false);
//      cmd_called = false;
    }

    this.tinyinit = true;
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

  /**
   * Save paragraph
   */
  saveParagraph()
  {
    this.cmdSave();
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


}
