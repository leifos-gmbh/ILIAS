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
export default class Timer {
    duration: number;
    running: boolean = false;
    window: Window;
    interval: number;
    counter: number;

    constructor(window: Window, duration: number, listener: Function) {
        this.window = window;
        this.duration = duration;
    }

    run(): void {
        let as = this;
        if (this.running) {
            return;
        }
        this.running = true;
        this.counter = this.duration;
        this.interval = this.window.setInterval(() => {
            as.counter -= 1;
            if (as.counter == 0) {
                as.reset();
            }
        }, 1000);
    }

    reset(): void {
        this.counter = 0;
        this.running = false
        this.window.clearInterval(this.interval);
    }
}