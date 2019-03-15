"use strict";
exports.__esModule = true;
var RPCQuery_1 = require("./RPCQuery");
var RPCResponse_1 = require("./RPCResponse");
/**
 * RPC query factory
 */
var RPCFactory = /** @class */ (function () {
    function RPCFactory() {
        this.id = 0;
    }
    /**
     *
     * @param {string} method
     * @param {Object} params
     * @returns {RPCQuery}
     */
    RPCFactory.prototype.query = function (method, params) {
        this.id++;
        return new RPCQuery_1["default"](method, params, this.id);
    };
    /**
     *
     * @param {RPCQuery} query
     * @param {boolean} is_error
     * @param {Object} response
     * @returns {RPCResponse}
     */
    RPCFactory.prototype.response = function (query, is_error, response) {
        return new RPCResponse_1["default"](query, is_error, response);
    };
    return RPCFactory;
}());
exports["default"] = RPCFactory;
//# sourceMappingURL=RPCFactory.js.map