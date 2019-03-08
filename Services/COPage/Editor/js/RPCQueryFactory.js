"use strict";
exports.__esModule = true;
var RPCQuery_1 = require("./RPCQuery");
/**
 * RPC query factory
 */
var RPCQueryFactory = /** @class */ (function () {
    function RPCQueryFactory() {
    }
    /**
     *
     * @param {string} method
     * @param {Object} params
     * @returns {RPCQuery}
     */
    RPCQueryFactory.prototype.query = function (method, params) {
        return new RPCQuery_1["default"](method, params);
    };
    return RPCQueryFactory;
}());
exports["default"] = RPCQueryFactory;
//# sourceMappingURL=RPCQueryFactory.js.map