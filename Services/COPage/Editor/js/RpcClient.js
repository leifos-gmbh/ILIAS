"use strict";
exports.__esModule = true;
var RpcClient = /** @class */ (function () {
    /**
     * Constructor
     * @param {JQueryStatic} jquery
     * @param {string} endpoint
     */
    function RpcClient(jquery, endpoint) {
        this.endpoint = endpoint;
        this.jquery = jquery;
    }
    RpcClient.prototype.addQuery = function (rpc_query) {
        this.queries.push(rpc_query);
    };
    /**
     * Send rp call
     * @returns {Promise<any>}
     */
    RpcClient.prototype.send = function () {
        var _this = this;
        var packaged_queries = [];
        var query;
        return new Promise(function (resolve, reject) {
            while (query = _this.queries.shift()) {
                packaged_queries.push({
                    jsonrpc: '2.0',
                    method: query.method,
                    id: query.id,
                    params: query.params
                });
            }
            _this.jquery.ajax({
                url: _this.endpoint,
                data: JSON.stringify(packaged_queries),
                type: "POST",
                dataType: "json",
                success: function (r) {
                    //@todo: transform response into RPCResponse array
                    console.log(r);
                    resolve(r);
                },
                error: function (err) {
                    reject(err);
                }
            });
            /* this.jquery.ajax({
                url: this.endpoint,
                data: JSON.stringify ({
                    jsonrpc: '2.0',
                    method: method,
                    params: params,
                    id: Date.now()
                }),
                type: "POST",
                dataType: "json",
                success: function(r) {
                    resolve(r);
                },
                error: function (err) {
                    reject(err);
                }
            });*/
        });
    };
    return RpcClient;
}());
exports["default"] = RpcClient;
//# sourceMappingURL=RpcClient.js.map