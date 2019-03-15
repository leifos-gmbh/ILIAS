/// <reference path="../typings/JQueryStatic.d.ts" />

declare var $: JQueryStatic;

export default class UI {
    canvas_id: string = "#copg-editor-canvas";
    slate_id: string = "#copg-editor-slate";
    jquery: JQueryStatic;
    ui_model: Object;
    page_html: string;
    page_model: Object;

    /**
     *
     * @param {JQueryStatic} jquery
     */
    constructor(jquery: JQueryStatic) {
        this.jquery = jquery;
    }

    replacePageCanvas(html: string) {
        this.jquery(this.canvas_id).html(html);
    }

    replacePageSlate(html: string) {
        this.jquery(this.slate_id).html(html);
    }

    setUIComponentModel(model: Object) {
        this.ui_model = model;
    }

    setPageHtml(page_html: string) {
        this.page_html = page_html;
    }

    setPageModel(page_model: Object) {
        this.page_model = page_model;
    }

    refreshPage() {
        this.replacePageCanvas(this.page_html);
        this.addEvents();
    }

    addEvents() {
        let $ = this.jquery;

        $(this.canvas_id + " ")
    }
}