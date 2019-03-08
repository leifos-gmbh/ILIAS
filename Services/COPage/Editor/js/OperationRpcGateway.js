"use strict";
exports.__esModule = true;
var OperationRpcGateway = /** @class */ (function () {
    function OperationRpcGateway(rpc_client, rpc_query_factory) {
        this.rpc_client = rpc_client;
        this.rpc_query_factory = rpc_query_factory;
    }
    OperationRpcGateway.prototype.sendQueue = function (queue) {
        var _this = this;
        return new Promise(function (resolve, reject) {
            var op;
            while (op = queue.pop()) {
                _this.rpc_client.addQuery(_this.rpc_query_factory.query(op.type, {
                    pcid: op.pcid,
                    targetid: op.targetid,
                    model: op.pcmodel
                }));
            }
            _this.rpc_client.send()
                .then(function (resp) {
                //@todo: transform RPCResponse array into Operation Response Object
                resolve(resp);
            })["catch"](function (err) {
                //@todo: transform error into Operation Response Object
                reject(err);
            });
        });
    };
    return OperationRpcGateway;
}());
exports["default"] = OperationRpcGateway;
//# sourceMappingURL=OperationRpcGateway.js.map