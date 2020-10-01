/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * HTML transformations
 */
export default class HTMLTransform {

  /**
   * @type {boolean}
   */
  debug = true;

  constructor() {

  }

  /**
   * @param {string} str
   * @return {string}
   */
  removeLineFeeds(str) {
    str = str.replace(/(\r\n|\r|\n)/g, '\n');
    str = str.replace(/(\n)/g, ' ');
    return str;
  }

  /**
   * @param {string} tag
   * @param {string} str
   * @return {string}
   */
  removeAttributesFromTag(tag, str) {
    const re = new RegExp("(<" + tag + " [^>]*>)","g");
    return str.replace(re, '<' + tag + '>');
  }

  /**
   * @param {string} tag
   * @param {string} str
   * @return {string}
   */
  removeTag(tag, str) {
    const re1 = new RegExp("(<" + tag + " [^>]*>)","g");
    const re2 = new RegExp("(<\/" + tag + ">)","g");
    str = str.replace(re1, '');
    str = str.replace(re2, '');
    return str;
  }


}