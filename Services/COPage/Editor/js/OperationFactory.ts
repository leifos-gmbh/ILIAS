import Operation from './Operation';
import OperationType from './OperationType';

/**
 * Operation factory
 */
export default class OperationFactory {

    constructor() {
    }

    /**
     *
     * @param {OperationType} type
     * @param {string} pcid
     * @param {string} targetid
     * @param {Object} pcmodel
     * @returns {Operation}
     */
    operation(type: OperationType, pcid: string, targetid: string, pcmodel: Object): Operation {
        return new Operation(type, pcid, targetid, pcmodel);
    }
}