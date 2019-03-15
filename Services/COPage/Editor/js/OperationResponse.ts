import OperationType from './OperationType';

/**
 * Operation Response
 */
export default class OperationResponse {
    type: OperationType;
    result: Object;
    is_error: boolean;

    constructor(type: OperationType, is_error: boolean, result: Object) {
        this.type = type;
        this.is_error = is_error;
        this.result = result;
    }
}