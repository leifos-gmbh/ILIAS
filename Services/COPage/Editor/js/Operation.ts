import OperationType from './OperationType';

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
export default class Operation {
    type: OperationType;
    pcid: string;
    targetid: string;
    pcmodel: Object;

    constructor(type: OperationType, pcid: string, targetid: string, pcmodel: Object) {
        this.type = type;
        this.pcid = pcid;
        this.targetid = targetid;
        this.pcmodel = pcmodel;
    }
}