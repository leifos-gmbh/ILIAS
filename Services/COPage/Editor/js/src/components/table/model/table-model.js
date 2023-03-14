/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Controller (handles editor initialisation process)
 */
export default class TableModel {

  //debug = true;

  //currentRow = null;
  //currentCol = null;

  constructor() {
    this.STATE_DATA = "data";          // data editing
    this.STATE_TABLE = "table";        // table properties editing
    this.STATE_CELLS = "cells";        // cells properties editing
    this.states = [
      this.STATE_DATA,
      this.STATE_TABLE,
      this.STATE_CELLS,
    ];

    this.state = this.STATE_TABLE;
    this.selectedCells = new Set(),
    this.debug = true;
    this.currentRow = null;
    this.currentCol = null;
  }

  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  /**
   * @param {string} state
   */
  setState(state) {
    if (this.states.includes(state)) {
      this.log("table-model.setState " + state);
      this.state = state;
    }
  }

  /**
   * @return {string}
   */
  getState() {
    return this.state;
  }


  /**
   *
   * @param {number} row
   * @param {number} col
   */
  setCurrentCell(row, col) {
    this.currentRow = row;
    this.currentCol = col;
  }

  /**
   * @return {number}
   */
  getCurrentRow() {
    return this.currentRow;
  }

  /**
   * @return {number}
   */
  getCurrentColumn() {
    return this.currentCol;
  }

  /**
   *
   * @param {string} pcid
   * @param {string} hierid
   */
  toggleSelect(row, col) {
    const key = row + ":" + col;
    if (this.model.selectedCells.has(key)) {
      this.model.selectedCells.delete(key);
    } else {
      this.model.selectedCells.add(key);
    }
  }

  selectNone() {
    this.model.selectedCells.clear();
  }

  /**
   * Do we have selected cells?
   * @return {boolean}
   */
  hasSelected() {
    return (this.model.selectedCells.size  > 0);
  }

  /**
   * Get all selected cells
   * @return {Set<string>}
   */
  getSelected() {
    return this.model.selectedCells;
  }

}