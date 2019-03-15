import Operation from './Operation';

// internal interface, not exported
interface IOpQueue {
    push(op: Operation, par?: number): void,
    pop(): Operation
}

/**
 * Stores operations being done in the editor
 */
export default class OpQueue implements IOpQueue {
    operations: Operation[];

    constructor() {
        this.operations = [];
    }

    /**
     * Push operation to queue
     * @param {Operation} op
     * @param {number} par
     */
    push(op: Operation, par?: number): void {
        console.log("OpQueue push called.");
        console.log(op);
        this.operations.push(op);
    }

    /**
     * Pop operation from queue
     * @returns {Operation}
     */
    pop(): Operation {
        return this.operations.shift();
    }

}
