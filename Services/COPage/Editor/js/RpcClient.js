"use strict";
exports.__esModule = true;
var RpcClient = /** @class */ (function () {
    /**
     * Constructor
     * @param {JQueryStatic} jquery
     * @param {string} endpoint
     * @param {RPCFactory} rpc_factory
     */
    function RpcClient(jquery, endpoint, rpc_factory) {
        this.endpoint = endpoint;
        this.jquery = jquery;
        this.rpc_factory = rpc_factory;
        this.queries = [];
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
        var query_map;
        var responses;
        var rpc_factory;
        return new Promise(function (resolve, reject) {
            query_map = new Map();
            while (query = _this.queries.shift()) {
                packaged_queries.push({
                    jsonrpc: '2.0',
                    method: query.method,
                    id: query.id,
                    params: query.params
                });
                query_map.set(query.id, query);
            }
            rpc_factory = _this.rpc_factory;
            _this.jquery.ajax({
                url: _this.endpoint,
                data: JSON.stringify(packaged_queries),
                type: "POST",
                dataType: "json",
                success: function (r) {
                    var i;
                    // transform response into RPCResponse array
                    responses = [];
                    if (Array.isArray(r)) {
                        for (i = 0; i < r.length; ++i) {
                            console.log(r[i]);
                            if (query = query_map.get(r[i].id)) {
                                responses.push(rpc_factory.response(query, false, r[i].result));
                            }
                        }
                    }
                    resolve(responses);
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