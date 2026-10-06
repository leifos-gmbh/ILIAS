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
 */
!function(t){"use strict";class e{#t;#e;#n;#i;constructor(t){if(this.#e=t,this.#t=t.ownerDocument,this.#n=this.#e.querySelector(":scope > button"),null===this.#n)throw new Error("Dropdown: Expected exactly one button in dropdown element.",this.#e);if(this.#i=this.#e.querySelector(".dropdown-menu"),null===this.#i)throw new Error("Dropdown: Expected exactly a dropdown element.",this.#e);this.#n.addEventListener("click",this.#s)}#o=t=>{"Escape"===t.key&&this.hide()};#s=t=>{t.stopPropagation(),this.show()};#d=()=>{this.hide()};#l=t=>{this.#e.contains(t.relatedTarget)||this.hide()};#h=()=>{let t=this.#e.parentElement;for(;null!==t;){const e=this.#t.defaultView.getComputedStyle(t);if("hidden"===e.overflowX||"hidden"===e.overflowY)return t;t=t.parentElement}return null};#r=()=>{this.#i.classList.remove("dropdown-menu__left"),this.#i.classList.add("dropdown-menu__right");const t=this.#h(),e=t?.getBoundingClientRect(),n=this.#i.getBoundingClientRect(),i=e?.right??this.#t.documentElement.clientWidth;n.right>i&&(this.#i.classList.remove("dropdown-menu__right"),this.#i.classList.add("dropdown-menu__left"))};show(){il.UI.dropdown.opened?.hide(),il.UI.dropdown.opened=this,this.#i.style.display="block",this.#r(),this.#n.setAttribute("aria-expanded","true"),this.#t.addEventListener("keydown",this.#o),this.#t.addEventListener("click",this.#d),this.#e.addEventListener("focusout",this.#l),this.#n.removeEventListener("click",this.#s)}hide(){this.#i.style.display="none",this.#n.setAttribute("aria-expanded","false"),this.#t.removeEventListener("keydown",this.#o),this.#t.removeEventListener("click",this.#d),this.#e.removeEventListener("focusout",this.#l),this.#n.addEventListener("click",this.#s)}}t.UI=t.UI||{},t.UI.dropdown={},t.UI.dropdown.opened=null,t.UI.dropdown.init=function(t){return new e(t)}}(il);
