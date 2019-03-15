/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./js/Editor.ts");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./js/Controller.ts":
/*!**************************!*\
  !*** ./js/Controller.ts ***!
  \**************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\nvar OperationType_1 = __webpack_require__(/*! ./OperationType */ \"./js/OperationType.ts\");\n/**\n * Stores operations being done in the editor\n */\nvar Controller = /** @class */ (function () {\n    /**\n     *\n     * @param {OperationRpcGateway} operation_rpc_gateway\n     * @param {OperationFactory} op_factory\n     * @param {OpQueue} op_queue\n     * @param {UI} ui\n     */\n    function Controller(operation_rpc_gateway, op_factory, op_queue, ui) {\n        //this.rpcclient = rpcclient;\n        this.op_factory = op_factory;\n        this.op_queue = op_queue;\n        this.operation_rpc_gateway = operation_rpc_gateway;\n        this.ui = ui;\n    }\n    /**\n     *\n     */\n    Controller.prototype.initEditor = function () {\n        var ui;\n        ui = this.ui;\n        // get page html\n        this.pushOperation(OperationType_1[\"default\"].PageHtml, \"\", \"\", {});\n        this.pushOperation(OperationType_1[\"default\"].PageModel, \"\", \"\", {});\n        this.pushOperation(OperationType_1[\"default\"].UIAll, \"\", \"\", {});\n        this.sendOperations().then(function (r) {\n            ui.refreshPage();\n        });\n    };\n    /**\n     * Push an operation to the op queue\n     *\n     * @param {OperationType} type\n     * @param {string} pcid\n     * @param {string} targetid\n     * @param {Object} pcmodel\n     */\n    Controller.prototype.pushOperation = function (type, pcid, targetid, pcmodel) {\n        this.op_queue.push(this.op_factory.operation(type, pcid, targetid, pcmodel));\n    };\n    /**\n     * Send operations\n     */\n    Controller.prototype.sendOperations = function () {\n        var _this = this;\n        return new Promise(function (resolve, reject) {\n            var controller;\n            var i;\n            controller = _this;\n            _this.operation_rpc_gateway.sendQueue(_this.op_queue).then(function (r) {\n                if (Array.isArray(r)) {\n                    for (i = 0; i < r.length; ++i) {\n                        //@todo: handle is_error\n                        controller.processResponse(r[i].type, r[i].result);\n                    }\n                }\n                resolve();\n            })[\"catch\"](function (err) {\n                reject(err);\n            });\n        });\n    };\n    /**\n     *\n     * @param {string} type\n     * @param {Object} result\n     */\n    Controller.prototype.processResponse = function (type, result) {\n        console.log(\"processResponse\");\n        console.log(type);\n        console.log(result);\n        switch (type) {\n            case OperationType_1[\"default\"].PageHtml:\n                this.handlePageHTML(result);\n                break;\n            case OperationType_1[\"default\"].UIAll:\n                this.handleUIAll(result);\n                break;\n        }\n    };\n    /**\n     *\n     * @param result\n     */\n    Controller.prototype.handlePageHTML = function (result) {\n        this.ui.setPageHtml(result);\n    };\n    /**\n     *\n     * @param result\n     */\n    Controller.prototype.handleUIAll = function (result) {\n        this.ui.setUIComponentModel(result);\n    };\n    return Controller;\n}());\nexports[\"default\"] = Controller;\n\n\n//# sourceURL=webpack:///./js/Controller.ts?");

/***/ }),

/***/ "./js/Editor.ts":
/*!**********************!*\
  !*** ./js/Editor.ts ***!
  \**********************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\nvar RpcClient_1 = __webpack_require__(/*! ./RpcClient */ \"./js/RpcClient.ts\");\nvar Controller_1 = __webpack_require__(/*! ./Controller */ \"./js/Controller.ts\");\nvar OperationFactory_1 = __webpack_require__(/*! ./OperationFactory */ \"./js/OperationFactory.ts\");\nvar OpQueue_1 = __webpack_require__(/*! ./OpQueue */ \"./js/OpQueue.ts\");\nvar OperationRpcGateway_1 = __webpack_require__(/*! ./OperationRpcGateway */ \"./js/OperationRpcGateway.ts\");\nvar RPCFactory_1 = __webpack_require__(/*! ./RPCFactory */ \"./js/RPCFactory.ts\");\nvar UI_1 = __webpack_require__(/*! ./UI */ \"./js/UI.ts\");\nil = il || {};\nil.copg = il.copg || {};\n(function ($, il) {\n    il.copg.editor = (function ($) {\n        var jquery = $;\n        var rpcclient;\n        var controller;\n        var op_factory;\n        var op_queue;\n        var op_rpc_gateway;\n        var rpc_factory;\n        var ui;\n        function init(endpoint) {\n            // rpc stuff\n            rpc_factory = new RPCFactory_1[\"default\"]();\n            rpcclient = new RpcClient_1[\"default\"](jquery, endpoint, rpc_factory);\n            // operation stuff\n            op_factory = new OperationFactory_1[\"default\"]();\n            op_queue = new OpQueue_1[\"default\"]();\n            op_rpc_gateway = new OperationRpcGateway_1[\"default\"](rpcclient, rpc_factory, op_factory);\n            // editor ui\n            ui = new UI_1[\"default\"](jquery);\n            // main controller\n            controller = new Controller_1[\"default\"](op_rpc_gateway, op_factory, op_queue, ui);\n            controller.initEditor();\n        }\n        return {\n            init: init\n        };\n    })($);\n})($, il);\n\n\n//# sourceURL=webpack:///./js/Editor.ts?");

