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


import {BaseComponent} from 'core/reactive';
import {getPagedContentReactive} from "./paged_content_reactive";
import {DEFAULT} from "./defaults";
/**
 * The main paged content component.
 *
 * @module     core/local/pagination/paged_content
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export default class extends BaseComponent {
    /**
     * Constructor hook.
     */
    create() {
        this.name = 'paged-content';
        this.selectors = {
            PAGES_CONTAINER: `[data-region='pages']`,
            PAGING_BAR: `[data-region='paging-bar']`,
            PAGING_DROPDOWN: `[data-region='paging-dropdown']`,
        };
        this.id = this.getElement().id;
    }

    /**
     * Static method to create a component instance form the mustache template.
     *
     * @param {Node} element the DOM main element
     * @param {object} selectors optional css selector overrides
     * @return {BaseComponent} the component instance
     */
    static init(element, selectors) {
        const reactive = getPagedContentReactive(element.id);
        return new this({
            element: element,
            reactive: reactive,
            selectors,
        });
    }

    /**
     * Get Watcher
     * @return {[{handler: *, watch: string}]}
     */
    getWatchers() {
        return [
            // The state.users:created is triggered by the initUserList mutation creates the users field in the state.

        ];
    }
    /**
     * Initial state ready method.
     *
     * @param {object} state the initial state
     * @return {Promise<void>}
     */
    async stateReady(state) {
        if (state.config.hasPagingBar) {
            const activepageNumber = state.pages.find((page) => page.active).pageNumber;
            const pagesForBar = state.pages.values().toArray().map((page) => {
                return {
                    url: '#',
                    page: page.pageNumber,
                    active: page.active,
                };
            });
            this.pagingBar = await this.renderComponent(
                this.getElement(this.selectors.PAGING_BAR),
                'core/local/pagination/paging_bar', {
                    reactiveid: this.id,
                    showitemsperpageselector: true,
                    itemsperpage: DEFAULT.DEFAULT_ITEMS_PER_PAGE,
                    previous: true,
                    next: true,
                    first: false,
                    last: false,
                    activepagenumber: activepageNumber,
                    pages: pagesForBar
                });
        }
        this.pages = await this.renderComponent(
            this.getElement(this.selectors.PAGES_CONTAINER),
            'core/local/pagination/pages', {
                reactiveid: this.id,
                pages: state.pages.values().toArray(),
            });
        // TODO: We need also to deal with the paging dropdown.
    }


}