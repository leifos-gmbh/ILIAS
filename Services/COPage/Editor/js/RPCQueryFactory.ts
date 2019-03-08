import RPCQuery from './RPCQuery';

/**
 * RPC query factory
 */
export default class RPCQueryFactory {

    constructor() {
    }

    /**
     *
     * @param {string} method
     * @param {Object} params
     * @returns {RPCQuery}
     */
    query(method: string, params: Object): RPCQuery {
        return new RPCQuery(method, params);
    }
}