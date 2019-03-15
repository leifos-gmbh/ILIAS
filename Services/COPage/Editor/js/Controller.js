"use strict";
exports.__esModule = true;
var OperationType_1 = require("./OperationType");
/**
 * Stores operations being done in the editor
 */
var Controller = /** @class */ (function () {
    /**
     *
     * @param {OperationRpcGateway} operation_rpc_gateway
     * @param {OperationFactory} op_factory
     * @param {OpQueue} op_queue
     * @param {UI} ui
     */
    function Controller(operation_rpc_gateway, op_factory, op_queue, ui) {
        //this.rpcclient = rpcclient;
        this.op_factory = op_factory;
        this.op_queue = op_queue;
        this.operation_rpc_gateway = operation_rpc_gateway;
        this.ui = ui;
    }
    /**
     *
     */
    Controller.prototype.initEditor = function () {
        var ui;
        ui = this.ui;
        // get page html
        this.pushOperation(OperationType_1["default"].PageHtml, "", "", {});
        this.pushOperation(OperationType_1["default"].PageModel, "", "", {});
        this.pushOperation(OperationType_1["default"].UIAll, "", "", {});
        this.sendOperations().then(function (r) {
            ui.refreshPage();
        });
    };
    /**
     * Push an operation to the op queue
     *
     * @param {OperationType} type
     * @param {string} pcid
     * @param {string} targetid
     * @param {Object} pcmodel
     */
    Controller.prototype.pushOperation = function (type, pcid, targetid, pcmodel) {
        this.op_queue.push(this.op_factory.operation(type, pcid, targetid, pcmodel));
    };
    /**
     * Send operations
     */
    Controller.prototype.sendOperations = function () {
        var _this = this;
        return new Promise(function (resolve, reject) {
            var controller;
            var i;
            controller = _this;
            _this.operation_rpc_gateway.sendQueue(_this.op_queue).then(function (r) {
                if (Array.isArray(r)) {
                    for (i = 0; i < r.length; ++i) {
                        //@todo: handle is_error
                        controller.processResponse(r[i].type, r[i].result);
                    }
                }
                resolve();
            })["catch"](function (err) {
                reject(err);
            });
        });
    };
    /**
     *
     * @param {string} type
     * @param {Object} result
     */
    Controller.prototype.processResponse = function (type, result) {
        console.log("processResponse");
        console.log(type);
        console.log(result);
        switch (type) {
            case OperationType_1["default"].PageHtml:
                this.handlePageHTML(result);
                break;
            case OperationType_1["default"].UIAll:
                this.handleUIAll(result);
                break;
        }
    };
    /**
     *
     * @param result
     */
    Controller.prototype.handlePageHTML = function (result) {
        this.ui.setPageHtml(result);
    };
    /**
     *
     * @param result
     */
    Controller.prototype.handleUIAll = function (result) {
        this.ui.setUIComponentModel(result);
    };
    return Controller;
}());
exports["default"] = Controller;
//# sourceMappingURL=Controller.js.map