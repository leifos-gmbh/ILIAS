"use strict";
exports.__esModule = true;
var Operation_1 = require("./Operation");
var OperationResponse_1 = require("./OperationResponse");
/**
 * Operation factory
 */
var OperationFactory = /** @class */ (function () {
    function OperationFactory() {
    }
    /**
     *
     * @param {OperationType} type
     * @param {string} pcid
     * @param {string} targetid
     * @param {object} pcmodel
     * @returns {Operation}
     */
    OperationFactory.prototype.operation = function (type, pcid, targetid, pcmodel) {
        return new Operation_1["default"](type, pcid, targetid, pcmodel);
    };
    /**
     *
     * @param {OperationType} type
     * @param {boolean} is_error
     * @param {object} result
     * @returns {OperationResponse}
     */
    OperationFactory.prototype.operationResponse = function (type, is_error, result) {
        return new OperationResponse_1["default"](type, is_error, result);
    };
    return OperationFactory;
}());
exports["default"] = OperationFactory;
//# sourceMappingURL=OperationFactory.js.map