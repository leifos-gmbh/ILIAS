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
import ActionFactory from "../actions/iim-action-factory.js"
import UI from "./iim-ui.js";
import Util from "../../../../../Editor/js/src/ui/util.js";

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
        this.util = new Util();
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
     * @param {IIMModel} model
     */
    handle(action, model) {
        const dispatcher = this.dispatcher;
        const actionFactory = this.actionFactory;
        const client = this.client;
        let form_sent = false;

        const params = action.getParams();

        // page actions
        if (action.getComponent() === "InteractiveImage") {
            switch (action.getType()) {

                case ACTIONS.E_ADD_TRIGGER:
                    this.ui.editTrigger(model.getCurrentTrigger().nr);
                    break;

                case ACTIONS.E_EDIT_TRIGGER:
                    this.ui.editTrigger(params.triggerNr);
                    break;

                case ACTIONS.E_TRIGGER_SHAPE_CHANGE:
                    this.ui.repaintTrigger();
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

                case ACTIONS.E_TRIGGER_PROPERTIES_SAVE:
                    this.sendSaveTriggerPropertiesCommand(
                      params,
                      model
                    );
                    break;

                case ACTIONS.E_TRIGGER_OVERLAY_ADD:
                    this.ui.showOverlayModal();
                    break;

                case ACTIONS.E_TRIGGER_POPUP_ADD:
                    this.ui.showPopupModal();
                    break;

                case ACTIONS.E_POPUP_SAVE:
                    this.sendPopupSave(params, model);
                    break;

                case ACTIONS.E_POPUP_RENAME:
                    this.ui.showPopupModal(params, model);
                    break;

                case ACTIONS.E_POPUP_DELETE:
                    this.sendDeletePopup(params, model);
                    break;

                case ACTIONS.E_OVERLAY_UPLOAD:
                    this.sendUploadOverlay(params, model);
                    break;

                case ACTIONS.E_OVERLAY_DELETE:
                    this.sendDeleteOverlay(params, model);
                    break;

            }
        }
    }

    sendSaveTriggerPropertiesCommand(params, model) {
        let update_action;
        const af = this.actionFactory;
        const dispatch = this.dispatcher;

        update_action = af.interactiveImage().command().saveTriggerProperties(
          params.nr,
          params.title,
          params.shapeType,
          params.coords
        );

        this.client.sendCommand(update_action).then(result => {
            this.handleStandardResponse(result, model);
            //dispatch.dispatch(af.page().editor().enablePageEditing());
        });
    }

    handleStandardResponse(result, model)
    {
        const pl = result.getPayload();

        if(pl.error === false)
        {
            model.initModel(pl.model);
        }
    }

    sendUploadOverlay(params, model) {
        let upload_action;
        const af = this.actionFactory;
        const dispatch = this.dispatcher;
        const util = this.util;

        this.util.sendFiles(params.data.form).then(() => {
            const data = new FormData(params.data.form);
            upload_action = af.interactiveImage().command().uploadOverlay(
              data
            );

            this.client.sendCommand(upload_action).then(result => {
                util.hideCurrentModal();
                this.handleStandardResponse(result, model);
                //after_pcid, pcid, component, data
                dispatch.dispatch(af.interactiveImage().editor().triggerOverlay());
            });
        });
    }

    sendDeleteOverlay(params, model) {
        const af = this.actionFactory;
        const dispatch = this.dispatcher;
        const delete_action = af.interactiveImage().command().deleteOverlay(
          params.overlay
        );
        this.client.sendCommand(delete_action).then(result => {
            this.handleStandardResponse(result, model);
            dispatch.dispatch(af.interactiveImage().editor().switchOverlays());
        });
    }

    sendPopupSave(params, model) {
        const af = this.actionFactory;
        const dispatch = this.dispatcher;
        const util = this.util;
        const data = new FormData(params.data.form);
        data.append('nr', params.nr);
        const save_action = af.interactiveImage().command().savePopup(
          data
        );
        this.client.sendCommand(save_action).then(result => {
            this.handleStandardResponse(result, model);
            util.hideCurrentModal();
            dispatch.dispatch(af.interactiveImage().editor().switchPopups());
        });
    }

    sendDeletePopup(params, model) {
        const af = this.actionFactory;
        const dispatch = this.dispatcher;
        const delete_action = af.interactiveImage().command().deletePopup(
          params.nr
        );
        this.client.sendCommand(delete_action).then(result => {
            this.handleStandardResponse(result, model);
            dispatch.dispatch(af.interactiveImage().editor().switchPopups());
        });
    }

}