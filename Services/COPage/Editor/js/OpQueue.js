"use strict";
exports.__esModule = true;
/**
 * Stores operations being done in the editor
 */
var OpQueue = /** @class */ (function () {
    function OpQueue() {
        this.operations = [];
    }
    /**
     * Push operation to queue
     * @param {Operation} op
     * @param {number} par
     */
    OpQueue.prototype.push = function (op, par) {
        this.operations.push(op);
    };
    /**
     * Pop operation from queue
     * @returns {Operation}
     */
    OpQueue.prototype.pop = function () {
        return this.operations.shift();
    };
    return OpQueue;
}());
exports["default"] = OpQueue;
//# sourceMappingURL=OpQueue.js.map