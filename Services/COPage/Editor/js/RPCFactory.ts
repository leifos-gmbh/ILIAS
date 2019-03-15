import RPCQuery from './RPCQuery';
import RPCResponse from "./RPCResponse";

/**
 * RPC query factory
 */
export default class RPCFactory {
    id: number;

    constructor() {
        this.id = 0;
    }

    /**
     *
     * @param {string} method
     * @param {Object} params
     * @returns {RPCQuery}
     */
    query(method: string, params: Object): RPCQuery {
        this.id++;
        return new RPCQuery(method, params, this.id);
    }

    /**
     *
     * @param {RPCQuery} query
     * @param {boolean} is_error
     * @param {Object} response
     * @returns {RPCResponse}
     */
    response(query: RPCQuery, is_error: boolean, response: Object) {
        return new RPCResponse(query, is_error, response);
    }

}