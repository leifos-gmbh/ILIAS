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
eval("\nexports.__esModule = true;\nvar OperationType_1 = __webpack_require__(/*! ./OperationType */ \"./js/OperationType.ts\");\n/**\n * Stores operations being done in the editor\n */\nvar Controller = /** @class */ (function () {\n    /**\n     *\n     * @param {OperationRpcGateway} operation_rpc_gateway\n     * @param {OperationFactory} op_factory\n     * @param {OpQueue} op_queue\n     * @param {UI} ui\n     */\n    function Controller(operation_rpc_gateway, op_factory, op_queue, ui) {\n        //this.rpcclient = rpcclient;\n        this.op_factory = op_factory;\n        this.op_queue = op_queue;\n        this.operation_rpc_gateway = operation_rpc_gateway;\n        this.ui = ui;\n    }\n    /**\n     *\n     */\n    Controller.prototype.initEditor = function () {\n        var ui;\n        ui = this.ui;\n        // get page html\n        this.pushOperation(OperationType_1[\"default\"].PageHtml, \"\", \"\", {});\n        this.pushOperation(OperationType_1[\"default\"].PageModel, \"\", \"\", {});\n        this.pushOperation(OperationType_1[\"default\"].UIAll, \"\", \"\", {});\n        this.sendOperations().then(function (r) {\n            ui.refreshPage();\n        });\n    };\n    /**\n     * Push an operation to the op queue\n     *\n     * @param {OperationType} type\n     * @param {string} pcid\n     * @param {string} targetid\n     * @param {Object} pcmodel\n     */\n    Controller.prototype.pushOperation = function (type, pcid, targetid, pcmodel) {\n        this.op_queue.push(this.op_factory.operation(type, pcid, targetid, pcmodel));\n    };\n    /**\n     * Send operations\n     */\n    Controller.prototype.sendOperations = function () {\n        var _this = this;\n        return new Promise(function (resolve, reject) {\n            var controller;\n            var i;\n            controller = _this;\n            _this.operation_rpc_gateway.sendQueue(_this.op_queue).then(function (r) {\n                if (Array.isArray(r)) {\n                    for (i = 0; i < r.length; ++i) {\n                        //@todo: handle is_error\n                        controller.processResponse(r[i].type, r[i].result);\n                    }\n                }\n                resolve();\n            })[\"catch\"](function (err) {\n                reject(err);\n            });\n        });\n    };\n    /**\n     *\n     * @param {string} type\n     * @param {Object} result\n     */\n    Controller.prototype.processResponse = function (type, result) {\n        console.log(\"processResponse\");\n        console.log(type);\n        console.log(result);\n        switch (type) {\n            case OperationType_1[\"default\"].PageModel:\n                this.handlePageModel(result);\n                break;\n            case OperationType_1[\"default\"].PageHtml:\n                this.handlePageHTML(result);\n                break;\n            case OperationType_1[\"default\"].UIAll:\n                this.handleUIAll(result);\n                break;\n        }\n    };\n    /**\n     *\n     * @param result\n     */\n    Controller.prototype.handlePageHTML = function (result) {\n        this.ui.setPageHtml(result);\n    };\n    /**\n     *\n     * @param result\n     */\n    Controller.prototype.handlePageModel = function (result) {\n        this.ui.setPageModel(result);\n    };\n    /**\n     *\n     * @param result\n     */\n    Controller.prototype.handleUIAll = function (result) {\n        this.ui.setUIComponentModel(result);\n    };\n    return Controller;\n}());\nexports[\"default\"] = Controller;\n\n\n//# sourceURL=webpack:///./js/Controller.ts?");

/***/ }),

