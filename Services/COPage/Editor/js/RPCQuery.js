"use strict";
exports.__esModule = true;
var RPCQuery = /** @class */ (function () {
    function RPCQuery(method, params) {
        this.method = method;
        this.params = params;
        this.id = Date.now();
    }
    return RPCQuery;
}());
exports["default"] = RPCQuery;
//# sourceMappingURL=RPCQuery.js.map