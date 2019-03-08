import RpcClient from './RpcClient';
import Controller from './Controller';
import OperationFactory from './OperationFactory';
import OpQueue from "./OpQueue";
import OperationRpcGateway from "./OperationRpcGateway";
import RPCQueryFactory from "./RPCQueryFactory";

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
            let rpc_query_factory: RPCQueryFactory;

            function init(endpoint: string) {

                // rpc stuff
                rpcclient = new RpcClient(jquery, endpoint);
                rpc_query_factory = new RPCQueryFactory();

                // operation stuff
                op_factory = new OperationFactory();
                op_queue = new OpQueue();
                op_rpc_gateway = new OperationRpcGateway(rpcclient, rpc_query_factory);

                // main controller
                controller = new Controller(op_rpc_gateway, op_factory, op_queue);
                controller.initEditor();
            }

            return {
                init
            };
    })($);
})($, il);