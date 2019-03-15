export default class RPCQuery {
    method: string;
    params: Object;
    id: number;

    constructor(method: string, params: Object, id: number) {
        this.method = method;
        this.params = params;
        this.id = id;
    }
}