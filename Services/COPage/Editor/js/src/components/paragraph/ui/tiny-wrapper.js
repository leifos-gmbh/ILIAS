/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import HTMLTransform from "./html-transform.js";
import TinyDomTransform from "./tiny-dom-transform.js";

/**
 * Wraps tiny
 */
export default class TinyWrapper {

  /**
   * @type {boolean}
   */
  debug = true;

  /**
   * @type {Object}
   */
  lib;

  /**
   * @type {string}
   */
  id = "tinytarget";

  /**
   * @type {number}
   */
  minwidth = 50;

  /**
   * @type {number}
   */
  minheight = 20;

  /**
   * @type {Object}
   */
  config = null;

  /**
   * @type {string}
   */
  content_css;

  current_td = "";                                      // MISSING

  /**
   * @type {HTMLTransform}
   */
  htmlTransform;

  /**
   * @type {Object}
   */
  text_formats = {
    Strong: { inline: 'span', classes: 'ilc_text_inline_Strong' },
    Emph: { inline: 'span', classes: 'ilc_text_inline_Emph' },
    Important: { inline: 'span', classes: 'ilc_text_inline_Important' },
    Comment: { inline: 'span', classes: 'ilc_text_inline_Comment' },
    Quotation: { inline: 'span', classes: 'ilc_text_inline_Quotation' },
    Accent: { inline: 'span', classes: 'ilc_text_inline_Accent' },
    Sup: { inline: 'sup', classes: 'ilc_sup_Sup' },
    Sub: { inline: 'sub', classes: 'ilc_sub_Sub' }
  };


  /**
   * @param {string} content_css
   */
  constructor(content_css) {
    this.lib = tinyMCE;
    this.htmlTransform = new HTMLTransform();
  }

  setContentCss(content_css) {
    this.content_css = content_css;
  }