/***/ "./js/Editor.ts":
/*!**********************!*\
  !*** ./js/Editor.ts ***!
  \**********************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\nvar RpcClient_1 = __webpack_require__(/*! ./RpcClient */ \"./js/RpcClient.ts\");\nvar Controller_1 = __webpack_require__(/*! ./Controller */ \"./js/Controller.ts\");\nvar OperationFactory_1 = __webpack_require__(/*! ./OperationFactory */ \"./js/OperationFactory.ts\");\nvar OpQueue_1 = __webpack_require__(/*! ./OpQueue */ \"./js/OpQueue.ts\");\nvar OperationRpcGateway_1 = __webpack_require__(/*! ./OperationRpcGateway */ \"./js/OperationRpcGateway.ts\");\nvar RPCFactory_1 = __webpack_require__(/*! ./RPCFactory */ \"./js/RPCFactory.ts\");\nvar UI_1 = __webpack_require__(/*! ./UI */ \"./js/UI.ts\");\nvar PageContentFactory_1 = __webpack_require__(/*! ./PageContent/PageContentFactory */ \"./js/PageContent/PageContentFactory.ts\");\nil = il || {};\nil.copg = il.copg || {};\n(function ($, il) {\n    il.copg.editor = (function ($) {\n        var jquery = $;\n        var rpcclient;\n        var controller;\n        var op_factory;\n        var op_queue;\n        var op_rpc_gateway;\n        var rpc_factory;\n        var ui;\n        var pc_factory;\n        function init(endpoint) {\n            // rpc stuff\n            rpc_factory = new RPCFactory_1[\"default\"]();\n            rpcclient = new RpcClient_1[\"default\"](jquery, endpoint, rpc_factory);\n            // operation stuff\n            op_factory = new OperationFactory_1[\"default\"]();\n            op_queue = new OpQueue_1[\"default\"]();\n            op_rpc_gateway = new OperationRpcGateway_1[\"default\"](rpcclient, rpc_factory, op_factory);\n            // editor ui\n            pc_factory = new PageContentFactory_1[\"default\"]();\n            ui = new UI_1[\"default\"](jquery, pc_factory);\n            // main controller\n            controller = new Controller_1[\"default\"](op_rpc_gateway, op_factory, op_queue, ui);\n            controller.initEditor();\n        }\n        return {\n            init: init\n        };\n    })($);\n})($, il);\n\n\n//# sourceURL=webpack:///./js/Editor.ts?");

/***/ }),

/***/ "./js/OpQueue.ts":
/*!***********************!*\
  !*** ./js/OpQueue.ts ***!
  \***********************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\n/**\n * Stores operations being done in the editor\n */\nvar OpQueue = /** @class */ (function () {\n    function OpQueue() {\n        this.operations = [];\n    }\n    /**\n     * Push operation to queue\n     * @param {Operation} op\n     * @param {number} par\n     */\n    OpQueue.prototype.push = function (op, par) {\n        this.operations.push(op);\n    };\n    /**\n     * Pop operation from queue\n     * @returns {Operation}\n     */\n    OpQueue.prototype.pop = function () {\n        return this.operations.shift();\n    };\n    return OpQueue;\n}());\nexports[\"default\"] = OpQueue;\n\n\n//# sourceURL=webpack:///./js/OpQueue.ts?");

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
eval("\nexports.__esModule = true;\nvar Operation_1 = __webpack_require__(/*! ./Operation */ \"./js/Operation.ts\");\nvar OperationResponse_1 = __webpack_require__(/*! ./OperationResponse */ \"./js/OperationResponse.ts\");\n/**\n * Operation factory\n */\nvar OperationFactory = /** @class */ (function () {\n    function OperationFactory() {\n    }\n    /**\n     *\n     * @param {OperationType} type\n     * @param {string} pcid\n     * @param {string} targetid\n     * @param {object} pcmodel\n     * @returns {Operation}\n     */\n    OperationFactory.prototype.operation = function (type, pcid, targetid, pcmodel) {\n        return new Operation_1[\"default\"](type, pcid, targetid, pcmodel);\n    };\n    /**\n     *\n     * @param {OperationType} type\n     * @param {boolean} is_error\n     * @param {object} result\n     * @returns {OperationResponse}\n     */\n    OperationFactory.prototype.operationResponse = function (type, is_error, result) {\n        return new OperationResponse_1[\"default\"](type, is_error, result);\n    };\n    return OperationFactory;\n}());\nexports[\"default\"] = OperationFactory;\n\n\n//# sourceURL=webpack:///./js/OperationFactory.ts?");

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

