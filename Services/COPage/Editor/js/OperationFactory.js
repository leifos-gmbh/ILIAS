"use strict";
exports.__esModule = true;
var Operation_1 = require("./Operation");
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
     * @param {Object} pcmodel
     * @returns {Operation}
     */
    OperationFactory.prototype.operation = function (type, pcid, targetid, pcmodel) {
        return new Operation_1["default"](type, pcid, targetid, pcmodel);
    };
    return OperationFactory;
}());
exports["default"] = OperationFactory;
//# sourceMappingURL=OperationFactory.js.map