/***/ }),

/***/ "./js/OpQueue.ts":
/*!***********************!*\
  !*** ./js/OpQueue.ts ***!
  \***********************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\n/**\n * Stores operations being done in the editor\n */\nvar OpQueue = /** @class */ (function () {\n    function OpQueue() {\n        this.operations = [];\n    }\n    /**\n     * Push operation to queue\n     * @param {Operation} op\n     * @param {number} par\n     */\n    OpQueue.prototype.push = function (op, par) {\n        console.log(\"OpQueue push called.\");\n        console.log(op);\n        this.operations.push(op);\n    };\n    /**\n     * Pop operation from queue\n     * @returns {Operation}\n     */\n    OpQueue.prototype.pop = function () {\n        return this.operations.shift();\n    };\n    return OpQueue;\n}());\nexports[\"default\"] = OpQueue;\n\n\n//# sourceURL=webpack:///./js/OpQueue.ts?");

/***/ }),

/***/ "./js/Operation.ts":
/*!*************************!*\
  !*** ./js/Operation.ts ***!
  \*************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\n/**\n * Examples:\n *\n *\n * DeletePC pcid\n * MovePC (after) pcid, targetid\n * CreatePC (after) pctype, targetid, pcmodel\n * ModifyPC pcid, pctype, pcmodel\n * ...\n *\n */\nvar Operation = /** @class */ (function () {\n    function Operation(type, pcid, targetid, pcmodel) {\n        this.type = type;\n        this.pcid = pcid;\n        this.targetid = targetid;\n        this.pcmodel = pcmodel;\n    }\n    return Operation;\n}());\nexports[\"default\"] = Operation;\n\n\n//# sourceURL=webpack:///./js/Operation.ts?");

/***/ }),

/***/ "./js/OperationFactory.ts":
/*!********************************!*\
  !*** ./js/OperationFactory.ts ***!
  \********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\nvar Operation_1 = __webpack_require__(/*! ./Operation */ \"./js/Operation.ts\");\nvar OperationResponse_1 = __webpack_require__(/*! ./OperationResponse */ \"./js/OperationResponse.ts\");\n/**\n * Operation factory\n */\nvar OperationFactory = /** @class */ (function () {\n    function OperationFactory() {\n    }\n    /**\n     *\n     * @param {OperationType} type\n     * @param {string} pcid\n     * @param {string} targetid\n     * @param {Object} pcmodel\n     * @returns {Operation}\n     */\n    OperationFactory.prototype.operation = function (type, pcid, targetid, pcmodel) {\n        return new Operation_1[\"default\"](type, pcid, targetid, pcmodel);\n    };\n    /**\n     *\n     * @param {OperationType} type\n     * @param {boolean} is_error\n     * @param {Object} result\n     * @returns {OperationResponse}\n     */\n    OperationFactory.prototype.operationResponse = function (type, is_error, result) {\n        return new OperationResponse_1[\"default\"](type, is_error, result);\n    };\n    return OperationFactory;\n}());\nexports[\"default\"] = OperationFactory;\n\n\n//# sourceURL=webpack:///./js/OperationFactory.ts?");

/***/ }),

/***/ "./js/OperationResponse.ts":
/*!*********************************!*\
  !*** ./js/OperationResponse.ts ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\n/**\n * Operation Response\n */\nvar OperationResponse = /** @class */ (function () {\n    function OperationResponse(type, is_error, result) {\n        this.type = type;\n        this.is_error = is_error;\n        this.result = result;\n    }\n    return OperationResponse;\n}());\nexports[\"default\"] = OperationResponse;\n\n\n//# sourceURL=webpack:///./js/OperationResponse.ts?");

