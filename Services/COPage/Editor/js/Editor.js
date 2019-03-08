"use strict";
exports.__esModule = true;
var RpcClient_1 = require("./RpcClient");
var Controller_1 = require("./Controller");
var OperationFactory_1 = require("./OperationFactory");
var OpQueue_1 = require("./OpQueue");
var OperationRpcGateway_1 = require("./OperationRpcGateway");
var RPCQueryFactory_1 = require("./RPCQueryFactory");
il = il || {};
il.copg = il.copg || {};
(function ($, il) {
    il.copg.editor = (function ($) {
        var jquery = $;
        var rpcclient;
        var controller;
        var op_factory;
        var op_queue;
        var op_rpc_gateway;
        var rpc_query_factory;
        function init(endpoint) {
            // rpc stuff
            rpcclient = new RpcClient_1["default"](jquery, endpoint);
            rpc_query_factory = new RPCQueryFactory_1["default"]();
            // operation stuff
            op_factory = new OperationFactory_1["default"]();
            op_queue = new OpQueue_1["default"]();
            op_rpc_gateway = new OperationRpcGateway_1["default"](rpcclient, rpc_query_factory);
            // main controller
            controller = new Controller_1["default"](op_rpc_gateway, op_factory, op_queue);
            controller.initEditor();
        }
        return {
            init: init
        };
    })($);
})($, il);
//# sourceMappingURL=Editor.js.map