  /**
   * @param message
   */
  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }


  getConfig(after_init, after_keyup) {
    if (!this.config) {
      this.config = {
        /* part of 4 */
        toolbar: false,
        menubar: false,
        statusbar: false,
        language: "en",
        height: "100%",
        plugins: "save,paste,lists",
        save_onsavecallback: "saveParagraph",
        mode: "exact",
        elements: this.id,
        content_css: this.content_css,
        fix_list_elements: true,
        valid_elements: "p,br[_moz_dirty],span[class],code,sub[class],sup[class],ul[class],ol[class],li[class]",
        forced_root_block: 'p',
        entity_encoding: "raw",
        paste_remove_styles: true,
        formats: this.text_formats,
        /* not found in 4 code or docu (the configs for p/br are defaults for 3, so this should be ok) */
        removeformat_selector: 'span,code',
        remove_linebreaks: true,
        convert_newlines_to_brs: false,
        force_p_newlines: true,
        force_br_newlines: false,
        /* not found in 3 docu (anymore?) */
        cleanup_on_startup: true,
        cleanup: true,
        paste_auto_cleanup_on_paste: true,
        branding: false,
        paste_preprocess: (pl, o) => {
          this.pastePreProcess(pl, o);
        },
        paste_postprocess: (pl, o) => {
          this.pastePostProcess(pl, o);
        },
        setup: (tiny) => {
          this.setup(tiny, after_init, after_keyup);
        },
      };
    }
    return this.config;
  }

  addTextFormat(f) {
    this.text_formats[f] = { inline: 'span', classes: 'ilc_text_inline_' + f };
  }

  pastePreProcess(pl, o) {
    const html = this.htmlTransform;

    // see #23696, since tinymce4 it seems not possible to disable link conversion (even if <a> tags are not valid elements)
    // so we paste http string "on our own" and reset the paste content
    if (o.content.substring(0, 4) === "http") {
      par_ui.addBBCode(o.content, '', true);
      o.content = '';
    }

    if (o.wordContent) {
      o.content = html.removeLineFeeds(o.content);
    }
    o.content = html.removeAttributesFromTag("p", o.content);
    o.content = html.removeTag("div", o.content);
  }

  getTinyDomTransform() {
    return new TinyDomTransform(this.tiny);
  }

  pastePostProcess(pl, o) {
    const tiny = this.tiny;

    const tinyDom = this.getTinyDomTransform();

    // we must handle all valid elements here
    // p (handled in paste_preprocess)
    // br[_moz_dirty] (investigate)
    // span[class] (todo)
    // code (should be ok, since no attributes allowed)
    // ul[class],ol[class],li[class] handled here

    // add standard ilias list classes
    tinyDom.addListClasses(o.node);

    // replace all b, u, i tags by ilias spans
    tinyDom.replaceBoldUnderlineItalic(o.node);

    // remove all id attributes from the content
    tinyDom.removeIds(o.node);

    this.pasting = true;
  }

  setup(tiny, after_init, after_keyup) {
    this.log("tiny-wrapper.init.setup");
    this.tiny = tiny;
    const wrapper = this;

    // if this does not work this.tiny = this.lib.get(this.id); ??


    tiny.on('KeyUp', function (ev) {
      wrapper.autoResize();
      after_keyup();
    });

    tiny.on('KeyDown', function (ev) {
      if (ev.keyCode === 35 || ev.keyCode === 36) {
        const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
        if (!ev.shiftKey && isMac) {
          ev.preventDefault();
          ev.stopPropagation();
        }
      }

      if (ev.keyCode === 9 && !ev.shiftKey) {
        ev.preventDefault();
        ev.stopPropagation();
        /*        if (par_ui.current_td !== "")
                {
                  //par_ui.editNextCell();                              // MISSING
                }*/
      }

      if (ev.keyCode === 9 && ev.shiftKey) {
        ev.preventDefault();
        ev.stopPropagation();
        /*if (wrapper.current_td != "")
        {
          //par_ui.editPreviousCell();                          // MISSING
        }*/
      }
    });


    tiny.on('NodeChange', function (cm, n) {

      // clean content after paste (has this really an effect?)
      // (yes, it does, at least splitSpans is important here #13019)
      if (wrapper.pasting) {
        wrapper.pasting = false;
        wrapper.splitDivs();
        wrapper.fixListClasses(false);
        wrapper.splitSpans();
      }

      // update state of indent/outdent buttons
      const ibut = document.querySelector("[data-copg-ed-action='list.indent']");
      const obut = document.querySelector("[data-copg-ed-action='list.outdent']");
      if (ibut != null && obut != null) {
        if (tiny.queryCommandState('InsertUnorderedList') ||
          tiny.queryCommandState('InsertOrderedList')) {
          ibut.disabled = false;
          obut.disabled = false;
        } else {
          ibut.disabled = true;
          obut.disabled = true;
        }
      }
    });

    let width = wrapper.ghost_reg.width;
    let height = wrapper.ghost_reg.height;
    if (width < wrapper.minwidth) {
      width = wrapper.minwidth;
    }
    if (height < wrapper.minheight) {
      height = wrapper.minheight;
    }

    //ed.onInit.add(function(ed, evt)
    tiny.on('init', function (evt) {

      let ed = tiny;
      let mode = "insert";                                      // MISSING

      ed.formatter.register('mycode', {
        inline: 'code'
      });

      wrapper.log("tiny-wrapper.init.tiny-init");

      wrapper.setEditFrameSize(width, height);           // MISSING
      if (mode === 'edit') {
        pdiv.style.display = "none";

        var tinytarget = document.getElementById("tinytarget_div");
        ta_div.style.position = '';
        ta_div.style.left = '';

        ed.setProgressState(1); // Show progress
        //          par_ui.loadCurrentParagraphIntoTiny(switched);                        // MISSING
      }


      if (mode === 'insert') {
        wrapper.initContent("<p></p>", 'ilc_text_block_Standard');
      }
      /*
      if (mode == 'td')
      {
        //console.log("Setting content to: " + pdiv.innerHTML);           // MISSING
        ed.setContent(pdiv.innerHTML);
        this.splitBR();
        this.prepareTinyForEditing(false, false);
        this.synchInputRegion();
        this.focusTiny(true);
        //              cmd_called = false;
      }*/

      $('#tinytarget_ifr').contents().find("html").attr('lang', $('html').attr('lang'));
      $('#tinytarget_ifr').contents().find("html").attr('dir', $('html').attr('dir'));
      $('#tinytarget_ifr').contents().find("html").css("overflow", "auto");

      if (after_init) {
        after_init();
      }
    });
  }

  initContent(content, characteristic) {
    this.log("tiny-wrapper.initContent");
    this.setContent(content);
    let ed = this.tiny;
    this.setParagraphClass(characteristic);
    this.synchInputRegion();
    this.focusTiny(true);
  }

  initEdit(content_element, text, characteristic, after_init, after_keyup) {
    this.log('tiny-wrapper.initEdit');

    this.setGhostAt(content_element);

    if (!this.tiny) {
      this.createTextAreaForTiny();
      this.lib.init(this.getConfig(() => {
              this.initContent(text, characteristic);
              after_init();
      }, after_keyup));
    } else {
      this.showAfter(content_element);
      this.initContent(text, characteristic);
      after_init();
    }
  }

  initInsert(content_element, after_init, after_keyup) {
    this.log('tiny-wrapper.initInsert');

    this.setGhostAt(content_element);
    if (!this.tiny) {
      this.createTextAreaForTiny();
      this.lib.init(this.getConfig(after_init, after_keyup));
    } else {
      this.showAfter(content_element);
      this.initContent("<p></p>", 'ilc_text_block_Standard');
      after_init();
    }
  }

  hide() {
    const tdiv = document.getElementById("tinytarget_div");
    tdiv.style.display = "none";
  }

  showAfter(content_element) {
    const tdiv = document.getElementById("tinytarget_div");
    tdiv.style.display = "";
  }

  /**
   * Note: we always add tiny at the end of the document, since it does not
   * like to be moved around, see
   * https://stackoverflow.com/questions/2535569/tinymce-editor-dislikes-being-moved-around
   */
  createTextAreaForTiny() {
    this.log("tiny-wrapper.createTextAraForTiny");

    let ta = document.createElement("textarea");
    let ta_div = document.createElement("div");

    const parent = document.getElementById("ilContentContainer");

    parent.appendChild(ta_div);
    ta_div.appendChild(ta);

    ta_div.id = 'tinytarget_div';
    ta.id = "tinytarget";
    ta.className = 'par_textarea';
  }


  setGhostAt(content_element) {
    this.log("tiny-wrapper.setGhostAt " + content_element);
    // get paragraph edit div
    this.ghost = content_element;
    this.ghost.classList.add("copg-ghost-wrapper");

    this.ghost_reg = YAHOO.util.Region.getRegion(this.ghost);
  }

  // copy input of tiny to ghost div in background
  copyInputToGhost(add_final_spacer) {
    this.log('tiny-wrapper.copyInputToGhost');
    let tag;
    let ed = this.tiny;

    if (this.ghost) {
      let cl = ed.dom.getRoot().className;
      let c = this.p2br(ed.getContent());
      if (this.current_td === "") {
        cl = "copg-input-ghost " + cl;
        console.log(cl);
        const cl_arr = cl.split("_");
        const characteristic = cl_arr[cl_arr.length - 1];
        switch (characteristic) {
          case "Headline1":
            tag = "h1";
            break;
          case "Headline2":
            tag = "h2";
            break;
          case "Headline3":
            tag = "h3";
            break;
          default:
            tag = "div";
            break;
        }

        if (add_final_spacer) {
          c = c + "<br />.";
        }

        c = "<div class='ilEditLabel'>" + il.Language.txt("cont_ed_par") +
          " (" + characteristic + ")</div><" + tag + " style='position:static;' class='" + cl + "'>" + c + "</" + tag + ">";
      } else {
        this.tds[this.current_td] =
          this.getContentForSaving();
      }

      // we remove the first child div content div (edit label)
      this.ghost.querySelector("div").remove();
      this.ghost.querySelector("div, h1, h2, h3").remove();

      console.log("replacing with: " + c);

      // we replace the second div (content) with c
      this.ghost.innerHTML = c;

    }
  }

  stopEditing() {
    this.copyInputToGhost(false);
    this.clearGhost();
    this.hide();
  }

  clearGhost() {
    this.log('tiny-wrapper.clearGhost');

    if (this.ghost) {
      this.ghost.classList.remove("copg-ghost-wrapper");
      this.ghost.style.overflow = "";
      this.ghost.style.height = "";
      const content = this.ghost.querySelector(".copg-input-ghost");
      content.classList.remove("copg-input-ghost");
      this.ghost = null;
    }
  }

  // synchs the size/position of the tiny to the space the ghost
  // object uses in the background
  synchInputRegion() {
    this.log('tiny-wrapper.synchInputRegion');

    let back_el, dummy;

    back_el = this.ghost;

    if (this.current_td) {              // MISSING
      back_el = back_el.parentNode;
    }

    if (!back_el) {
      return;
    }

    back_el.style.paddingLeft = "";
    back_el.style.paddingRight = "";

    let tdiv = document.getElementById("tinytarget_div");

    // make sure, background element does not go beyond page bottom
    back_el.style.display = '';
    back_el.style.overflow = 'auto';
    back_el.style.height = '';
    var back_reg = YAHOO.util.Region.getRegion(back_el);

    this.log("Ghost region: ");
    this.log(back_reg);

    var cl_reg = YAHOO.util.Dom.getClientRegion();
    if (back_reg.y + back_reg.height + 20 > cl_reg.top + cl_reg.height) {
      back_el.style.overflow = 'hidden';
      back_el.style.height = (cl_reg.top + cl_reg.height - back_reg.y - 20) + "px";
      back_reg = YAHOO.util.Region.getRegion(back_el);
    }

    YAHOO.util.Dom.setX(tdiv, back_reg.x);
    YAHOO.util.Dom.setY(tdiv, back_reg.y);
    this.setEditFrameSize(back_reg.width,
      back_reg.height);

    if (!this.current_td) {
      this.autoScroll();
    }

    // force redraw for webkit based browsers (ILIAS chrome bug #0010871)
    // http://stackoverflow.com/questions/3485365/how-can-i-force-webkit-to-redraw-repaint-to-propagate-style-changes
    // no feature detection here since we are fixing a webkit bug and IE does not like this patch (starts flickering
    // on "short" pages)
    /*
    let isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    let isSafari = /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor);
    if (isChrome || isSafari) {
      back_el.style.display='none';
      dummy = back_el.offsetHeight;
      back_el.style.display='';
    }*/
  }

  autoResize() {
    this.log('tiny-wrapper.autoResize');
    this.copyInputToGhost(true);
    this.synchInputRegion();
  }

  // scrolls position of editor under editor menu
  autoScroll() {
    this.log('tiny-wrapper.autoScroll (deactivated)');
    return;                                               // MISSING

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

  removeTiny() {
    this.log('tiny-wrapper.removeTiny');
    tinyMCE.execCommand('mceRemoveEditor', false, 'tinytarget');
    let tt = document.getElementById("tinytarget");
    tt.style.display = 'none';
  }

  // set frame size of editor
  setEditFrameSize(width, height) {
    this.log('tiny-wrapper.setEditFrameSize');
    let tinyifr = document.getElementById("tinytarget_ifr");
    let tinytd = document.getElementById("tinytarget_tbl");
    tinyifr.style.width = width + "px";
    tinyifr.style.height = height + "px";

    $("#tinytarget_ifr").css("width", width + "px");
    $("#tinytarget_ifr").css("height", height + "px");
    $("#tinytarget_div").css("width", width + "px");
    $("#tinytarget_div").css("height", height + "px");

    this.ed_width = width;
    this.ed_height = height;
  }

  focusTiny(delayed) {
    this.log('tiny-wrapper.focusTiny');
    let timeout = 1;
    if (delayed) {
      timeout = 500;
    }

    setTimeout(function () {
      let ed = tinyMCE.get('tinytarget');
      if (ed) {
        let e = tinyMCE.DOM.get(ed.id + '_external');
        let r = ed.dom.getRoot();
        let fc = r.childNodes[0];
        if (r.className != null) {
          var st = r.className.substring(15);
        }

        ed.getWin().focus();
      }
    }, timeout);
  }


  setContent (text, characteristic) {
    const switched = false;                                   // MISSING
    const ed = this.tiny;
    ed.setContent(text);
    this.splitBR();
    this.autoResize();
    this.setParagraphClass(characteristic);
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

  /**
   * convert <p> tags to <br />
   * @param {string} c
   * @return {string}
   */
  p2br(c) {
    // remove <p> and \n
    c = c.split("<p>").join("");
    c = c.split("\n").join("");

    // convert </p> to <br />
    c = c.split("</p>").join("<br />");

    // remove trailing <br />
    if (c.substr(c.length - 6) === "<br />") {
      c = c.substr(0, c.length - 6);
    }

    return c;
  }

  setParagraphClass(i) {
    let ed = tinyMCE.activeEditor;
    ed.focus();
    let snode = ed.dom.getRoot();

    //snode = snode.querySelector("p");

    if (snode) {
      //snode.className = "ilc_text_block_" + i['hid_val'];
      snode.className = "ilc_text_block_" + i;
      snode.style.position = 'static';
    }
    snode.parentNode.className = "il-no-tiny-bg";

    this.autoResize();
  }

  toggleFormat(t) {
    let ed = this.tiny;
    if (t === "Code") {
      t = "mycode";
    }
    ed.execCommand('mceToggleFormat', false, t);
    ed.focus();
    ed.selection.collapse(false);
    this.autoResize();
  }

  removeFormat() {
    let ed = this.tiny;
    ed.focus();
    ed.execCommand('RemoveFormat', false);
    this.autoResize(ed);
  }

  getText() {
      let ed = this.tiny;
      let c = ed.getContent();
      c = this.p2br(c);
      return c;
  }

  getCharacteristic() {
      let ed = this.tiny;
      let parts = ed.dom.getRoot().className.split("_");
      //console.log("---");
      return parts[parts.length - 1];
  }

  bulletList() {
    let ed = this.tiny;
    ed.focus();
    ed.execCommand('InsertUnorderedList', false);
    this.fixListClasses(true);
    this.autoResize(ed);
  }

  numberedList() {
    let ed = this.tiny;
    ed.focus();
    ed.execCommand('InsertOrderedList', false);
    this.fixListClasses(true);
    this.autoResize(ed);
  }

  listIndent() {
    let blockq = false, range, ed = this.tiny;

    this.log("listIndent");

    ed.focus();
    ed.execCommand('Indent');
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

  listOutdent() {
    this.log("listOutdent");
    let ed = this.tiny;
    ed.focus();
    ed.execCommand('Outdent', false);
    this.fixListClasses(true);
    this.autoResize(ed);
  }
}