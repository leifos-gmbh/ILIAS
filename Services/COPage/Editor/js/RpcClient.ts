/// <reference path="../typings/JQueryStatic.d.ts" />
import RPCQuery from "./RPCQuery";
import RPCFactory from "./RPCFactory";
import RPCResponse from "./RPCResponse";

declare var $: JQueryStatic;

export default class RpcClient {
    endpoint: string;
    jquery: JQueryStatic;
    rpc_factory: RPCFactory;
    queries: RPCQuery[];

    /**
     * Constructor
     * @param {JQueryStatic} jquery
     * @param {string} endpoint
     * @param {RPCFactory} rpc_factory
     */
    constructor(jquery: JQueryStatic, endpoint: string, rpc_factory: RPCFactory) {
        this.endpoint = endpoint;
        this.jquery = jquery;
        this.rpc_factory = rpc_factory;
        this.queries = [];
    }

    addQuery(rpc_query: RPCQuery): void {
        this.queries.push(rpc_query);
    }

    /**
     * Send rp call
     * @returns {Promise<any>}
     */
    send (): Promise<any> {
        let packaged_queries = [];
        let query: RPCQuery;
        let query_map: Map<number, RPCQuery>;
        let responses: RPCResponse[];
        let rpc_factory: RPCFactory;

        return new Promise((resolve, reject) => {

            query_map = new Map<number, RPCQuery>();

            while (query = this.queries.shift()) {
                packaged_queries.push({
                    jsonrpc: '2.0',
                    method: query.method,
                    id: query.id,
                    params: query.params
                });

                query_map.set(query.id, query);
            }

            rpc_factory = this.rpc_factory;

            this.jquery.ajax({
                url: this.endpoint,
                data: JSON.stringify (packaged_queries),
                type: "POST",
                dataType: "json",
                success: function(r) {
                    let i: number;

                    // transform response into RPCResponse array
                    responses = [];
                    if (Array.isArray(r)) {
                        for (i = 0; i < r.length; ++i) {
                            if (query = query_map.get(r[i].id)) {
                                responses.push(rpc_factory.response(query, false, r[i].result))
                            }
                        }
                    }

                    resolve(responses);
                },
                error: function (err) {
                    reject(err);
                }
            });
        });
    }
}