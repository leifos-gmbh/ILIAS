/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wraps tiny
 */
export default class TinyWrapper {

  /**
   * @type {Object}
   */
  lib;

  /**
   * @type {string}
   */
  id = "tinytarget";

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
   * @param {string} content_css
   */
  constructor(content_css) {
    this.lib = tinyMCE;
    this.content_css;
  }

  init() {
    this
  }

  getConfig() {
    return {
      /* part of 4 */
      toolbar: false,
      menubar: false,
      statusbar: false,
      theme : "modern",
      language : "en",
      plugins : "save,paste",
      save_onsavecallback : "saveParagraph",
      mode : "exact",
      elements: this.id,
      content_css: this.content_css,
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
      paste_preprocess: (pl, o) => {
        this.pastePreProcess(pl, o);
      },
      paste_postprocess: (pl, o) => {
        this.pastePostProcess(pl, o);
      },
      setup: (tiny) => {
        this.setup(tiny);
      },

    };
  }

  addTextFormat(f) {
    this.text_formats[f] = { inline: 'span', classes: 'ilc_text_inline_' + f };
  }

  pastePreProcess(pl, o) {
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
  }

  pastePostProcess(pl, o) {
    const tiny = this.tiny;

    // we must handle all valid elements here
    // p (handled in paste_preprocess)
    // br[_moz_dirty] (investigate)
    // span[class] (todo)
    // code (should be ok, since no attributes allowed)
    // ul[class],ol[class],li[class] handled here

    // fix lists
    tiny.dom.setAttrib(tiny.dom.select('ol', o.node), 'class', 'ilc_list_o_NumberedList');
    tiny.dom.setAttrib(tiny.dom.select('ul', o.node), 'class', 'ilc_list_u_BulletedList');
    tiny.dom.setAttrib(tiny.dom.select('li', o.node), 'class', 'ilc_list_item_StandardListItem');

    // replace all b nodes by spans[Strong]
    tinymce.each(tiny.dom.select('b', o.node), function(n) {
      tiny.dom.replace(tiny.dom.create('span', {'class': 'ilc_text_inline_Strong'}, n.innerHTML), n);
    });
    // replace all u nodes by spans[Important]
    tinymce.each(tiny.dom.select('u', o.node), function(n) {
      tiny.dom.replace(tiny.dom.create('span', {'class': 'ilc_text_inline_Important'}, n.innerHTML), n);
    });
    // replace all i nodes by spans[Emph]
    tinymce.each(tiny.dom.select('i', o.node), function(n) {
      tiny.dom.replace(tiny.dom.create('span', {'class': 'ilc_text_inline_Emph'}, n.innerHTML), n);
    });

    // remove all id attributes from the content
    tinyMCE.each(tiny.dom.select('*[id!=""]', o.node), function(el) {
      el.id = '';
    });

    this.pasting = true;
  }

  setup(tiny) {
    this.tiny = tiny;
    // if this does not work this.tiny = this.lib.get(this.id); ??

    tiny.on('KeyUp', function(ev) {
      this.autoResize(tiny);                                            // MISSING
    });

    tiny.on('KeyDown', function(ev)
    {
      if(ev.keyCode === 35 || ev.keyCode === 36)
      {
        const isMac = navigator.platform.toUpperCase().indexOf('MAC')>=0;
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
          //par_ui.editNextCell();                              // MISSING
        }
        else
        {
          if (tiny.queryCommandState('InsertUnorderedList') ||
            tiny.queryCommandState('InsertOrderedList'))
          {
            //par_ui.cmdListIndent();                              // MISSING
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
          //par_ui.editPreviousCell();                              // MISSING
        }
        else
        {
          if (ed.queryCommandState('InsertUnorderedList') ||
            ed.queryCommandState('InsertOrderedList'))
          {
            //par_ui.cmdListOutdent();                              // MISSING
          }
        }
      }
    });


    tiny.on('NodeChange', function(cm, n) {

        // clean content after paste (has this really an effect?)
        // (yes, it does, at least splitSpans is important here #13019)
        if (this.pasting) {
          this.pasting = false;
          this.splitDivs();                                         // MISSING
          this.fixListClasses(false);                               // MISSING
          this.splitSpans();                                    // MISSING
        }

        // update state of indent/outdent buttons
        const ibut = document.getElementById('ilIndentBut');
        const obut = document.getElementById('ilOutdentBut');
        if (ibut != null && obut != null)
        {
          if (tiny.queryCommandState('InsertUnorderedList') ||
            tiny.queryCommandState('InsertOrderedList'))
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

        this.updateMenuButtons();                                 // MISSING

      });

      let width = pdiv_reg.width;
      let height = pdiv_reg.height;
      if (width < par_ui.minwidth) {
        width = par_ui.minwidth;
      }
      if (height < par_ui.minheight) {
        height = par_ui.minheight;
    }

    //ed.onInit.add(function(ed, evt)
    tiny.on('init', function(evt)
    {
      // see https://www.tinymce.com/docs/api/tinymce/tinymce.shortcuts/
      // removing does not seem to work, also the functions do not
      // seem to be executed, but this way the shortcut is at least disabled
      // on chrome/mac, see also 0008662
//        tiny.shortcuts.add('meta+b', '', function() {par_ui.cmdSpan('Strong');});       // MISSING (per setter von aussen!)
//        tiny.shortcuts.add('meta+u', '', function() {par_ui.cmdSpan('Important');});
//        tiny.shortcuts.add('meta+i', '', function() {par_ui.cmdSpan('Emph');});

      this.setEditFrameSize(width, height);           // MISSING
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
//          par_ui.loadCurrentParagraphIntoTiny(switched);                        // MISSING
      }


      if (mode == 'insert')
      {
        this.setContent("<p></p>");
        //				console.log(ed.getContent());
        var snode = ed.dom.getRoot();
        snode.className = 'ilc_text_block_Standard';
        this.prepareTinyForEditing(true);
        this.synchInputRegion();
        this.focusTiny(true);
        //		setTimeout('this.focusTiny();', 1000);
        //              cmd_called = false;
        //				console.log(ed.getContent());
      }

      if (mode == 'td')
      {
        //console.log("Setting content to: " + pdiv.innerHTML);
        ed.setContent(pdiv.innerHTML);
        this.splitBR();
        this.prepareTinyForEditing(false, false);
        this.synchInputRegion();
        this.focusTiny(true);
        //              cmd_called = false;
      }

      $('#tinytarget_ifr').contents().find("html").attr('lang', $('html').attr('lang'));
      $('#tinytarget_ifr').contents().find("html").attr('dir', $('html').attr('dir'));
    });
  }
}