/***/ "./js/PageContent/PCParagraph.ts":
/*!***************************************!*\
  !*** ./js/PageContent/PCParagraph.ts ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\n/**\n * Paragraph Page Content Element\n */\nvar PCParagraph = /** @class */ (function () {\n    function PCParagraph() {\n    }\n    /**\n     * @inheritDoc\n     */\n    PCParagraph.prototype.initEditForm = function (pcid, model, $el, $form_canvas, model_updater) {\n        var $text_field = $form_canvas.find(\"#text\");\n        var $select_dropdown = $form_canvas.find(\"#characteristic\");\n        // init form\n        $text_field.val(model.text);\n        $form_canvas.find(\"#characteristic option[value='\" + model.characteristic + \"']\").attr('selected', true);\n        // handle updates\n        $text_field.attr(\"onkeyup\", null);\n        $text_field.on(\"input\", function () {\n            var text = $text_field.val();\n            $el.find(\".ilc_Paragraph\").html(text);\n            model.text = text;\n            model_updater(model);\n        });\n        $select_dropdown.on(\"input\", function () {\n            var characteristic = $select_dropdown.val();\n            $el.find(\".ilc_Paragraph\").removeClass().addClass(\"ilc_Paragraph\").addClass(\"ilc_text_block_\" + characteristic);\n            model.characteristic = characteristic;\n            model_updater(model);\n        });\n    };\n    /**\n     * @inheritDoc\n     */\n    PCParagraph.prototype.getNew = function () {\n        return {\n            model: {\n                text: '',\n                characteristic: 'Standard'\n            },\n            html: '<div class=\"ilc_Paragraph ilc_text_block_Standard\"></div>'\n        };\n    };\n    return PCParagraph;\n}());\nexports[\"default\"] = PCParagraph;\n\n\n//# sourceURL=webpack:///./js/PageContent/PCParagraph.ts?");

/***/ }),

/***/ "./js/PageContent/PageContentFactory.ts":
/*!**********************************************!*\
  !*** ./js/PageContent/PageContentFactory.ts ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\nvar PCParagraph_1 = __webpack_require__(/*! ./PCParagraph */ \"./js/PageContent/PCParagraph.ts\");\n/**\n * Operation factory\n */\nvar PageContentFactory = /** @class */ (function () {\n    function PageContentFactory() {\n    }\n    /**\n     * Get page content object\n     * @param {string} type\n     * @returns {PageContentInterface}\n     */\n    PageContentFactory.prototype.pageContent = function (type) {\n        switch (type) {\n            case \"par\":\n                return new PCParagraph_1[\"default\"]();\n                break;\n        }\n    };\n    return PageContentFactory;\n}());\nexports[\"default\"] = PageContentFactory;\n\n\n//# sourceURL=webpack:///./js/PageContent/PageContentFactory.ts?");

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
eval("\nexports.__esModule = true;\nvar RpcClient = /** @class */ (function () {\n    /**\n     * Constructor\n     * @param {JQueryStatic} jquery\n     * @param {string} endpoint\n     * @param {RPCFactory} rpc_factory\n     */\n    function RpcClient(jquery, endpoint, rpc_factory) {\n        this.endpoint = endpoint;\n        this.jquery = jquery;\n        this.rpc_factory = rpc_factory;\n        this.queries = [];\n    }\n    RpcClient.prototype.addQuery = function (rpc_query) {\n        this.queries.push(rpc_query);\n    };\n    /**\n     * Send rp call\n     * @returns {Promise<any>}\n     */\n    RpcClient.prototype.send = function () {\n        var _this = this;\n        var packaged_queries = [];\n        var query;\n        var query_map;\n        var responses;\n        var rpc_factory;\n        return new Promise(function (resolve, reject) {\n            query_map = new Map();\n            while (query = _this.queries.shift()) {\n                packaged_queries.push({\n                    jsonrpc: '2.0',\n                    method: query.method,\n                    id: query.id,\n                    params: query.params\n                });\n                query_map.set(query.id, query);\n            }\n            rpc_factory = _this.rpc_factory;\n            _this.jquery.ajax({\n                url: _this.endpoint,\n                data: JSON.stringify(packaged_queries),\n                type: \"POST\",\n                dataType: \"json\",\n                success: function (r) {\n                    var i;\n                    // transform response into RPCResponse array\n                    responses = [];\n                    if (Array.isArray(r)) {\n                        for (i = 0; i < r.length; ++i) {\n                            if (query = query_map.get(r[i].id)) {\n                                responses.push(rpc_factory.response(query, false, r[i].result));\n                            }\n                        }\n                    }\n                    resolve(responses);\n                },\n                error: function (err) {\n                    reject(err);\n                }\n            });\n        });\n    };\n    return RpcClient;\n}());\nexports[\"default\"] = RpcClient;\n\n\n//# sourceURL=webpack:///./js/RpcClient.ts?");

