// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Sorting module for the recordings table.
 *
 * @module     mod_bigbluebuttonbn/recordings_sorting
 * @copyright  2025 Blindside Networks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Stores sorting order state for each column.
 * @type {Object}
 */
const sortOrders = {name: true, description: true, date: true};

/**
 * Sorts the table rows based on the selected column.
 *
 * @param {string} column - The column to sort by.
 */
export const sortTable = (column) => {
    const tableContainer = document.querySelector(".mod_bigbluebuttonbn_recordings_table");
    if (!tableContainer) {
        return;
    }

    const rows = Array.from(tableContainer.querySelectorAll(".row.mb-3.align-items-center"));

    rows.sort((rowA, rowB) => {
        let valueA, valueB;

        if (column === "date") {
            const dateAElement = rowA.querySelector(".col-md-2[data-sort='date']");
            const dateBElement = rowB.querySelector(".col-md-2[data-sort='date']");

            if (!dateAElement || !dateBElement) {
                return 0;
            }

            const dateA = parseDate(dateAElement.textContent.trim());
            const dateB = parseDate(dateBElement.textContent.trim());

            return sortOrders[column] ? dateA - dateB : dateB - dateA;
        } else {
            const columnSelector = `.col-md-${column === "name" ? 1 : 2}[data-sort='${column}']`;
            const elementA = rowA.querySelector(columnSelector);
            const elementB = rowB.querySelector(columnSelector);

            if (!elementA || !elementB) {
                return 0;
            }

            valueA = elementA.textContent.trim().toLowerCase();
            valueB = elementB.textContent.trim().toLowerCase();

            return sortOrders[column] ? valueA.localeCompare(valueB) : valueB.localeCompare(valueA);
        }
    });

    rows.forEach(row => {
        tableContainer.appendChild(row);
    });

    sortOrders[column] = !sortOrders[column];

    updateSortIcons(column);
};

/**
 * Parses a date string and returns a timestamp for sorting.
 *
 * @param {string} dateString - The date string to parse.
 * @returns {number} The timestamp value of the parsed date.
 */
export const parseDate = (dateString) => {
    const parsedDate = Date.parse(dateString);
    return isNaN(parsedDate) ? 0 : parsedDate;
};

/**
 * Updates the sorting icons on table headers.
 *
 * @param {string} activeColumn - The column currently being sorted.
 */
export const updateSortIcons = (activeColumn) => {
    document.querySelectorAll(".sortable-header .sort-icon").forEach(icon => {
        icon.textContent = "▲";
    });

    const activeHeader = document.querySelector(`.sortable-header[data-sort="${activeColumn}"] .sort-icon`);
    if (activeHeader) {
        activeHeader.textContent = sortOrders[activeColumn] ? "▲" : "▼";
    }
};
