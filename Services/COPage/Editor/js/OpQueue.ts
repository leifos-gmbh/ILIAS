/// <reference path="Operation.ts" />

/// <reference path="../typings/JQueryStatic.d.ts" />
declare var $: JQueryStatic;
//$("div").appendChild($(".iltest"));

namespace il.Editor {

    // internal interface, not exported to the namespace!
    interface IOpQueue {
        push(op: Operation, par?: number): void,
        pop(): Operation
    }

    /**
     * Stores operations being done in the editor
     */
    export class OpQueue implements IOpQueue {
        operations: Operation[];

        constructor() {
        }

        /**
         * Push operation to queue
         * @param {Editor.Operation} op
         * @param {number} par
         */
        push(op: Operation, par?: number): void {
            console.log(par);
            this.operations.push(op);
        }

        /**
         * Pop operation from queue
         * @returns {Editor.Operation}
         */
        pop(): Operation {
            return this.operations.shift();
        }
    }

}