/***/ }),

/***/ "./js/OperationRpcGateway.ts":
/*!***********************************!*\
  !*** ./js/OperationRpcGateway.ts ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\nvar OperationRpcGateway = /** @class */ (function () {\n    function OperationRpcGateway(rpc_client, rpc_factory, op_factory) {\n        this.rpc_client = rpc_client;\n        this.rpc_factory = rpc_factory;\n        this.op_factory = op_factory;\n    }\n    OperationRpcGateway.prototype.sendQueue = function (queue) {\n        var _this = this;\n        return new Promise(function (resolve, reject) {\n            var op;\n            var op_factory;\n            op_factory = _this.op_factory;\n            while (op = queue.pop()) {\n                _this.rpc_client.addQuery(_this.rpc_factory.query(op.type, {\n                    pcid: op.pcid,\n                    targetid: op.targetid,\n                    model: op.pcmodel\n                }));\n            }\n            _this.rpc_client.send()\n                .then(function (r) {\n                var i;\n                var op_responses;\n                op_responses = [];\n                if (Array.isArray(r)) {\n                    for (i = 0; i < r.length; ++i) {\n                        console.log(r[i]);\n                        op_responses.push(op_factory.operationResponse(r[i].query.method, r[i].is_error, r[i].response));\n                    }\n                }\n                resolve(op_responses);\n            })[\"catch\"](function (err) {\n                //@todo: transform error into Operation Response Object\n                reject(err);\n            });\n        });\n    };\n    return OperationRpcGateway;\n}());\nexports[\"default\"] = OperationRpcGateway;\n\n\n//# sourceURL=webpack:///./js/OperationRpcGateway.ts?");

/***/ }),

/***/ "./js/OperationType.ts":
/*!*****************************!*\
  !*** ./js/OperationType.ts ***!
  \*****************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\n/**\n * Operation types for the Operation class\n */\nvar OperationType;\n(function (OperationType) {\n    OperationType[\"DeletePC\"] = \"pc/delete\";\n    OperationType[\"CreatePC\"] = \"pc/create\";\n    OperationType[\"ModifyPC\"] = \"pc/modify\";\n    OperationType[\"MovePC\"] = \"pc/move\";\n    OperationType[\"PageHtml\"] = \"page/html\";\n    OperationType[\"PageModel\"] = \"page/model\";\n    OperationType[\"UIButtons\"] = \"ui/buttons\";\n    OperationType[\"UIForms\"] = \"ui/forms\";\n    OperationType[\"UIDropdowns\"] = \"ui/dropdowns\";\n    OperationType[\"UIAll\"] = \"ui/all\";\n})(OperationType || (OperationType = {}));\nexports[\"default\"] = OperationType;\n\n\n//# sourceURL=webpack:///./js/OperationType.ts?");

/***/ }),

/***/ "./js/RPCFactory.ts":
/*!**************************!*\
  !*** ./js/RPCFactory.ts ***!
  \**************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\nvar RPCQuery_1 = __webpack_require__(/*! ./RPCQuery */ \"./js/RPCQuery.ts\");\nvar RPCResponse_1 = __webpack_require__(/*! ./RPCResponse */ \"./js/RPCResponse.ts\");\n/**\n * RPC query factory\n */\nvar RPCFactory = /** @class */ (function () {\n    function RPCFactory() {\n        this.id = 0;\n    }\n    /**\n     *\n     * @param {string} method\n     * @param {Object} params\n     * @returns {RPCQuery}\n     */\n    RPCFactory.prototype.query = function (method, params) {\n        this.id++;\n        return new RPCQuery_1[\"default\"](method, params, this.id);\n    };\n    /**\n     *\n     * @param {RPCQuery} query\n     * @param {boolean} is_error\n     * @param {Object} response\n     * @returns {RPCResponse}\n     */\n    RPCFactory.prototype.response = function (query, is_error, response) {\n        return new RPCResponse_1[\"default\"](query, is_error, response);\n    };\n    return RPCFactory;\n}());\nexports[\"default\"] = RPCFactory;\n\n\n//# sourceURL=webpack:///./js/RPCFactory.ts?");

/***/ }),

/***/ "./js/RPCQuery.ts":
/*!************************!*\
  !*** ./js/RPCQuery.ts ***!
  \************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\nvar RPCQuery = /** @class */ (function () {\n    function RPCQuery(method, params, id) {\n        this.method = method;\n        this.params = params;\n        this.id = id;\n    }\n    return RPCQuery;\n}());\nexports[\"default\"] = RPCQuery;\n\n\n//# sourceURL=webpack:///./js/RPCQuery.ts?");

