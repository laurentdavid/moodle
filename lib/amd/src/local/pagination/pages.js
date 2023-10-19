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

/**
 * Pages component.
 *
 * @module     core/local/pagination/pages
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
            PAGES_CONTAINER: `[data-region='pages-content']`,
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
        const reactive = getPagedContentReactive(element.dataset.reactiveId);
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
        return [ {watch: `pages:updated`, handler: this._updatePages},];
    }

    /**
     * Initial state ready method.
     *
     * @param {object} state the initial state
     * @return {Promise<void>}
     */
    async stateReady(state) {
        this.pages = [];
        await this._updatePages({state});
    }

    /**
     * Update page content
     * @param {Object} element
     * @param {Object} element.state
     * @private
     */
    async _updatePages({state}) {
        const element = this.getElement();
        const pagesContainer = this.getElement(this.selectors.PAGES_CONTAINER);
        for (const page of state.pages.values()) {
            if (typeof this.pages[page.pageNumber] == 'undefined') {
                let pageContainer = pagesContainer.querySelector('[data-page="' + page.pageNumber + '"]');
                if (pageContainer === null) {
                    pageContainer = document.createElement('div');
                    pageContainer.setAttribute('data-page', page.pageNumber);
                    pagesContainer.appendChild(pageContainer);
                }
                this.pages[page.pageNumber] = await this.renderComponent(
                    pageContainer,
                    'core/local/pagination/page', {
                        reactiveid: element.dataset.reactiveId,
                        page: page,
                    });
            }
        }
    }
}