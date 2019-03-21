/// <reference path="../typings/JQueryStatic.d.ts" />
import PageContentFactory from "./PageContent/PageContentFactory";
import PageContentInterface from "./PageContent/PageContentInterface";
import * as uuid from 'uuid';

declare var $: JQueryStatic;

export default class UI {

    pc_factory: PageContentFactory;

    canvas_id: string = "#copg-editor-canvas";
    slate_id: string = "#copg-editor-slate";
    add_id: string = "copg-add";
    pc_components_path: string = "#copg-editor-canvas .il_editarea";
    jquery: JQueryStatic;
    ui_model: any;
    page_html: string;
    page_model: Array<any>;
    page_model_map: Object;

    /**
     *
     * @param {JQueryStatic} jquery
     */
    constructor(jquery: JQueryStatic, pc_factory: PageContentFactory) {
        this.jquery = jquery;
        this.pc_factory = pc_factory;
    }

    replacePageCanvas(html: string) {
        this.jquery(this.canvas_id).html(html);
    }

    replacePageSlate(html: string) {
        this.jquery(this.slate_id).html(html);
    }

    setUIComponentModel(model: string) {
        this.ui_model = JSON.parse(model);
    }

    setPageHtml(page_html: string) {
        this.page_html = page_html;
    }

    setPageModel(page_model: string) {
        this.page_model = JSON.parse(page_model);
        console.log("setPageModel");
        console.log(this.page_model);
        this.page_model_map = this.page_model.reduce(function(map, obj) {
            map[obj.pc_id] = obj;
            return map;
        }, {});
        console.log(this.page_model_map);
    }

    refreshPage(): void {
        this.replacePageCanvas(this.page_html);
        this.fixIds();
        this.addAddButton();
        this.addEvents();
    }

    addAddButton(): void {
        this.jquery(this.canvas_id).append("<div id='" + this.add_id + "'>" + this.ui_model.dropdowns.add + "</div>");
    }

    fixIds() {
        let ui = this;
        this.jquery(this.pc_components_path).each(function() {
            let id = this.id.split(":");
            ui.jquery(this).attr("id", id[1]);
        });
    }

    /**
     * Add events for page editing
     */
    addEvents(): void {
        let $ = this.jquery;
        let ui = this;

        // components: click
        $(this.pc_components_path).on("click", function(event) {
            ui.handleComponentClick(event, this);
        });

        // add dropdown: click
        $("#" + this.add_id + " button").on("click", function(event) {
            ui.handleAddClick(event, this);
        });
    }

    /**
     * Add new page component
     * @param ui
     * @param event
     * @param el
     */
    handleAddClick(event, el): void {
        let action = $(el).data("action");
        let pctype: string;
        let new_pc_id: string;
        let new_pc: any = {};
        let ui = this;

        if (action) {
            pctype = action.substring(1);
            let pc: PageContentInterface = this.pc_factory.pageContent(pctype);
            new_pc = pc.getNew();
            new_pc_id = uuid.v1();
            this.updatePCModel(new_pc_id, new_pc.model);
            this.page_model_map[new_pc_id].pc_type = pctype;    // @todo: clean this up
            this.jquery("#" + this.add_id).before("<div class='il_editarea' id='" + new_pc_id + "'>" + new_pc.html + "</div>");
            this.jquery("#" + new_pc_id).on("click", function(event) {
                ui.handleComponentClick(event, this);
            });
            this.jquery("#" + new_pc_id).trigger("click");
        }
    }

    /**
     * Handle click on pc component
     *
     * @param event
     * @param el
     */
    handleComponentClick(event, el) {
        let pcid = this.extractPCIdFromPCComponentDomId(el.id);
        console.log(pcid);
        console.log(this.page_model_map[pcid]);
        this.loadEditFormForPageContent(el, pcid);
    }

    /**
     * Extract pc id from dom id
     *
     * @param {string} domid
     * @returns {string}
     */
    extractPCIdFromPCComponentDomId(domid: string): string {
        return domid;
        // not needed after fix ids
        //return (domid.split(":"))[1];
    }

    /**
     * Load edit form
     * @param el
     * @param pcid
     */
    loadEditFormForPageContent(el, pcid) {
        let ui = this;
        let pctype: string = this.getPCType(pcid);
        let pc: PageContentInterface = this.pc_factory.pageContent(pctype);

        this.replacePageSlate(this.ui_model.forms[pctype].edit);

        pc.initEditForm(pcid,
            this.getPCModel(pcid),
            this.jquery(el),
            this.jquery(this.slate_id),
            function (model) {
                ui.updatePCModel(pcid, model);
            }
        );
    }

    /**
     *
     * @param {string} pcid
     * @param model
     */
    updatePCModel(pcid: string, model): void {
        if (!this.page_model_map[pcid]) {
            this.page_model_map[pcid] = {};
        }
        this.page_model_map[pcid].pc_model = model;
    }

    /**
     * Get pc type for pc id
     *
     * @param {string} pcid
     * @returns {string}
     */
    getPCType(pcid: string): string {
        return this.page_model_map[pcid].pc_type;
    }

    /**
     * Get pc type for pc id
     *
     * @param {string} pcid
     * @returns {string}
     */
    getPCModel(pcid: string): string {
        return this.page_model_map[pcid].pc_model;
    }
}