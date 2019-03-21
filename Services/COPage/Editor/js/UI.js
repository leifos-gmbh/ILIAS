"use strict";
exports.__esModule = true;
var uuid = require("uuid");
var UI = /** @class */ (function () {
    /**
     *
     * @param {JQueryStatic} jquery
     */
    function UI(jquery, pc_factory) {
        this.canvas_id = "#copg-editor-canvas";
        this.slate_id = "#copg-editor-slate";
        this.add_id = "copg-add";
        this.pc_components_path = "#copg-editor-canvas .il_editarea";
        this.jquery = jquery;
        this.pc_factory = pc_factory;
    }
    UI.prototype.replacePageCanvas = function (html) {
        this.jquery(this.canvas_id).html(html);
    };
    UI.prototype.replacePageSlate = function (html) {
        this.jquery(this.slate_id).html(html);
    };
    UI.prototype.setUIComponentModel = function (model) {
        this.ui_model = JSON.parse(model);
    };
    UI.prototype.setPageHtml = function (page_html) {
        this.page_html = page_html;
    };
    UI.prototype.setPageModel = function (page_model) {
        this.page_model = JSON.parse(page_model);
        console.log("setPageModel");
        console.log(this.page_model);
        this.page_model_map = this.page_model.reduce(function (map, obj) {
            map[obj.pc_id] = obj;
            return map;
        }, {});
        console.log(this.page_model_map);
    };
    UI.prototype.refreshPage = function () {
        this.replacePageCanvas(this.page_html);
        this.fixIds();
        this.addAddButton();
        this.addEvents();
    };
    UI.prototype.addAddButton = function () {
        this.jquery(this.canvas_id).append("<div id='" + this.add_id + "'>" + this.ui_model.dropdowns.add + "</div>");
    };
    UI.prototype.fixIds = function () {
        var ui = this;
        this.jquery(this.pc_components_path).each(function () {
            var id = this.id.split(":");
            ui.jquery(this).attr("id", id[1]);
        });
    };
    /**
     * Add events for page editing
     */
    UI.prototype.addEvents = function () {
        var $ = this.jquery;
        var ui = this;
        // components: click
        $(this.pc_components_path).on("click", function (event) {
            ui.handleComponentClick(event, this);
        });
        // add dropdown: click
        $("#" + this.add_id + " button").on("click", function (event) {
            ui.handleAddClick(event, this);
        });
    };
    /**
     * Add new page component
     * @param ui
     * @param event
     * @param el
     */
    UI.prototype.handleAddClick = function (event, el) {
        var action = $(el).data("action");
        var pctype;
        var new_pc_id;
        var new_pc = {};
        var ui = this;
        if (action) {
            pctype = action.substring(1);
            var pc = this.pc_factory.pageContent(pctype);
            new_pc = pc.getNew();
            new_pc_id = uuid.v1();
            this.updatePCModel(new_pc_id, new_pc.model);
            this.page_model_map[new_pc_id].pc_type = pctype; // @todo: clean this up
            this.jquery("#" + this.add_id).before("<div class='il_editarea' id='" + new_pc_id + "'>" + new_pc.html + "</div>");
            this.jquery("#" + new_pc_id).on("click", function (event) {
                ui.handleComponentClick(event, this);
            });
            this.jquery("#" + new_pc_id).trigger("click");
        }
    };
    /**
     * Handle click on pc component
     *
     * @param event
     * @param el
     */
    UI.prototype.handleComponentClick = function (event, el) {
        var pcid = this.extractPCIdFromPCComponentDomId(el.id);
        console.log(pcid);
        console.log(this.page_model_map[pcid]);
        this.loadEditFormForPageContent(el, pcid);
    };
    /**
     * Extract pc id from dom id
     *
     * @param {string} domid
     * @returns {string}
     */
    UI.prototype.extractPCIdFromPCComponentDomId = function (domid) {
        return domid;
        // not needed after fix ids
        //return (domid.split(":"))[1];
    };
    /**
     * Load edit form
     * @param el
     * @param pcid
     */
    UI.prototype.loadEditFormForPageContent = function (el, pcid) {
        var ui = this;
        var pctype = this.getPCType(pcid);
        var pc = this.pc_factory.pageContent(pctype);
        this.replacePageSlate(this.ui_model.forms[pctype].edit);
        pc.initEditForm(pcid, this.getPCModel(pcid), this.jquery(el), this.jquery(this.slate_id), function (model) {
            ui.updatePCModel(pcid, model);
        });
    };
    /**
     *
     * @param {string} pcid
     * @param model
     */
    UI.prototype.updatePCModel = function (pcid, model) {
        if (!this.page_model_map[pcid]) {
            this.page_model_map[pcid] = {};
        }
        this.page_model_map[pcid].pc_model = model;
    };
    /**
     * Get pc type for pc id
     *
     * @param {string} pcid
     * @returns {string}
     */
    UI.prototype.getPCType = function (pcid) {
        return this.page_model_map[pcid].pc_type;
    };
    /**
     * Get pc type for pc id
     *
     * @param {string} pcid
     * @returns {string}
     */
    UI.prototype.getPCModel = function (pcid) {
        return this.page_model_map[pcid].pc_model;
    };
    return UI;
}());
exports["default"] = UI;
//# sourceMappingURL=UI.js.map