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
import UI from "./iim-ui.js";

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
     * @param {UI} ui
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
        if (action.getComponent() === "InteractiveImage") {
            switch (action.getType()) {

                case ACTIONS.E_ADD_TRIGGER:
                    this.ui.addTrigger();
                    break;

                case ACTIONS.E_EDIT_TRIGGER:
                    this.ui.editTrigger(params.triggerNr);
                    break;

                case ACTIONS.E_TRIGGER_PROPERTIES:
                    this.ui.showTriggerProperties();
                    break;

                case ACTIONS.E_TRIGGER_OVERLAY:
                    this.ui.showTriggerOverlay();
                    break;

                case ACTIONS.E_TRIGGER_POPUP:
                    this.ui.showTriggerPopup();
                    break;

                case ACTIONS.E_TRIGGER_BACK:
                    this.ui.showMainScreen();
                    break;

                case ACTIONS.E_SWITCH_SETTINGS:
                    this.ui.showSettings();
                    break;

                case ACTIONS.E_SWITCH_OVERLAYS:
                    this.ui.showOverlays();
                    break;

                case ACTIONS.E_SWITCH_POPUPS:
                    this.ui.showPopups();
                    break;
            }
        }
    }
}