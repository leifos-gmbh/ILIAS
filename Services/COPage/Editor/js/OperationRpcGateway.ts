import OpQueue from './OpQueue';
import Operation from "./Operation";
import RpcClient from "./RpcClient";
import RPCFactory from "./RPCFactory";
import OperationFactory from "./OperationFactory";

export default class OperationRpcGateway {
    rpc_client: RpcClient;
    rpc_factory: RPCFactory;
    op_factory: OperationFactory;

    constructor(rpc_client: RpcClient, rpc_factory: RPCFactory, op_factory: OperationFactory) {
        this.rpc_client = rpc_client;
        this.rpc_factory = rpc_factory;
        this.op_factory = op_factory;
    }

    sendQueue(queue: OpQueue): Promise<any> {

        return new Promise((resolve, reject) => {
            let op: Operation;
            let op_factory: OperationFactory;

            op_factory = this.op_factory;

            while (op = queue.pop()) {
                this.rpc_client.addQuery(this.rpc_factory.query(op.type, {
                    pcid: op.pcid,
                    targetid: op.targetid,
                    model: op.pcmodel
                }));
            }

            this.rpc_client.send()
                .then((r) => {
                    let i: number;
                    let op_responses: Array<any>;

                    op_responses = [];
                    if (Array.isArray(r)) {
                        for (i = 0; i < r.length; ++i) {
                            console.log(r[i]);
                            op_responses.push(op_factory.operationResponse(r[i].query.method, r[i].is_error, r[i].response))
                        }
                    }

                    resolve(op_responses);
                })
                .catch((err) => {

                    //@todo: transform error into Operation Response Object

                    reject(err);
                }
            );

        });

    }
}