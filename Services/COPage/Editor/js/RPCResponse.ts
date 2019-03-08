import RPCQuery from "./RPCQuery";

export default class RPCResponse {
    query: RPCQuery;
    response: Object;
    is_error: boolean;

    constructor(query: RPCQuery, is_error: boolean, response: Object) {
        this.query = query;
        this.is_error = is_error;
        this.response = response;
    }
}