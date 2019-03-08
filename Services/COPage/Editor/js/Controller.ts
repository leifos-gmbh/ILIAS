import RpcClient from "./RpcClient";
import OperationFactory from "./OperationFactory";
import OpQueue from "./OpQueue";
import OperationType from "./OperationType";
import OperationRpcGateway from "./OperationRpcGateway";

/**
 * Stores operations being done in the editor
 */
export default class Controller {

    //rpcclient: RpcClient;
    op_factory: OperationFactory;
    op_queue: OpQueue;
    operation_rpc_gateway: OperationRpcGateway;


    /**
     *
     * @param {OperationRpcGateway} operation_rpc_gateway
     * @param {OperationFactory} op_factory
     * @param {OpQueue} op_queue
     */
    constructor(operation_rpc_gateway: OperationRpcGateway, op_factory: OperationFactory, op_queue: OpQueue) {
        //this.rpcclient = rpcclient;
        this.op_factory = op_factory;
        this.op_queue = op_queue;
        this.operation_rpc_gateway = operation_rpc_gateway;
    }

    /**
     *
     */
    initEditor(): void {

        // create operations, add them to queue
        this.op_queue.push(this.op_factory.operation(OperationType.PageHtml, "", "", {}));

        this.operation_rpc_gateway.sendQueue(this.op_queue);


        // send queue package

        // receive Operation responses
        /*
        this.rpcclient.send("page/html", [3, 4], function(result) {
            console.log(result);
        });*/
    }

}