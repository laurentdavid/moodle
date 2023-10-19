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
 * Paged data mutations
 *
 * @module     core/local/pagination/mutations
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export default class {

    /**
     * Constructor
     * @param {CallableFunction} renderPagesContentCallback
     */
    constructor(renderPagesContentCallback) {
        this.renderPagesContentCallback = renderPagesContentCallback;
    }

    /**
     * Page changes
     * @param {StateManager} stateManager
     * @param {string} direction
     **/
    async pageChange(stateManager, direction) {
        // Get the state data from the state manager.
        stateManager.setReadOnly(false);
        const state = stateManager.state;
        const currentPage = state.pages.find((page) => page.active);
        if (!state.config.totalPages) {
            const currentPageSize = state.config.itemsPerPage.find((item) => item.active).value;
            const newPageNumber = state.pages.size + 1;
            state.pages.add({
                id: newPageNumber,
                pageNumber: newPageNumber,
                offset: 0,
                limit: currentPageSize,
                active: false,
                content: ""
            });
        }
        const totalPages = state.config.totalPages ? state.config.totalPages : state.pages.size;
        let nextPageNumber = direction === 'next' ? currentPage.pageNumber + 1 : currentPage.pageNumber - 1;
        if (nextPageNumber > totalPages) {
            nextPageNumber = totalPages;
        }
        if (nextPageNumber < 1) {
            nextPageNumber = 1;
        }
        state.pages.forEach((page) => {
            page.active = page.pageNumber === nextPageNumber;
        });
        await this._updatePages(state, this.renderPagesContentCallback);
        stateManager.setReadOnly(true);
    }

    /**
     * Page load
     * @param {StateManager} stateManager
     * @param {number} pageNumber
     **/
    async pageLoadsContent(stateManager, pageNumber) {
        // Get the state data from the state manager.
        stateManager.setReadOnly(false);
        const state = stateManager.state;
        const currentPage = state.pages.find((page) => page.pageNumber === pageNumber);
        currentPage.content = "";
        await this._updatePages(state, this.renderPagesContentCallback);
        stateManager.setReadOnly(true);
    }

    /**
     * Page change size
     * @param {StateManager} stateManager
     * @param {number} limit
     **/
    async changePageSize(stateManager, limit) {
        stateManager.setReadOnly(false);
        const state = stateManager.state;
        state.config.itemsPerPage.forEach((item) => {
            item.active = (item.value === limit);
        });
        state.config.totalPages = 0;
        // Reload the pages.
        state.pages = [{
            id: 1,
            pageNumber: 1,
            offset: 0,
            limit: limit,
            active: true,
            content: "",
        }];
        await this._updatePages(state, this.renderPagesContentCallback);
        stateManager.setReadOnly(true);
    }

    /**
     * Update pages
     * @param {object} state
     * @param {CallableFunction} renderPagesContentCallback
     **/
    async _updatePages(state, renderPagesContentCallback) {
        const contentPromises = await renderPagesContentCallback(state.pages, {
            allItemsLoaded: async (currentPage) => {
                state.config.totalPages = Math.max(currentPage, state.config.totalPages);
            }
        });
        const contentArray = await Promise.all(contentPromises);
        const pageKeyIterator = state.pages.keys();
        contentArray.forEach((content) => {
            const pageKey = pageKeyIterator.next().value;
            const page = state.pages.get(pageKey);
            if (page.content === "") {
                page.content = content;
            }
        });
    }
}