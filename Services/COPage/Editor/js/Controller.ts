import RpcClient from "./RpcClient";
import OperationFactory from "./OperationFactory";
import OpQueue from "./OpQueue";
import OperationType from "./OperationType";
import OperationRpcGateway from "./OperationRpcGateway";
import UI from "./UI";

/**
 * Stores operations being done in the editor
 */
export default class Controller {

    //rpcclient: RpcClient;
    op_factory: OperationFactory;
    op_queue: OpQueue;
    operation_rpc_gateway: OperationRpcGateway;
    ui: UI;


    /**
     *
     * @param {OperationRpcGateway} operation_rpc_gateway
     * @param {OperationFactory} op_factory
     * @param {OpQueue} op_queue
     * @param {UI} ui
     */
    constructor(operation_rpc_gateway: OperationRpcGateway, op_factory: OperationFactory, op_queue: OpQueue, ui: UI) {
        //this.rpcclient = rpcclient;
        this.op_factory = op_factory;
        this.op_queue = op_queue;
        this.operation_rpc_gateway = operation_rpc_gateway;
        this.ui = ui;
    }

    /**
     *
     */
    initEditor(): void {
        let ui: UI;

        ui = this.ui;

        // get page html
        this.pushOperation(OperationType.PageHtml, "", "", {});
        this.pushOperation(OperationType.PageModel, "", "", {});
        this.pushOperation(OperationType.UIAll, "", "", {});

        this.sendOperations().then((r) => {
            ui.refreshPage();
        });
    }

    /**
     * Push an operation to the op queue
     *
     * @param {OperationType} type
     * @param {string} pcid
     * @param {string} targetid
     * @param {Object} pcmodel
     */
    pushOperation(type: OperationType, pcid: string, targetid: string, pcmodel: Object): void {
        this.op_queue.push(this.op_factory.operation(type, pcid, targetid, pcmodel));
    }

    /**
     * Send operations
     */
    sendOperations(): Promise<any> {

        return new Promise((resolve, reject) => {
            let controller: Controller;
            let i: number;

            controller = this;

            this.operation_rpc_gateway.sendQueue(this.op_queue).then((r) => {
                if (Array.isArray(r)) {
                    for (i = 0; i < r.length; ++i) {

                        //@todo: handle is_error

                        controller.processResponse(r[i].type, r[i].result);
                    }
                }
                resolve();
            }).catch((err) => {

                reject(err);
            });
        });
    }

    /**
     *
     * @param {string} type
     * @param {Object} result
     */
    processResponse(type: string, result: Object) {
        console.log("processResponse");
        console.log(type);
        console.log(result);
        switch (type)
        {
            case OperationType.PageModel:
                this.handlePageModel(result);
                break;

            case OperationType.PageHtml:
                this.handlePageHTML(result);
                break;

            case OperationType.UIAll:
                this.handleUIAll(result);
                break;
        }
    }

    /**
     *
     * @param result
     */
    handlePageHTML(result) {
        this.ui.setPageHtml(result);
    }

    /**
     *
     * @param result
     */
    handlePageModel(result) {
        this.ui.setPageModel(result);
    }

    /**
     *
     * @param result
     */
    handleUIAll(result) {
        this.ui.setUIComponentModel(result);
    }

}