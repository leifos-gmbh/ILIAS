/// <reference path="../typings/JQueryStatic.d.ts" />
import RPCQuery from "./RPCQuery";

declare var $: JQueryStatic;

export default class RpcClient {
    endpoint: string;
    jquery: JQueryStatic;
    queries: RPCQuery[];

    /**
     * Constructor
     * @param {JQueryStatic} jquery
     * @param {string} endpoint
     */
    constructor(jquery: JQueryStatic, endpoint: string) {
        this.endpoint = endpoint;
        this.jquery = jquery;
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
        return new Promise((resolve, reject) => {

            while (query = this.queries.shift()) {
                packaged_queries.push({
                    jsonrpc: '2.0',
                    method: query.method,
                    id: query.id,
                    params: query.params
                });
            }

            this.jquery.ajax({
                url: this.endpoint,
                data: JSON.stringify (packaged_queries),
                type: "POST",
                dataType: "json",
                success: function(r) {

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
    }
}