/***/ }),

/***/ "./js/UI.ts":
/*!******************!*\
  !*** ./js/UI.ts ***!
  \******************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nexports.__esModule = true;\nvar uuid = __webpack_require__(/*! uuid */ \"./node_modules/uuid/index.js\");\nvar UI = /** @class */ (function () {\n    /**\n     *\n     * @param {JQueryStatic} jquery\n     */\n    function UI(jquery, pc_factory) {\n        this.canvas_id = \"#copg-editor-canvas\";\n        this.slate_id = \"#copg-editor-slate\";\n        this.add_id = \"copg-add\";\n        this.pc_components_path = \"#copg-editor-canvas .il_editarea\";\n        this.jquery = jquery;\n        this.pc_factory = pc_factory;\n    }\n    UI.prototype.replacePageCanvas = function (html) {\n        this.jquery(this.canvas_id).html(html);\n    };\n    UI.prototype.replacePageSlate = function (html) {\n        this.jquery(this.slate_id).html(html);\n    };\n    UI.prototype.setUIComponentModel = function (model) {\n        this.ui_model = JSON.parse(model);\n    };\n    UI.prototype.setPageHtml = function (page_html) {\n        this.page_html = page_html;\n    };\n    UI.prototype.setPageModel = function (page_model) {\n        this.page_model = JSON.parse(page_model);\n        console.log(\"setPageModel\");\n        console.log(this.page_model);\n        this.page_model_map = this.page_model.reduce(function (map, obj) {\n            map[obj.pc_id] = obj;\n            return map;\n        }, {});\n        console.log(this.page_model_map);\n    };\n    UI.prototype.refreshPage = function () {\n        this.replacePageCanvas(this.page_html);\n        this.fixIds();\n        this.addAddButton();\n        this.addEvents();\n    };\n    UI.prototype.addAddButton = function () {\n        this.jquery(this.canvas_id).append(\"<div id='\" + this.add_id + \"'>\" + this.ui_model.dropdowns.add + \"</div>\");\n    };\n    UI.prototype.fixIds = function () {\n        var ui = this;\n        this.jquery(this.pc_components_path).each(function () {\n            var id = this.id.split(\":\");\n            ui.jquery(this).attr(\"id\", id[1]);\n        });\n    };\n    /**\n     * Add events for page editing\n     */\n    UI.prototype.addEvents = function () {\n        var $ = this.jquery;\n        var ui = this;\n        // components: click\n        $(this.pc_components_path).on(\"click\", function (event) {\n            ui.handleComponentClick(event, this);\n        });\n        // add dropdown: click\n        $(\"#\" + this.add_id + \" button\").on(\"click\", function (event) {\n            ui.handleAddClick(event, this);\n        });\n    };\n    /**\n     * Add new page component\n     * @param ui\n     * @param event\n     * @param el\n     */\n    UI.prototype.handleAddClick = function (event, el) {\n        var action = $(el).data(\"action\");\n        var pctype;\n        var new_pc_id;\n        var new_pc = {};\n        var ui = this;\n        if (action) {\n            pctype = action.substring(1);\n            var pc = this.pc_factory.pageContent(pctype);\n            new_pc = pc.getNew();\n            new_pc_id = uuid.v1();\n            this.updatePCModel(new_pc_id, new_pc.model);\n            this.page_model_map[new_pc_id].pc_type = pctype; // @todo: clean this up\n            this.jquery(\"#\" + this.add_id).before(\"<div class='il_editarea' id='\" + new_pc_id + \"'>\" + new_pc.html + \"</div>\");\n            this.jquery(\"#\" + new_pc_id).on(\"click\", function (event) {\n                ui.handleComponentClick(event, this);\n            });\n            this.jquery(\"#\" + new_pc_id).trigger(\"click\");\n        }\n    };\n    /**\n     * Handle click on pc component\n     *\n     * @param event\n     * @param el\n     */\n    UI.prototype.handleComponentClick = function (event, el) {\n        var pcid = this.extractPCIdFromPCComponentDomId(el.id);\n        console.log(pcid);\n        console.log(this.page_model_map[pcid]);\n        this.loadEditFormForPageContent(el, pcid);\n    };\n    /**\n     * Extract pc id from dom id\n     *\n     * @param {string} domid\n     * @returns {string}\n     */\n    UI.prototype.extractPCIdFromPCComponentDomId = function (domid) {\n        return domid;\n        // not needed after fix ids\n        //return (domid.split(\":\"))[1];\n    };\n    /**\n     * Load edit form\n     * @param el\n     * @param pcid\n     */\n    UI.prototype.loadEditFormForPageContent = function (el, pcid) {\n        var ui = this;\n        var pctype = this.getPCType(pcid);\n        var pc = this.pc_factory.pageContent(pctype);\n        this.replacePageSlate(this.ui_model.forms[pctype].edit);\n        pc.initEditForm(pcid, this.getPCModel(pcid), this.jquery(el), this.jquery(this.slate_id), function (model) {\n            ui.updatePCModel(pcid, model);\n        });\n    };\n    /**\n     *\n     * @param {string} pcid\n     * @param model\n     */\n    UI.prototype.updatePCModel = function (pcid, model) {\n        if (!this.page_model_map[pcid]) {\n            this.page_model_map[pcid] = {};\n        }\n        this.page_model_map[pcid].pc_model = model;\n    };\n    /**\n     * Get pc type for pc id\n     *\n     * @param {string} pcid\n     * @returns {string}\n     */\n    UI.prototype.getPCType = function (pcid) {\n        return this.page_model_map[pcid].pc_type;\n    };\n    /**\n     * Get pc type for pc id\n     *\n     * @param {string} pcid\n     * @returns {string}\n     */\n    UI.prototype.getPCModel = function (pcid) {\n        return this.page_model_map[pcid].pc_model;\n    };\n    return UI;\n}());\nexports[\"default\"] = UI;\n\n\n//# sourceURL=webpack:///./js/UI.ts?");

