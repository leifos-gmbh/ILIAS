import PageContentInterface from './PageContentInterface';

/**
 * Paragraph Page Content Element
 */
export default class PCParagraph implements PageContentInterface {

    constructor() {
    }

    /**
     * @inheritDoc
     */
    initEditForm(pcid: string, model, $el, $form_canvas, model_updater: Function): void {
        let $text_field = $form_canvas.find("#text");
        let $select_dropdown = $form_canvas.find("#characteristic");

        // init form
        $text_field.val(model.text);
        $form_canvas.find("#characteristic option[value='" + model.characteristic + "']").attr('selected', true);

        // handle updates
        $text_field.attr("onkeyup", null);
        $text_field.on("input", function() {
            let text = $text_field.val();
            $el.find(".ilc_Paragraph").html(text);
            model.text = text;
            model_updater(model);
        });

        $select_dropdown.on("input", function() {
            let characteristic = $select_dropdown.val();
            $el.find(".ilc_Paragraph").removeClass().addClass("ilc_Paragraph").addClass("ilc_text_block_" + characteristic);
            model.characteristic = characteristic;
            model_updater(model);
        });
    }

    /**
     * @inheritDoc
     */
    getNew() {
        return {
            model: {
                text: '',
                characteristic: 'Standard'
            },
            html: '<div class="ilc_Paragraph ilc_text_block_Standard"></div>'
        };
    }
}