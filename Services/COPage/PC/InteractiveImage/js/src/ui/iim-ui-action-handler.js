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
 *
 *********************************************************************/

import ACTIONS from "../actions/iim-action-types.js";

/**
 * Interactive image UI action handler
 */
export default class IIMUIActionHandler {

    /**
     * @type {ActionFactory}
     */
    //actionFactory;

    /**
     * @type {Dispatcher}
     */
    //dispatcher;

    /**
     * @type {Client}
     */
    //client;

    /**
     * @param {ActionFactory} actionFactory
     * @param {Client} client
     */
    constructor(actionFactory, client) {
        this.actionFactory = actionFactory;
        this.client = client;
        this.ui = null;
        this.dispatcher = null;
    }

    /**
     * @param {PlaceHolderUI} ui
     */
    setUI(ui) {
        this.ui = ui;
    }

    /**
     * @param {Dispatcher} dispatcher
     */
    setDispatcher(dispatcher) {
        this.dispatcher = dispatcher;
    }

    /**
     * @param {EditorAction} action
     * @param {PageModel} page_model
     */
    handle(action, page_model) {

        const dispatcher = this.dispatcher;
        const actionFactory = this.actionFactory;
        const client = this.client;
        let form_sent = false;

        const params = action.getParams();

        // page actions
        /*
        if (action.getComponent() === "Page" && page_model.getCurrentPCName() === "PlaceHolder") {
            switch (action.getType()) {

                case PAGE_ACTIONS.COMPONENT_EDIT:
                    this.handleEditCommand(page_model, params);
                    break;
            }
        }*/

    }
}