/***/ }),

/***/ "./node_modules/uuid/index.js":
/*!************************************!*\
  !*** ./node_modules/uuid/index.js ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var v1 = __webpack_require__(/*! ./v1 */ \"./node_modules/uuid/v1.js\");\nvar v4 = __webpack_require__(/*! ./v4 */ \"./node_modules/uuid/v4.js\");\n\nvar uuid = v4;\nuuid.v1 = v1;\nuuid.v4 = v4;\n\nmodule.exports = uuid;\n\n\n//# sourceURL=webpack:///./node_modules/uuid/index.js?");

/***/ }),

/***/ "./node_modules/uuid/lib/bytesToUuid.js":
/*!**********************************************!*\
  !*** ./node_modules/uuid/lib/bytesToUuid.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("/**\n * Convert array of 16 byte values to UUID string format of the form:\n * XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX\n */\nvar byteToHex = [];\nfor (var i = 0; i < 256; ++i) {\n  byteToHex[i] = (i + 0x100).toString(16).substr(1);\n}\n\nfunction bytesToUuid(buf, offset) {\n  var i = offset || 0;\n  var bth = byteToHex;\n  // join used to fix memory issue caused by concatenation: https://bugs.chromium.org/p/v8/issues/detail?id=3175#c4\n  return ([bth[buf[i++]], bth[buf[i++]], \n\tbth[buf[i++]], bth[buf[i++]], '-',\n\tbth[buf[i++]], bth[buf[i++]], '-',\n\tbth[buf[i++]], bth[buf[i++]], '-',\n\tbth[buf[i++]], bth[buf[i++]], '-',\n\tbth[buf[i++]], bth[buf[i++]],\n\tbth[buf[i++]], bth[buf[i++]],\n\tbth[buf[i++]], bth[buf[i++]]]).join('');\n}\n\nmodule.exports = bytesToUuid;\n\n\n//# sourceURL=webpack:///./node_modules/uuid/lib/bytesToUuid.js?");

/***/ }),

