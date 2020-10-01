/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Transformations in the tiny dom
 */
export default class TinyDomTransform {

  /**
   * @type {boolean}
   */
  debug = true;

  tiny;

  constructor(tiny) {
    this.tiny = tiny;
  }

  setAttribute(node, tag, attribute, value) {
    this.tiny.dom.setAttrib(this.tiny.dom.select(tag, node), attribute, value);
  }

  addListClasses(node) {
    this.setAttribute(node, 'ol', 'class', 'ilc_list_o_NumberedList');
    this.setAttribute(node, 'ul', 'class', 'ilc_list_u_BulletedList');
    this.setAttribute(node, 'li', 'class', 'ilc_list_item_StandardListItem');
  }

  replaceTag(node, tag, newTag, attributes) {
    const dom = this.tiny.dom;
    tinyMCE.each(dom.select(tag, node), function(n) {
      dom.replace(dom.create(newTag, attributes, n.innerHTML), n);
    });
  }

  replaceBoldUnderlineItalic(node) {
    this.replaceTag(node, 'b', 'span', {'class': 'ilc_text_inline_Strong'});
    this.replaceTag(node, 'u', 'span', {'class': 'ilc_text_inline_Important'});
    this.replaceTag(node, 'i', 'span', {'class': 'ilc_text_inline_Emph'});
  }

  removeIds(node) {
    const dom = this.tiny.dom;
    tinyMCE.each(dom.select('*[id!=""]', node), function(el) {
      el.id = '';
    });
  }

}