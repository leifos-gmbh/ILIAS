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
     */
    function Controller(operation_rpc_gateway, op_factory, op_queue) {
        //this.rpcclient = rpcclient;
        this.op_factory = op_factory;
        this.op_queue = op_queue;
        this.operation_rpc_gateway = operation_rpc_gateway;
    }
    /**
     *
     */
    Controller.prototype.initEditor = function () {
        // create operations, add them to queue
        this.op_queue.push(this.op_factory.operation(OperationType_1["default"].PageHtml, "", "", {}));
        this.operation_rpc_gateway.sendQueue(this.op_queue);
        // send queue package
        // receive Operation responses
        /*
        this.rpcclient.send("page/html", [3, 4], function(result) {
            console.log(result);
        });*/
    };
    return Controller;
}());
exports["default"] = Controller;
//# sourceMappingURL=Controller.js.map