/***/ "./node_modules/uuid/lib/rng-browser.js":
/*!**********************************************!*\
  !*** ./node_modules/uuid/lib/rng-browser.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("// Unique ID creation requires a high quality random # generator.  In the\n// browser this is a little complicated due to unknown quality of Math.random()\n// and inconsistent support for the `crypto` API.  We do the best we can via\n// feature-detection\n\n// getRandomValues needs to be invoked in a context where \"this\" is a Crypto\n// implementation. Also, find the complete implementation of crypto on IE11.\nvar getRandomValues = (typeof(crypto) != 'undefined' && crypto.getRandomValues && crypto.getRandomValues.bind(crypto)) ||\n                      (typeof(msCrypto) != 'undefined' && typeof window.msCrypto.getRandomValues == 'function' && msCrypto.getRandomValues.bind(msCrypto));\n\nif (getRandomValues) {\n  // WHATWG crypto RNG - http://wiki.whatwg.org/wiki/Crypto\n  var rnds8 = new Uint8Array(16); // eslint-disable-line no-undef\n\n  module.exports = function whatwgRNG() {\n    getRandomValues(rnds8);\n    return rnds8;\n  };\n} else {\n  // Math.random()-based (RNG)\n  //\n  // If all else fails, use Math.random().  It's fast, but is of unspecified\n  // quality.\n  var rnds = new Array(16);\n\n  module.exports = function mathRNG() {\n    for (var i = 0, r; i < 16; i++) {\n      if ((i & 0x03) === 0) r = Math.random() * 0x100000000;\n      rnds[i] = r >>> ((i & 0x03) << 3) & 0xff;\n    }\n\n    return rnds;\n  };\n}\n\n\n//# sourceURL=webpack:///./node_modules/uuid/lib/rng-browser.js?");

/***/ }),

