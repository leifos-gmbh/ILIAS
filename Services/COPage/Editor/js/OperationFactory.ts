import Operation from './Operation';
import OperationType from './OperationType';
import OperationResponse from "./OperationResponse";

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

    /**
     *
     * @param {OperationType} type
     * @param {boolean} is_error
     * @param {Object} result
     * @returns {OperationResponse}
     */
    operationResponse(type: OperationType, is_error: boolean, result: Object): OperationResponse {
        return new OperationResponse(type, is_error, result);
    }
}