export default interface PageContentInterface {

    /**
     *
     * @param {string} pcid
     * @param model
     * @param $el
     * @param $form_canvas
     * @param {Function} model_updater
     */
    initEditForm(pcid: string, model, $el, $form_canvas, model_updater: Function): void

    /**
     *
     * @returns {object}
     */
    getNew(): object;
}