/***/ }),

/***/ "./js/RPCResponse.ts":
/*!***************************!*\
  !*** ./js/RPCResponse.ts ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\nvar RPCResponse = /** @class */ (function () {\n    function RPCResponse(query, is_error, response) {\n        this.query = query;\n        this.is_error = is_error;\n        this.response = response;\n    }\n    return RPCResponse;\n}());\nexports[\"default\"] = RPCResponse;\n\n\n//# sourceURL=webpack:///./js/RPCResponse.ts?");

/***/ }),

/***/ "./js/RpcClient.ts":
/*!*************************!*\
  !*** ./js/RpcClient.ts ***!
  \*************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\nvar RpcClient = /** @class */ (function () {\n    /**\n     * Constructor\n     * @param {JQueryStatic} jquery\n     * @param {string} endpoint\n     * @param {RPCFactory} rpc_factory\n     */\n    function RpcClient(jquery, endpoint, rpc_factory) {\n        this.endpoint = endpoint;\n        this.jquery = jquery;\n        this.rpc_factory = rpc_factory;\n        this.queries = [];\n    }\n    RpcClient.prototype.addQuery = function (rpc_query) {\n        this.queries.push(rpc_query);\n    };\n    /**\n     * Send rp call\n     * @returns {Promise<any>}\n     */\n    RpcClient.prototype.send = function () {\n        var _this = this;\n        var packaged_queries = [];\n        var query;\n        var query_map;\n        var responses;\n        var rpc_factory;\n        return new Promise(function (resolve, reject) {\n            query_map = new Map();\n            while (query = _this.queries.shift()) {\n                packaged_queries.push({\n                    jsonrpc: '2.0',\n                    method: query.method,\n                    id: query.id,\n                    params: query.params\n                });\n                query_map.set(query.id, query);\n            }\n            rpc_factory = _this.rpc_factory;\n            _this.jquery.ajax({\n                url: _this.endpoint,\n                data: JSON.stringify(packaged_queries),\n                type: \"POST\",\n                dataType: \"json\",\n                success: function (r) {\n                    var i;\n                    // transform response into RPCResponse array\n                    responses = [];\n                    if (Array.isArray(r)) {\n                        for (i = 0; i < r.length; ++i) {\n                            console.log(r[i]);\n                            if (query = query_map.get(r[i].id)) {\n                                responses.push(rpc_factory.response(query, false, r[i].result));\n                            }\n                        }\n                    }\n                    resolve(responses);\n                },\n                error: function (err) {\n                    reject(err);\n                }\n            });\n            /* this.jquery.ajax({\n                url: this.endpoint,\n                data: JSON.stringify ({\n                    jsonrpc: '2.0',\n                    method: method,\n                    params: params,\n                    id: Date.now()\n                }),\n                type: \"POST\",\n                dataType: \"json\",\n                success: function(r) {\n                    resolve(r);\n                },\n                error: function (err) {\n                    reject(err);\n                }\n            });*/\n        });\n    };\n    return RpcClient;\n}());\nexports[\"default\"] = RpcClient;\n\n\n//# sourceURL=webpack:///./js/RpcClient.ts?");

/***/ }),

/***/ "./js/UI.ts":
/*!******************!*\
  !*** ./js/UI.ts ***!
  \******************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\n/// <reference path=\"../typings/JQueryStatic.d.ts\" />\nexports.__esModule = true;\nvar UI = /** @class */ (function () {\n    /**\n     *\n     * @param {JQueryStatic} jquery\n     */\n    function UI(jquery) {\n        this.jquery = jquery;\n    }\n    UI.prototype.replacePageCanvas = function (html) {\n        this.jquery(\"#copg-editor-canvas\").html(html);\n    };\n    UI.prototype.replacePageSlate = function (html) {\n        this.jquery(\"#copg-editor-slate\").html(html);\n    };\n    UI.prototype.setUIComponentModel = function (model) {\n        this.ui_model = model;\n    };\n    UI.prototype.setPageHtml = function (page_html) {\n        this.page_html = page_html;\n    };\n    UI.prototype.setPageModel = function (page_model) {\n        this.page_model = page_model;\n    };\n    UI.prototype.refreshPage = function () {\n        this.replacePageCanvas(this.page_html);\n    };\n    return UI;\n}());\nexports[\"default\"] = UI;\n\n\n//# sourceURL=webpack:///./js/UI.ts?");

/***/ })

/******/ });