"use strict";
exports.__esModule = true;
var OperationRpcGateway = /** @class */ (function () {
    function OperationRpcGateway(rpc_client, rpc_factory, op_factory) {
        this.rpc_client = rpc_client;
        this.rpc_factory = rpc_factory;
        this.op_factory = op_factory;
    }
    OperationRpcGateway.prototype.sendQueue = function (queue) {
        var _this = this;
        return new Promise(function (resolve, reject) {
            var op;
            var op_factory;
            op_factory = _this.op_factory;
            while (op = queue.pop()) {
                _this.rpc_client.addQuery(_this.rpc_factory.query(op.type, {
                    pcid: op.pcid,
                    targetid: op.targetid,
                    model: op.pcmodel
                }));
            }
            _this.rpc_client.send()
                .then(function (r) {
                var i;
                var op_responses;
                op_responses = [];
                if (Array.isArray(r)) {
                    for (i = 0; i < r.length; ++i) {
                        console.log(r[i]);
                        op_responses.push(op_factory.operationResponse(r[i].query.method, r[i].is_error, r[i].response));
                    }
                }
                resolve(op_responses);
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