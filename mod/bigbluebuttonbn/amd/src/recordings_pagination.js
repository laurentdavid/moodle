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
 * Pagination module for the recordings table.
 *
 * @module     mod_bigbluebuttonbn/recordings_pagination
 * @copyright  2025 Blindside Networks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';

/**
 * Initializes pagination functionality for the recordings table.
 */
export const setupPagination = () => {
    const tableContainer = document.querySelector(".mod_bigbluebuttonbn_recordings_table");
    if (!tableContainer) {
        return;
    }

    const rows = Array.from(tableContainer.querySelectorAll(".row.mb-3.align-items-center"));

    // Select pagination buttons.
    const firstPageBtn = document.getElementById("firstPage");
    const prevPageBtn = document.getElementById("prevPage");
    const nextPageBtn = document.getElementById("nextPage");
    const lastPageBtn = document.getElementById("lastPage");
    const pageSelect = document.getElementById("pageSelect");

    if (!firstPageBtn || !prevPageBtn || !nextPageBtn || !lastPageBtn || !pageSelect) {
        return;
    }

    const itemsPerPage = 10;
    let currentPage = 1;
    let totalPages = Math.ceil(rows.length / itemsPerPage);

    /**
     * Updates the visibility of table rows based on the selected page.
     * @param {number} page - The current page to display.
     */
    function renderTable(page) {
        let visibleIndex = 0;

        rows.forEach(row => {
            if (row.dataset.filtered === "false") {
                row.style.display = "none";
                return;
            }

            const start = (page - 1) * itemsPerPage;
            const end = page * itemsPerPage;

            if (visibleIndex >= start && visibleIndex < end) {
                row.style.display = "flex";
            } else {
                row.style.display = "none";
            }

            visibleIndex++;
        });
    }

    /**
     * Updates pagination buttons and dropdown.
     */
    async function updatePaginationControls() {
        const filteredRows = rows.filter(row => row.dataset.filtered !== "false");
        pageSelect.innerHTML = "";

        let pageString;
        try {
            pageString = await getString('view_recording_yui_page', 'bigbluebuttonbn');
        } catch (error) {
            pageString = "Page";
        }

        totalPages = Math.max(1, Math.ceil(filteredRows.length / itemsPerPage));

        for (let i = 1; i <= totalPages; i++) {
            let option = document.createElement("option");
            option.value = i;
            option.textContent = `${pageString} ${i}`;
            if (i === currentPage) {
                option.selected = true;
            }
            pageSelect.appendChild(option);
        }

        firstPageBtn.disabled = (currentPage === 1);
        prevPageBtn.disabled = (currentPage === 1);
        nextPageBtn.disabled = (currentPage === totalPages);
        lastPageBtn.disabled = (currentPage === totalPages);
    }

    /**
     * Event listeners for first page in pagination controls.
     */
    firstPageBtn.addEventListener("click", () => {
        currentPage = 1;
        renderTable(currentPage);
        updatePaginationControls();
    });

    /**
     * Event listeners for previous page in pagination controls.
     */
    prevPageBtn.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable(currentPage);
            updatePaginationControls();
        }
    });

    /**
     * Event listeners for next page in pagination controls.
     */
    nextPageBtn.addEventListener("click", () => {
        if (currentPage < totalPages) {
            currentPage++;
            renderTable(currentPage);
            updatePaginationControls();
        }
    });

    /**
     * Event listeners for last page in pagination controls.
     */
    lastPageBtn.addEventListener("click", () => {
        currentPage = totalPages;
        renderTable(currentPage);
        updatePaginationControls();
    });

    /**
     * Event listeners for page selection dropdown.
     */
    pageSelect.addEventListener("change", (e) => {
        currentPage = parseInt(e.target.value, 10);
        renderTable(currentPage);
        updatePaginationControls();
    });

    /**
     * Expose pagination update function to be used by search.
     */
    window.updatePagination = () => {
        currentPage = 1;
        renderTable(currentPage);
        updatePaginationControls();
    };

    // Default all rows to visible and flagged as included.
    rows.forEach(row => {
        row.dataset.filtered = "true";
        row.style.display = "flex";
    });

    renderTable(currentPage);
    updatePaginationControls();
};
