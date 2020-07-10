/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * paragraph ui
 */
export default class ParagraphUI {


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
   * @param {Model} model
   */
  constructor(client, dispatcher, actionFactory, page_model) {
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.page_model = page_model;
  }

  //
  // Initialisation
  //

  /**
   */
  init(uiModel) {
    this.uiModel = uiModel;
    let t = this;

    il.Util.addOnLoad(function () {
      $(window).resize(() => {
        t.autoResize();
      });
    });

    this.uiModel.config.text_formats.forEach(f =>
      this.addTextFormat(f)
    );

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
            dispatch.dispatch(action.paragraph().editor().cancel());
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
  minwidth = 50;
  minheight = 20;
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

  addTextFormat(f) {
    this.text_formats[f] = { inline: 'span', classes: 'ilc_text_inline_' + f };
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
    let ed = tinyMCE.get('tinytarget');
    this.autoResize(ed);
    this.setEditStatus(false);
    this.setInsertStatus(false);
    this.copyInputToGhost(false);
    this.removeTiny();
    this.hideToolbar();
    if (this.current_td == "")
    {
      this.sendCmdRequest("cancel", this.ed_para, null, {},
        true, {}, this.pageReloadAjaxSuccess);
    }
    else
    {
      this.sendCmdRequest("saveDataTable", this.ed_para, null,
        {cancel_update: 1}, null, null);
    }

  }

  setCharacterClass(i)
  {
    switch (i.hid_val)
    {
      case "Quotation":
      case "Comment":
      case "Accent":
        this.cmdSpan(i.hid_val);
        break;

      case "Code":
        this.cmdCode();
        break;

      default:
        this.cmdSpan(i.hid_val);
        break;
    }
    return false;
  }

  cmdSpan(t)
  {
    const stype = {Strong: '0', Emph: '1', Important: '2', Comment: '3',
      Quotation: '4', Accent: '5'};
    let ed = tinyMCE.get('tinytarget');

    tinymce.activeEditor.formatter.toggle(t);
    ed.focus();
    ed.selection.collapse(false);
    this.autoResize(ed);
  }

  cmdCode()
  {
    let ed = tinyMCE.get('tinytarget');

    tinymce.activeEditor.formatter.register('mycode', {
      inline : 'code'
    });
    ed.execCommand('mceToggleFormat', false, 'mycode');
    this.autoResize(ed);
  }

  cmdSup()
  {
    let ed = tinyMCE.get('tinytarget');

    ed.execCommand('mceToggleFormat', false, 'Sup');
    this.autoResize(ed);
  }

  cmdSub()
  {
    let ed = tinyMCE.get('tinytarget');

    ed.execCommand('mceToggleFormat', false, 'Sub');
    this.autoResize(ed);
  }

  cmdRemoveFormat()
  {
    let ed = tinyMCE.get('tinytarget');
    ed.focus();
    ed.execCommand('RemoveFormat', false);
    this.autoResize(ed);
  }

  cmdPasteWord()
  {
    let ed = tinyMCE.get('tinytarget');
    ed.focus();
    ed.execCommand('mcePasteWord');
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
    document.getElementById("ilAdvSelListAnchorText_style_selection").firstChild.textContent = i + " ";
    let ed = tinyMCE.activeEditor;
    ed.focus();
    let snode = ed.dom.getRoot();

    if (snode) {
      //snode.className = "ilc_text_block_" + i['hid_val'];
      snode.className = "ilc_text_block_" + i;
      snode.style.position = 'static';
    }
    this.autoResize(ed);
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

  // convert <p> tags to <br />
  p2br(c)
  {
    // remove <p> and \n
    c = c.split("<p>").join("");
    c = c.split("\n").join("");

    // convert </p> to <br />
    c = c.split("</p>").join("<br />");

    // remove trailing <br />
    if (c.substr(c.length - 6) == "<br />")
    {
      c = c.substr(0, c.length - 6);
    }

    return c;
  }


  /**
   * This function converts all <br /> into corresponding paragraphs
   * (server content comes with <br />, but tiny has all kind of issues
   * in "<br>" mode (e.g. IE cannot handle lists). So we use the more
   * reliable "<p>" mode of tiny.
   */
  splitBR()
  {
    let snode;
    let ed = tinyMCE.activeEditor;
    let r = ed.dom.getRoot();

    // STEP 1: Handle all top level <br />

    // make copy of root
    let rcopy = r.cloneNode(true);

    // remove all childs of top level
    for (var k = r.childNodes.length - 1; k >= 0; k--)
    {
      r.removeChild(r.childNodes[k]);
    }

    // cp -> current P
    let cp = ed.dom.create('p', {}, '');
    let cp_content = false; // has current P any content?
    let cc, pc; // cc: currrent child (top level), pc: P child

    // walk through root copy and add content to emptied original root
    for (var k = 0; k < rcopy.childNodes.length; k++)
    {
      cc = rcopy.childNodes[k];

      // handle Ps on top level
      // main purpose: convert <p> ...<br />...</p> to <p>...</p><p>...</p>
      if (cc.nodeName == "P")
      {
        // is there a current P with content? -> add it to top level
        if (cp_content)
        {
          r.appendChild(cp);
          cp = ed.dom.create('p', {}, '');
          cp_content = false;
        }

        // split all BRs into separate Ps on top level
        for (var i = 0; i < cc.childNodes.length; i++)
        {
          pc = cc.childNodes[i];
          if (pc.nodeName == "BR")
          {
            // append the current p an create a new one
            r.appendChild(cp);
            cp = ed.dom.create('p', {}, '');
            cp_content = false;
          }
          else
          {
            // append the content to the current p
            cp.appendChild(pc.cloneNode(true));
            cp_content = true;
          }
        }

        // append current p and create a new one
        if (cp_content)
        {
          r.appendChild(cp);
          cp = ed.dom.create('p', {}, '');
          cp_content = false;
        }
      }
      else if (cc.nodeName == "UL" || cc.nodeName == "OL")
      {
        // UL and OL are simply appended to the root
        if (cp_content)
        {
          r.appendChild(cp);
          cp = ed.dom.create('p', {}, '');
          cp_content = false;
        }
        r.appendChild(rcopy.childNodes[k].cloneNode(true));
      }
      else
      {
        cp.appendChild(rcopy.childNodes[k].cloneNode(true));
        cp_content = true;
      }
    }
    if (cp_content)
    {
      r.appendChild(cp);
    }

    // STEP 2: Handle all non-top level <br />
    // this is the standard tiny br splitting (which fails in top level Ps)
    /*		tinymce.each(ed.dom.select('br').reverse(), function(b) {
     try {
     var snode = ed.dom.getParent(b, 'p,li');
     ed.dom.split(snode, b);
     } catch (ex) {
     // IE can sometimes fire an unknown runtime error so we just ignore it
     }
     });*/
    this.splitTopBr();


    // STEP 3: Clean up

    // remove brs (normally all should have been handled above)
    var c = ed.getContent();
    c = c.split("<br />").join("");
    c = c.split("\n").join("");
    ed.setContent(c);
  }

  // split all span classes that are direct "children of themselves"
  // fixes bug #13019
  splitSpans() {

    let k, ed = tinyMCE.activeEditor, s,
      classes = ['ilc_text_inline_Strong','ilc_text_inline_Emph', 'ilc_text_inline_Important',
        'ilc_text_inline_Comment', 'ilc_text_inline_Quotation', 'ilc_text_inline_Accent'];

    for (var i = 0; i < classes.length; i++) {

      s = ed.dom.select('span[class="' + classes[i] + '"] > span[class="' + classes[i] + '"]');
      for (k in s) {
        ed.dom.split(s[k].parentNode, s[k]);
      }
    }
  }

  /**
   * This one ensures that the standard ILIAS list style classes
   * are assigned to list elements
   */
  fixListClasses(handle_inner_br)
  {
    let ed = tinyMCE.activeEditor, par, r;

    // return;

    ed.dom.addClass(tinyMCE.activeEditor.dom.select('ol'), 'ilc_list_o_NumberedList');
    ed.dom.addClass(tinyMCE.activeEditor.dom.select('ul'), 'ilc_list_u_BulletedList');
    ed.dom.addClass(tinyMCE.activeEditor.dom.select('li'), 'ilc_list_item_StandardListItem');

    if (handle_inner_br)
    {
      let rcopy = ed.selection.getRng(true);
      let target_pos = false;

      // get selection start p or li tag
      let st_cont = rcopy.startContainer.nodeName.toLowerCase();
      if (st_cont !== "p" && st_cont !== "li")
      {
        par = rcopy.startContainer.parentNode;
        if (par.nodeName.toLowerCase() === "body")
        {
          // starting from something like a text node under body
          // not really a parent anymore, but ok to get the previous sibling from
          par = rcopy.startContainer;
        }
        else
        {
          // starting from a deeper node in text
          while (par.parentNode &&
          par.nodeName.toLowerCase() !== "li" &&
          par.nodeName.toLowerCase() !== "p" &&
          par.nodeName.toLowerCase() !== "body")
          {
            par = par.parentNode;
            //console.log(par);
          }
        }
      }
      else
      {
        par = rcopy.startContainer;
      }
      //console.log(par);


      // get previous sibling
      var ps = par.previousSibling;
      if (ps)
      {
        if (ps.nodeName.toLowerCase() === "p" ||
          ps.nodeName.toLowerCase() === "li")
        {
          target_pos = ps;
        }
        if (ps.nodeName.toLowerCase() === "ul")
        {
          if (ps.lastChild)
          {
            target_pos = ps.lastChild;
          }
        }
      }
      else
      {
        //console.log("case d");
        // set selection to beginning
        r = ed.dom.getRoot();
        target_pos = r.childNodes[0];
      }
      if (this.splitTopBr())
      {
        //console.log("setting range");

        // set selection to start of first div
        if (target_pos)
        {
          r =  ed.dom.createRng();
          r.setStart(target_pos, 0);
          r.setEnd(target_pos, 0);
          ed.selection.setRng(r);
        }
      }
    }
  }

  splitTopBr()
  {
    let changed = false;

    let ed = tinyMCE.activeEditor;
    ed.getContent(); // this line is imporant and seems to fix some things
    tinymce.each(ed.dom.select('br').reverse(), function(b) {

      //console.log(b);
      //return;

      try {
        let snode = ed.dom.getParent(b, 'p,li');
        if (snode.nodeName !== "LI" &&
          snode.childNodes.length !== 1)
        {
          //				ed.dom.split(snode, b);

          function trim(node) {
            var i, children = node.childNodes;

            if (node.nodeType === 1 && node.getAttribute('_mce_type') === 'bookmark')
              return;

            for (i = children.length - 1; i >= 0; i--)
              trim(children[i]);

            if (node.nodeType !== 9) {
              // Keep non whitespace text nodes
              if (node.nodeType === 3 && node.nodeValue.length > 0) {
                // If parent element isn't a block or there isn't any useful contents for example "<p>   </p>"
                if (!t.isBlock(node.parentNode) || tinymce.trim(node.nodeValue).length > 0)
                  return;
              }

              if (node.nodeType === 1) {
                // If the only child is a bookmark then move it up
                children = node.childNodes;
                if (children.length === 1 && children[0] && children[0].nodeType === 1 && children[0].getAttribute('_mce_type') === 'bookmark')
                  node.parentNode.insertBefore(children[0], node);

                // Keep non empty elements or img, hr etc
                if (children.length || /^(br|hr|input|img)$/i.test(node.nodeName))
                  return;
              }

              t.remove(node);
            }
            return node;
          }

          let pe = snode;
          let e = b;
          if (pe && e) {
            var t = ed.dom, r = t.createRng(), bef, aft, pa;

            // Get before chunk
            r.setStart(pe.parentNode, t.nodeIndex(pe));
            r.setEnd(e.parentNode, t.nodeIndex(e));
            bef = r.extractContents();

            // Get after chunk
            r = t.createRng();
            r.setStart(e.parentNode, t.nodeIndex(e) + 1);
            r.setEnd(pe.parentNode, t.nodeIndex(pe) + 1);
            aft = r.extractContents();

            // Insert before chunk
            pa = pe.parentNode;
            pa.insertBefore(trim(bef), pe);
            //pa.insertBefore(bef, pe);

            // Insert after chunk
            pa.insertBefore(trim(aft), pe);
            //pa.insertBefore(aft, pe);
            t.remove(pe);

            //					return re || e;
            changed = true;
          }
        }

      } catch (ex) {
        // IE can sometimes fire an unknown runtime error so we just ignore it
      }
    });
    return changed;
  }

  // remove all divs (used after pasting)
  splitDivs()
  {
    // split all divs in divs
    let ed = tinyMCE.activeEditor;
    let divs = ed.dom.select('p > div');
    let k;
    for (k in divs)
    {
      ed.dom.split(divs[k].parentNode, divs[k]);
    }
  }

  ////
  //// Tiny/text area/menu handling
  ////

  prepareTinyForEditing (insert, switched)
  {
    var ed = tinyMCE.get('tinytarget');
    //tinyMCE.execCommand('mceAddControl', false, 'tinytarget');
    tinyMCE.execCommand('mceAddEditor', false, 'tinytarget');
    //console.log("prepareTiny");
    if (!switched)
    {
      this.showToolbar('tinytarget');
    }

    // todo tinynew
    //		tinyifr = document.getElementById("tinytarget_parent");
    //		tinyifr.style.position = "absolute";

    this.setEditStatus(true);
    this.setInsertStatus(insert);
    if (!insert)
    {
      this.focusTiny(false);
    }
    //this.autoScroll();
    if (this.current_td !== "")
    {
      this.copyInputToGhost(false);
    }
    else
    {
      this.copyInputToGhost(true);
    }
    this.synchInputRegion();
    this.updateMenuButtons();
  }

  focusTiny(delayed)
  {
    let timeout = 1;
    if (delayed)
    {
      timeout = 500;
    }

    setTimeout(function () {
      let ed = tinyMCE.get('tinytarget');
      if (ed)
      {
        let e = tinyMCE.DOM.get(ed.id + '_external');
        let r = ed.dom.getRoot();
        let fc = r.childNodes[0];
        if (r.className != null)
        {
          var st = r.className.substring(15);
          il.AdvancedSelectionList.selectItem('style_selection', st);
        }

        ed.getWin().focus();
      }
    }, timeout);
  }

  removeTiny() {
    tinyMCE.execCommand('mceRemoveEditor', false, 'tinytarget');
    let tt = document.getElementById("tinytarget");
    tt.style.display = 'none';
  }

  // set frame size of editor
  setEditFrameSize(width, height)
  {
    let tinyifr = document.getElementById("tinytarget_ifr");
    let tinytd = document.getElementById("tinytarget_tbl");
    tinyifr.style.width = width + "px";
    tinyifr.style.height = height + "px";

    $("#tinytarget_ifr").css("width", width + "px");
    $("#tinytarget_ifr").css("height", height + "px");

    this.ed_width = width;
    this.ed_height = height;
  }

  // copy input of tiny to ghost div in background
  copyInputToGhost(add_final_spacer)
  {
    let ed = tinyMCE.get('tinytarget');

    if (this.edit_ghost)
    {
      let pdiv = document.getElementById(this.edit_ghost);
      if (pdiv)
      {
        let cl = ed.dom.getRoot().className;
        let c = this.p2br(ed.getContent());
        if (this.current_td === "")
        {
          c = "<div style='position:static;' class='" + cl + "'>" + c + "</div>";
        }
        else
        {
          this.tds[this.current_td] =
            this.getContentForSaving();
        }
        let e = c.substr(c.length - 6);
        let b = c.substr(c.length - 12, 6);
        if (e === "</div>" && add_final_spacer)
        {
          // ensure at least one more line of space
          if (b !== "<br />") {
            c = c.substr(0, c.length - 6) + "<br />.</div>";
          } else {
            // this looks good under firefox. If this leads to problems on other
            // browsers, ".</div>" would be the alternative for this case (last new empty line)
            c = c.substr(0, c.length - 6) + "<br />.</div>";
          }

        }
        pdiv.innerHTML = c;
      }
    }
  }

  // synchs the size/position of the tiny to the space the ghost
  // object uses in the background
  synchInputRegion()
  {
    let back_el, dummy;

    if (this.current_td)
    {
      back_el = document.getElementById(this.edit_ghost);
      back_el = back_el.parentNode;
    }
    else
    {
      back_el = document.getElementById(this.edit_ghost);
    }

    if (!back_el) {
      return;
    }

    back_el.style.minHeight = this.minheight + "px";
    //		back_el.style.minWidth = this.minwidth + "px";

    // alex, 30 Dec 2011, see bug :
    // for reasons I do not understand, the above does not
    // work for IE7, even if minWidth is implemented there.
    // so we do this "padding" trick which works for all browsers
    if ($(back_el).width() < this.minwidth)
    {
      var new_pad = (this.minwidth - $(back_el).width()) / 2;
      back_el.style.paddingLeft = new_pad + "px";
      back_el.style.paddingRight = new_pad + "px";
    }
    else
    {
      back_el.style.paddingLeft = "";
      back_el.style.paddingRight = "";
    }

    let tinyifr = document.getElementById("tinytarget_ifr");
    tinyifr = tinyifr.parentNode;
    $(tinyifr).css("position", "absolute");

    // make sure, background element does not go beyond page bottom
    back_el.style.display = '';
    back_el.style.overflow = 'auto';
    back_el.style.height = '';
    var back_reg = YAHOO.util.Region.getRegion(back_el);
    var cl_reg = YAHOO.util.Dom.getClientRegion();
    if (back_reg.y + back_reg.height + 20 > cl_reg.top + cl_reg.height)
    {
      back_el.style.overflow = 'hidden';
      back_el.style.height = (cl_reg.top + cl_reg.height - back_reg.y - 20) + "px";
      back_reg = YAHOO.util.Region.getRegion(back_el);
    }

    if (this.current_td)
    {
      YAHOO.util.Dom.setX(tinyifr, back_reg.x -2);
      YAHOO.util.Dom.setY(tinyifr, back_reg.y -2);
      this.setEditFrameSize(back_reg.width-2,
        back_reg.height);
    }
    else
    {
      if (this.getInsertStatus())
      {
        YAHOO.util.Dom.setX(tinyifr, back_reg.x - 1);
        YAHOO.util.Dom.setY(tinyifr, back_reg.y);
        this.setEditFrameSize(back_reg.width + 1,
          back_reg.height);
      }
      else
      {
        YAHOO.util.Dom.setX(tinyifr, back_reg.x);
        YAHOO.util.Dom.setY(tinyifr, back_reg.y);
        this.setEditFrameSize(back_reg.width,
          back_reg.height);
      }
    }

    if (!this.current_td) {
      this.autoScroll();
    }

    // force redraw for webkit based browsers (ILIAS chrome bug #0010871)
    // http://stackoverflow.com/questions/3485365/how-can-i-force-webkit-to-redraw-repaint-to-propagate-style-changes
    // no feature detection here since we are fixing a webkit bug and IE does not like this patch (starts flickering
    // on "short" pages)
    let isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    let isSafari = /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor);
    if (isChrome || isSafari) {
      back_el.style.display='none';
      dummy = back_el.offsetHeight;
      back_el.style.display='';
    }
  }

  autoResize(ed) {
    this.copyInputToGhost(true);
    this.synchInputRegion();
  }

  // scrolls position of editor under editor menu
  autoScroll() {
    let tiny_reg, menu_reg, cl_reg, diff;

    //var tinyifr = document.getElementById("tinytarget_parent");
    let tinyifr = document.getElementById("tinytarget_ifr");
    let menu = document.getElementById('iltinymenu');
    let fc = document.getElementById('fixed_content');

    if (tinyifr && menu) {

      if ($(fc).css("position") === "static") {
        tiny_reg = YAHOO.util.Region.getRegion(tinyifr);
        menu_reg = YAHOO.util.Region.getRegion(menu);
        //console.log(tiny_reg);
        //console.log(menu_reg);
        cl_reg = YAHOO.util.Dom.getClientRegion();
        //console.log(cl_reg);
        //console.log(-20 + tiny_reg.y - (menu_reg.height + menu_reg.y - cl_reg.top));
        window.scrollTo(0, -20 + tiny_reg.y - (menu_reg.height + menu_reg.y - cl_reg.top));
      } else {
        diff = Math.floor($(menu).offset().top + $(menu).height()  + 20 - $(tinyifr).offset().top);
        if (diff > 1 || diff < -1) {
          $(fc).scrollTop($(fc).scrollTop() - diff);
        }
      }
    }
  }

  updateMenuButtons()
  {
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
    this.setParagraphClass(pc_model.characteristic);
    const ed = tinyMCE.get('tinytarget');
    ed.setContent(pc_model.text);
    this.splitBR();
    ed.setProgressState(0); // Show progress
    this.prepareTinyForEditing(false, switched);
    this.autoResize();
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
        //				hideToolbar();

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


  editParagraph(pcId, hierId, mode, switched)
  {
    let pdiv, pdiv_reg, ins_div, ta_div;

    let div_id = hierId + ":" + pcId;

    //	this.setEditStatus(true);
//    cmd_called = true;
    this.ed_para = div_id;
    this.pc_id_str = "";

    if (mode === 'edit')
    {
      // get paragraph edit div
      pdiv = document.getElementById("CONTENT" + div_id);
      pdiv_reg = YAHOO.util.Region.getRegion(pdiv);
    }

    if (mode === 'insert')
    {
      // get placeholder div
      pdiv = document.getElementById("TARGET" + div_id);
      let insert_ghost = new YAHOO.util.Element(document.createElement('div'));
      insert_ghost = YAHOO.util.Dom.insertAfter(insert_ghost, pdiv);
      insert_ghost.id = "insert_ghost";
      insert_ghost.style.paddingTop = "5px";
      insert_ghost.style.paddingBottom = "5px";

      pdiv_reg = YAHOO.util.Region.getRegion(pdiv);
    }

    // table editing mode (td)
    var moved = false;		// is edit area currently move from one td to another?
    if (mode === 'td')
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
    }


    // set background "ghost" element
    if (mode === 'td')
    {
      this.edit_ghost = "div_" + this.current_td;
      //this.edit_ghost = "td_" + this.current_td;
    }
    else if (mode === 'insert')
    {
      this.edit_ghost = "insert_ghost";
    }
    else
    {
      this.edit_ghost = "CONTENT" + this.ed_para;
    }


    if (switched)
    {
      let ta = document.getElementById('tinytarget');
      if (ta != null)
      {
        let ta_par = ta.parentNode;
        ta_par.removeChild(ta);
      }
    }

    // create new text area for tiny
    if (!moved)
    {
      //var pdiv_width = pdiv_reg.right - pdiv_reg.left;
      ta_div = new YAHOO.util.Element(document.createElement('div'));

      let ta = new YAHOO.util.Element(document.createElement('textarea'));
      ta = ta_div.appendChild(ta);
      ta.id = 'tinytarget';
      ta.className = 'par_textarea';
      ta.style.height = '1px';

      if (this.current_td !== "")
      {
        // this should be the table
        ins_div = pdiv.parentNode.parentNode.parentNode.parentNode;
      }
      else
      {
        ins_div = pdiv;
      }

      ta_div = YAHOO.util.Dom.insertAfter(ta_div, ins_div);
      ta_div.id = 'tinytarget_div';
      ta_div.style.position = 'absolute';
      ta_div.style.left = '-200px';
    }

    // init tiny
    let resize = false;
    let show_path = false;
    let statusbar = false;


    var tinytarget = document.getElementById("tinytarget");

    let par_ui = this;

    if (!moved)
    {
      tinyMCE.init({
        /* part of 4 */
        toolbar: false,
        menubar: false,
        statusbar: false,
        theme : "modern",
        language : "en",
        plugins : "save,paste",
        save_onsavecallback : "saveParagraph",
        mode : "exact",
        elements: "tinytarget",
        content_css: this.uiModel.config.content_css,
        fix_list_elements : true,
        valid_elements : "p,br[_moz_dirty],span[class],code,sub[class],sup[class],ul[class],ol[class],li[class]",
        forced_root_block : 'p',
        entity_encoding : "raw",
        paste_remove_styles: true,
        formats : this.text_formats,
        /* not found in 4 code or docu (the configs for p/br are defaults for 3, so this should be ok) */
        removeformat_selector : 'span,code',
        remove_linebreaks : true,
        convert_newlines_to_brs : false,
        force_p_newlines : true,
        force_br_newlines : false,
        /* not found in 3 docu (anymore?) */
        cleanup_on_startup : true,
        cleanup: true,
        paste_auto_cleanup_on_paste : true,
        branding: false,


        /**
         * Event is triggered after the paste plugin put the content
         * that should be pasted into a dom structure now
         * BUT the content is not put into the document yet
         *
         * still exists in 4
         */
        paste_preprocess: function (pl, o) {


          // see #23696, since tinymce4 it seems not possible to disable link conversion (even if <a> tags are not valid elements)
          // so we paste http string "on our own" and reset the paste content
          if (o.content.substring(0, 4) === "http") {
            par_ui.addBBCode(o.content, '', true);
            o.content = '';
          }

          if (o.wordContent)
          {
            o.content = o.content.replace(/(\r\n|\r|\n)/g, '\n');
            o.content = o.content.replace(/(\n)/g, ' ');
          }
          // remove any attributes from <p>
          o.content = o.content.replace(/(<p [^>]*>)/g, '<p>');

          // remove all divs
          o.content = o.content.replace(/(<div [^>]*>)/g, '');
          o.content = o.content.replace(/(<\/div>)/g, '');
        },

        /**
         * Event is triggered after the paste plugin put the content
         * that should be pasted into a dom structure now
         * BUT the content is not put into the document yet
         *
         * still exists in 4
         */
        paste_postprocess: function (pl, o) {
          var ed = ed = tinyMCE.activeEditor;

          if (o.wordContent)
          {

          }

          // we must handle all valid elements here
          // p (handled in paste_preprocess)
          // br[_moz_dirty] (investigate)
          // span[class] (todo)
          // code (should be ok, since no attributes allowed)
          // ul[class],ol[class],li[class] handled here

          // fix lists
          ed.dom.setAttrib(ed.dom.select('ol', o.node), 'class', 'ilc_list_o_NumberedList');
          ed.dom.setAttrib(ed.dom.select('ul', o.node), 'class', 'ilc_list_u_BulletedList');
          ed.dom.setAttrib(ed.dom.select('li', o.node), 'class', 'ilc_list_item_StandardListItem');

          // replace all b nodes by spans[Strong]
          tinymce.each(ed.dom.select('b', o.node), function(n) {
            ed.dom.replace(ed.dom.create('span', {'class': 'ilc_text_inline_Strong'}, n.innerHTML), n);
          });
          // replace all u nodes by spans[Important]
          tinymce.each(ed.dom.select('u', o.node), function(n) {
            ed.dom.replace(ed.dom.create('span', {'class': 'ilc_text_inline_Important'}, n.innerHTML), n);
          });
          // replace all i nodes by spans[Emph]
          tinymce.each(ed.dom.select('i', o.node), function(n) {
            ed.dom.replace(ed.dom.create('span', {'class': 'ilc_text_inline_Emph'}, n.innerHTML), n);
          });

          // remove all id attributes from the content
          tinyMCE.each(ed.dom.select('*[id!=""]', o.node), function(el) {
            el.id = '';
          });

          par_ui.pasting = true;
        },

        setup : function(ed) {

          ed.on('KeyUp', function(ev)
          {
            var ed = tinyMCE.get('tinytarget');
            //console.log("onKeyPress");
            par_ui.autoResize(ed);
          });
          ed.on('KeyDown', function(ev)
          {
            var ed = tinyMCE.get('tinytarget');

            if(ev.keyCode === 35 || ev.keyCode === 36)
            {
              var isMac = navigator.platform.toUpperCase().indexOf('MAC')>=0;
              if (!ev.shiftKey && isMac) {
                YAHOO.util.Event.preventDefault(ev);
                YAHOO.util.Event.stopPropagation(ev);
              }
            }

            if(ev.keyCode === 9 && !ev.shiftKey)
            {
              YAHOO.util.Event.preventDefault(ev);
              YAHOO.util.Event.stopPropagation(ev);
              if (par_ui.current_td !== "")
              {
                par_ui.editNextCell();
              }
              else
              {
                if (ed.queryCommandState('InsertUnorderedList') ||
                  ed.queryCommandState('InsertOrderedList'))
                {
                  par_ui.cmdListIndent();
                }
              }
            }
            if(ev.keyCode == 9 && ev.shiftKey)
            {
              //						console.log("backtab");
              YAHOO.util.Event.preventDefault(ev);
              YAHOO.util.Event.stopPropagation(ev);
              if (this.current_td != "")
              {
                par_ui.editPreviousCell();
              }
              else
              {
                if (ed.queryCommandState('InsertUnorderedList') ||
                  ed.queryCommandState('InsertOrderedList'))
                {
                  par_ui.cmdListOutdent();
                }
              }
            }
            //console.log("onKeyDown");
          });
          ed.on('NodeChange', function(cm, n)
          {
            var ed = tinyMCE.get('tinytarget');
            //console.log("onNodeChange");
            //console.log("----");
            //console.trace();

            // clean content after paste (has this really an effect?)
            // (yes, it does, at least splitSpans is important here #13019)
            if (par_ui.pasting) {
              par_ui.pasting = false;
              par_ui.splitDivs();
              par_ui.fixListClasses(false);
              par_ui.splitSpans();
            }

            // update state of indent/outdent buttons
            var ibut = document.getElementById('ilIndentBut');
            var obut = document.getElementById('ilOutdentBut');
            if (ibut != null && obut != null)
            {
              if (ed.queryCommandState('InsertUnorderedList') ||
                ed.queryCommandState('InsertOrderedList'))
              {
                ibut.style.visibility = '';
                obut.style.visibility = '';
              }
              else
              {
                ibut.style.visibility = 'hidden';
                obut.style.visibility = 'hidden';
              }
            }

            par_ui.updateMenuButtons();

          });

          var width = pdiv_reg.width;
          var height = pdiv_reg.height;
          if (width < par_ui.minwidth)
          {
            width = par_ui.minwidth;
          }
          if (height < par_ui.minheight)
          {
            height = par_ui.minheight;
          }

          //ed.onInit.add(function(ed, evt)
          ed.on('init', function(evt)
          {
            var ed = tinyMCE.get('tinytarget');

            // see https://www.tinymce.com/docs/api/tinymce/tinymce.shortcuts/
            // removing does not seem to work, also the functions do not
            // seem to be executed, but this way the shortcut is at least disabled
            // on chrome/mac, see also 0008662
            ed.shortcuts.add('meta+b', '', function() {par_ui.cmdSpan('Strong');});
            ed.shortcuts.add('meta+u', '', function() {par_ui.cmdSpan('Important');});
            ed.shortcuts.add('meta+i', '', function() {par_ui.cmdSpan('Emph');});

            par_ui.setEditFrameSize(width, height);
            if (mode === 'edit')
            {
              pdiv.style.display = "none";
            }

            if (mode === 'edit')
            {

              var tinytarget = document.getElementById("tinytarget_div");
              ta_div.style.position = '';
              ta_div.style.left = '';

              ed.setProgressState(1); // Show progress
              par_ui.loadCurrentParagraphIntoTiny(switched);
            }


            if (mode == 'insert')
            {
              ed.setContent("<p></p>");
              //				console.log(ed.getContent());
              var snode = ed.dom.getRoot();
              snode.className = 'ilc_text_block_Standard';
              par_ui.prepareTinyForEditing(true);
              par_ui.synchInputRegion();
              par_ui.focusTiny(true);
              //		setTimeout('this.focusTiny();', 1000);
//              cmd_called = false;
              //				console.log(ed.getContent());
            }

            if (mode == 'td')
            {
              //console.log("Setting content to: " + pdiv.innerHTML);
              ed.setContent(pdiv.innerHTML);
              par_ui.splitBR();
              par_ui.prepareTinyForEditing(false, false);
              par_ui.synchInputRegion();
              par_ui.focusTiny(true);
//              cmd_called = false;
            }

            $('#tinytarget_ifr').contents().find("html").attr('lang', $('html').attr('lang'));
            $('#tinytarget_ifr').contents().find("html").attr('dir', $('html').attr('dir'));
          });
        }

      });
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
  showToolbar(ed_id) {
    let obj;

    //#0017152
    $('#tinytarget_ifr').contents().find("html").attr('lang', $('html').attr('lang'));
    $('#tinytarget_ifr').contents().find("html").attr('dir', $('html').attr('dir'));

    $("#tinytarget_ifr").parent().css("border-width", "0px");
    $("#tinytarget_ifr").parent().parent().parent().css("border-width", "0px");

    // move parent node to end of body to ensure layer being on top
    if (!this.menu_panel) {
      obj = document.getElementById('iltinymenu');
      //$(obj).appendTo("body");
      $(obj).appendTo("#copg-editor-slate-content");
      $("#copg-editor-help").css("display", "none");

      obj = document.getElementById('ilEditorPanel');
      // if statement added since this may miss if internal links not supported?
      // e.g. table editing
      if (obj) {
        $(obj.parentNode).appendTo("body");
      }
    }

    $('#ilsaving').addClass("ilNoDisplay");

    // make tinymenu a panel
    obj = document.getElementById('iltinymenu');
    obj.style.display = "";
    this.menu_panel = true;

    var m_el = document.getElementById('iltinymenu');
    var m_reg = YAHOO.util.Region.getRegion(m_el);
  }

  hideToolbar () {
    obj = document.getElementById('iltinymenu');
    obj.style.display = "none";
    $("#copg-editor-help").css("display", "");
    $(".il_droparea").css('visibility', '');
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
