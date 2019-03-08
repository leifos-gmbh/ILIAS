export default class RPCQuery {
    method: string;
    params: Object;
    id: number;

    constructor(method: string, params: Object) {
        this.method = method;
        this.params = params;
        this.id = Date.now();
    }
}