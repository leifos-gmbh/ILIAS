"use strict";
/// <reference path="../typings/JQueryStatic.d.ts" />
exports.__esModule = true;
var UI = /** @class */ (function () {
    /**
     *
     * @param {JQueryStatic} jquery
     */
    function UI(jquery) {
        this.canvas_id = "#copg-editor-canvas";
        this.slate_id = "#copg-editor-slate";
        this.jquery = jquery;
    }
    UI.prototype.replacePageCanvas = function (html) {
        this.jquery(this.canvas_id).html(html);
    };
    UI.prototype.replacePageSlate = function (html) {
        this.jquery(this.slate_id).html(html);
    };
    UI.prototype.setUIComponentModel = function (model) {
        this.ui_model = model;
    };
    UI.prototype.setPageHtml = function (page_html) {
        this.page_html = page_html;
    };
    UI.prototype.setPageModel = function (page_model) {
        this.page_model = page_model;
    };
    UI.prototype.refreshPage = function () {
        this.replacePageCanvas(this.page_html);
        this.addEvents();
    };
    UI.prototype.addEvents = function () {
        var $ = this.jquery;
        $(this.canvas_id + " ");
    };
    return UI;
}());
exports["default"] = UI;
//# sourceMappingURL=UI.js.map