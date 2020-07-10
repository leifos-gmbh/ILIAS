/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import PageModel from "../components/page/model/page-model.js";

/**
 * Controller (handles editor initialisation process)
 */
export default class Model {
  /**
   * @type {Object}
   */
  models = new Map();

  constructor() {
    this.models.set("page", new PageModel());
  }

  model(key) {
    return this.models.get(key);
  }
}