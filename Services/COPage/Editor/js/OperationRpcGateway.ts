import OpQueue from './OpQueue';
import Operation from "./Operation";
import RpcClient from "./RpcClient";
import RPCQueryFactory from "./RPCQueryFactory";

export default class OperationRpcGateway {
    rpc_client: RpcClient;
    rpc_query_factory: RPCQueryFactory;

    constructor(rpc_client: RpcClient, rpc_query_factory: RPCQueryFactory) {
        this.rpc_client = rpc_client;
        this.rpc_query_factory = rpc_query_factory;
    }

    sendQueue(queue: OpQueue): Promise<any> {

        return new Promise((resolve, reject) => {
            let op: Operation;
            while (op = queue.pop()) {
                this.rpc_client.addQuery(this.rpc_query_factory.query(op.type, {
                    pcid: op.pcid,
                    targetid: op.targetid,
                    model: op.pcmodel
                }));
            }

            this.rpc_client.send()
                .then((resp) => {

                    //@todo: transform RPCResponse array into Operation Response Object

                    resolve(resp);
                })
                .catch((err) => {

                    //@todo: transform error into Operation Response Object

                    reject(err);
                }
            );

        });

    }
}