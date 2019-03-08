"use strict";
exports.__esModule = true;
/**
 * Examples:
 *
 *
 * DeletePC pcid
 * MovePC (after) pcid, targetid
 * CreatePC (after) pctype, targetid, pcmodel
 * ModifyPC pcid, pctype, pcmodel
 * ...
 *
 */
var Operation = /** @class */ (function () {
    function Operation(type, pcid, targetid, pcmodel) {
        this.type = type;
        this.pcid = pcid;
        this.targetid = targetid;
        this.pcmodel = pcmodel;
    }
    return Operation;
}());
exports["default"] = Operation;
//# sourceMappingURL=Operation.js.map