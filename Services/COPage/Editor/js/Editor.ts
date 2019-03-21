import RpcClient from './RpcClient';
import Controller from './Controller';
import OperationFactory from './OperationFactory';
import OpQueue from "./OpQueue";
import OperationRpcGateway from "./OperationRpcGateway";
import RPCFactory from "./RPCFactory";
import UI from "./UI";
import PageContentFactory from "./PageContent/PageContentFactory";

/// <reference path="../typings/JQueryStatic.d.ts" />
declare var $: JQueryStatic;
declare var il: any;

il = il || {};
il.copg = il.copg || {};
(function($, il) {
    il.copg.editor = (
        function($) {
            let jquery = $;
            let rpcclient: RpcClient;
            let controller: Controller;
            let op_factory: OperationFactory;
            let op_queue: OpQueue;
            let op_rpc_gateway: OperationRpcGateway;
            let rpc_factory: RPCFactory;
            let ui: UI;
            let pc_factory: PageContentFactory;

            function init(endpoint: string) {

                // rpc stuff
                rpc_factory = new RPCFactory();
                rpcclient = new RpcClient(jquery, endpoint, rpc_factory);

                // operation stuff
                op_factory = new OperationFactory();
                op_queue = new OpQueue();
                op_rpc_gateway = new OperationRpcGateway(rpcclient, rpc_factory, op_factory);

                // editor ui
                pc_factory = new PageContentFactory();
                ui = new UI(jquery, pc_factory);

                // main controller
                controller = new Controller(op_rpc_gateway, op_factory, op_queue, ui);
                controller.initEditor();
            }

            return {
                init
            };
    })($);
})($, il);