/***/ "./node_modules/uuid/v1.js":
/*!*********************************!*\
  !*** ./node_modules/uuid/v1.js ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var rng = __webpack_require__(/*! ./lib/rng */ \"./node_modules/uuid/lib/rng-browser.js\");\nvar bytesToUuid = __webpack_require__(/*! ./lib/bytesToUuid */ \"./node_modules/uuid/lib/bytesToUuid.js\");\n\n// **`v1()` - Generate time-based UUID**\n//\n// Inspired by https://github.com/LiosK/UUID.js\n// and http://docs.python.org/library/uuid.html\n\nvar _nodeId;\nvar _clockseq;\n\n// Previous uuid creation time\nvar _lastMSecs = 0;\nvar _lastNSecs = 0;\n\n// See https://github.com/broofa/node-uuid for API details\nfunction v1(options, buf, offset) {\n  var i = buf && offset || 0;\n  var b = buf || [];\n\n  options = options || {};\n  var node = options.node || _nodeId;\n  var clockseq = options.clockseq !== undefined ? options.clockseq : _clockseq;\n\n  // node and clockseq need to be initialized to random values if they're not\n  // specified.  We do this lazily to minimize issues related to insufficient\n  // system entropy.  See #189\n  if (node == null || clockseq == null) {\n    var seedBytes = rng();\n    if (node == null) {\n      // Per 4.5, create and 48-bit node id, (47 random bits + multicast bit = 1)\n      node = _nodeId = [\n        seedBytes[0] | 0x01,\n        seedBytes[1], seedBytes[2], seedBytes[3], seedBytes[4], seedBytes[5]\n      ];\n    }\n    if (clockseq == null) {\n      // Per 4.2.2, randomize (14 bit) clockseq\n      clockseq = _clockseq = (seedBytes[6] << 8 | seedBytes[7]) & 0x3fff;\n    }\n  }\n\n  // UUID timestamps are 100 nano-second units since the Gregorian epoch,\n  // (1582-10-15 00:00).  JSNumbers aren't precise enough for this, so\n  // time is handled internally as 'msecs' (integer milliseconds) and 'nsecs'\n  // (100-nanoseconds offset from msecs) since unix epoch, 1970-01-01 00:00.\n  var msecs = options.msecs !== undefined ? options.msecs : new Date().getTime();\n\n  // Per 4.2.1.2, use count of uuid's generated during the current clock\n  // cycle to simulate higher resolution clock\n  var nsecs = options.nsecs !== undefined ? options.nsecs : _lastNSecs + 1;\n\n  // Time since last uuid creation (in msecs)\n  var dt = (msecs - _lastMSecs) + (nsecs - _lastNSecs)/10000;\n\n  // Per 4.2.1.2, Bump clockseq on clock regression\n  if (dt < 0 && options.clockseq === undefined) {\n    clockseq = clockseq + 1 & 0x3fff;\n  }\n\n  // Reset nsecs if clock regresses (new clockseq) or we've moved onto a new\n  // time interval\n  if ((dt < 0 || msecs > _lastMSecs) && options.nsecs === undefined) {\n    nsecs = 0;\n  }\n\n  // Per 4.2.1.2 Throw error if too many uuids are requested\n  if (nsecs >= 10000) {\n    throw new Error('uuid.v1(): Can\\'t create more than 10M uuids/sec');\n  }\n\n  _lastMSecs = msecs;\n  _lastNSecs = nsecs;\n  _clockseq = clockseq;\n\n  // Per 4.1.4 - Convert from unix epoch to Gregorian epoch\n  msecs += 12219292800000;\n\n  // `time_low`\n  var tl = ((msecs & 0xfffffff) * 10000 + nsecs) % 0x100000000;\n  b[i++] = tl >>> 24 & 0xff;\n  b[i++] = tl >>> 16 & 0xff;\n  b[i++] = tl >>> 8 & 0xff;\n  b[i++] = tl & 0xff;\n\n  // `time_mid`\n  var tmh = (msecs / 0x100000000 * 10000) & 0xfffffff;\n  b[i++] = tmh >>> 8 & 0xff;\n  b[i++] = tmh & 0xff;\n\n  // `time_high_and_version`\n  b[i++] = tmh >>> 24 & 0xf | 0x10; // include version\n  b[i++] = tmh >>> 16 & 0xff;\n\n  // `clock_seq_hi_and_reserved` (Per 4.2.2 - include variant)\n  b[i++] = clockseq >>> 8 | 0x80;\n\n  // `clock_seq_low`\n  b[i++] = clockseq & 0xff;\n\n  // `node`\n  for (var n = 0; n < 6; ++n) {\n    b[i + n] = node[n];\n  }\n\n  return buf ? buf : bytesToUuid(b);\n}\n\nmodule.exports = v1;\n\n\n//# sourceURL=webpack:///./node_modules/uuid/v1.js?");

/***/ }),

/***/ "./node_modules/uuid/v4.js":
/*!*********************************!*\
  !*** ./node_modules/uuid/v4.js ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var rng = __webpack_require__(/*! ./lib/rng */ \"./node_modules/uuid/lib/rng-browser.js\");\nvar bytesToUuid = __webpack_require__(/*! ./lib/bytesToUuid */ \"./node_modules/uuid/lib/bytesToUuid.js\");\n\nfunction v4(options, buf, offset) {\n  var i = buf && offset || 0;\n\n  if (typeof(options) == 'string') {\n    buf = options === 'binary' ? new Array(16) : null;\n    options = null;\n  }\n  options = options || {};\n\n  var rnds = options.random || (options.rng || rng)();\n\n  // Per 4.4, set bits for version and `clock_seq_hi_and_reserved`\n  rnds[6] = (rnds[6] & 0x0f) | 0x40;\n  rnds[8] = (rnds[8] & 0x3f) | 0x80;\n\n  // Copy bytes to buffer, if provided\n  if (buf) {\n    for (var ii = 0; ii < 16; ++ii) {\n      buf[i + ii] = rnds[ii];\n    }\n  }\n\n  return buf || bytesToUuid(rnds);\n}\n\nmodule.exports = v4;\n\n\n//# sourceURL=webpack:///./node_modules/uuid/v4.js?");

/***/ })

/******/ });