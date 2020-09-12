/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Model action handler
 */
export default class ModelActionHandler {

  /**
   * {Model}
   */
  model;

  /**
   *
   * @param {Model} model
   */
  constructor(model) {
    this.model = model;
  }


  /**
   * @return {Model}
   */
  getModel() {
    return this.model;
  }

  /**
   * @param {EditorAction} action
   */
  handle(action) {

    const params = action.getParams();

    switch (action.getType()) {

      case "dnd.drag":
        this.model.setState(this.model.STATE_DRAG_DROP);
        break;

      case "dnd.drop":
        this.model.setState(this.model.STATE_PAGE);
        break;

      case "multi.toggle":
        this.model.toggleSelect(params.pcid, params.hierid);
        console.log(this.model.hasSelected());
        if (this.model.hasSelected()) {
          this.model.setState(this.model.STATE_MULTI_ACTION);
        } else {
          this.model.setState(this.model.STATE_PAGE);
        }
        console.log(this.model.getState());
        break;

      case "multi.action":
        switch (params.type) {
          case "none":
            this.model.selectNone();
            this.model.setState(this.model.STATE_PAGE);
            break;

          case "all":
            this.model.selectAll();
            this.model.setState(this.model.STATE_MULTI_ACTION);
            break;
        }
        break;

      case "component.edit":
        this.model.setState(this.model.STATE_COMPONENT);
        this.model.setComponentState(this.model.STATE_COMPONENT_EDIT);
        this.model.setCurrentPageComponent(params.cname, params.pcid, params.hierid);

        this.model.setUndoPCModel(
          this.model.getCurrentPCId(),
          this.model.getPCModel(this.model.getCurrentPCId())
        );
        break;

      case "component.insert":
        this.model.setState(this.model.STATE_COMPONENT);
        this.model.setComponentState(this.model.STATE_COMPONENT_INSERT);
        this.model.setCurrentInsertPCId(params.pcid);   // insert after...
        const pcid = this.model.getNewPCId();
        this.model.setCurrentPageComponent(params.cname, pcid, '');
        break;

      case "component.cancel":
        this.model.undoPCModel(
          this.model.getCurrentPCId()
        );
        this.model.setState(this.model.STATE_PAGE);
        // note: we keep the component state and current component here, so that handlers
        // can use this
        break;

    }
  }
}