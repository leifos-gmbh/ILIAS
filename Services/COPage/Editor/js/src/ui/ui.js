/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import PageUI from '../components/page/ui/page-ui.js';
import ParagraphUI from '../components/paragraph/ui/paragraph-ui.js';

/**
 * editor ui
 */
export default class UI {

  /**
   * UI model
   * @type {Object}
   */
  uiModel = {};

  /**
   * Model
   * @type {Model}
   */
  model = {};

  /**
   * @type {Client}
   */
  client;

  /**
   * @type {Dispatcher}
   */
  dispatcher;

  /**
   * @type {ActionFactory}
   */
  actionFactory;

  /**
   * @type {PageUI}
   */
  page;

  /**
   * @type {ParagraphUI}
   */
  paragraph;

  /**
   * @type {ToolSlate}
   */
  toolSlate;

  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {Model} model
   * @param {ToolSlate} toolSlate
   */
  constructor(client, dispatcher, actionFactory, model, toolSlate) {
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.model = model;
    this.toolSlate = toolSlate;
    // @todo we need a ui factory here...
    this.page = new PageUI(
      this.client,
      this.dispatcher,
      this.actionFactory,
      this.model.model("page"),
      this.toolSlate);
    this.paragraph = new ParagraphUI(
      this.client,
      this.dispatcher,
      this.actionFactory,
      this.model.model("page"),
      this.toolSlate);
  }

  //
  // Initialisation
  //

  /**
   */
  init() {
    const ui_all_action = this.actionFactory.page().query().uiAll();
    this.client.sendQuery(ui_all_action).then(result => {
      this.uiModel = result.getPayload();


      // move page component model to model
      console.log(this.uiModel);
      this.model.model("page").setComponentModel(this.uiModel.pcModel);
      this.uiModel.pcModel = null;

      this.toolSlate.init(this.uiModel);
      this.page.init(this.uiModel);
      this.paragraph.init(this.uiModel);
    });
  }

  /**
   */
  reInit() {
    this.page.reInit();
  }
}
