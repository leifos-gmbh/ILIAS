"use strict";
exports.__esModule = true;
var RpcClient_1 = require("./RpcClient");
var Controller_1 = require("./Controller");
var OperationFactory_1 = require("./OperationFactory");
var OpQueue_1 = require("./OpQueue");
var OperationRpcGateway_1 = require("./OperationRpcGateway");
var RPCFactory_1 = require("./RPCFactory");
var UI_1 = require("./UI");
var PageContentFactory_1 = require("./PageContent/PageContentFactory");
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
        var rpc_factory;
        var ui;
        var pc_factory;
        function init(endpoint) {
            // rpc stuff
            rpc_factory = new RPCFactory_1["default"]();
            rpcclient = new RpcClient_1["default"](jquery, endpoint, rpc_factory);
            // operation stuff
            op_factory = new OperationFactory_1["default"]();
            op_queue = new OpQueue_1["default"]();
            op_rpc_gateway = new OperationRpcGateway_1["default"](rpcclient, rpc_factory, op_factory);
            // editor ui
            pc_factory = new PageContentFactory_1["default"]();
            ui = new UI_1["default"](jquery, pc_factory);
            // main controller
            controller = new Controller_1["default"](op_rpc_gateway, op_factory, op_queue, ui);
            controller.initEditor();
        }
        return {
            init: init
        };
    })($);
})($, il);
//# sourceMappingURL=Editor.js.map