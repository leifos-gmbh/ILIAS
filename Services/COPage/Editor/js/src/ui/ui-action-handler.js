/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import PageUIActionHandler from '../components/page/ui/page-ui-action-handler.js';
import ParagraphUIActionHandler from '../components/paragraph/ui/paragraph-ui-action-handler.js';
import MediaUIActionHandler from '../components/media/ui/media-ui-action-handler.js';

/**
 * UI action handler
 */
export default class UIActionHandler {

  /**
   * @type {UI}
   */
  ui;

  /**
   * @type {ActionFactory}
   */
  actionFactory;

  /**
   * @type {Dispatcher}
   */
  dispatcher;

  /**
   * @type {Client}
   */
  client;

  /**
   * @type {PageUIActionHandler}
   */
  pageActionHandler;

  /**
   * @type {ParagraphUIActionHandler}
   */
  paragraphActionHandler;

  /**
   * @type {MediaUIActionHandler}
   */
  mediaActionHandler;

  /**
   * @param {ActionFactory} actionFactory
   * @param {Client} client
   */
  constructor(actionFactory, client) {
    this.actionFactory = actionFactory;
    this.client = client;
    // @todo needs factory
    this.pageActionHandler = new PageUIActionHandler(
      this.actionFactory,
      this.client
    );
    this.paragraphActionHandler = new ParagraphUIActionHandler(
      this.actionFactory,
      this.client
    );
    this.mediaActionHandler = new MediaUIActionHandler(
      this.actionFactory,
      this.client
    );
  }

  /**
   * @param {UI} ui
   */
  setUI(ui) {
    this.ui = ui;
    this.pageActionHandler.setUI(this.ui.page);
    this.paragraphActionHandler.setUI(this.ui.paragraph);
    this.mediaActionHandler.setUI(this.ui.media);
  }

  /**
   * @param {DISPATCHER} dispatcher
   */
  setDispatcher(dispatcher) {
    this.dispatcher = dispatcher;
    this.pageActionHandler.setDispatcher(dispatcher);
    this.paragraphActionHandler.setDispatcher(dispatcher);
    this.mediaActionHandler.setDispatcher(dispatcher);
  }

  /**
   * @param {EditorAction} action
   * @param {Model} model
   */
  handle(action, model) {
    this.pageActionHandler.handle(action, model.model("page"));
    this.paragraphActionHandler.handle(action, model.model("page"));
    this.mediaActionHandler.handle(action, model.model("page"));
  }
}