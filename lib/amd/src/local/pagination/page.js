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
import Templates from 'core/templates';
import ReactiveEvents from "./reactive_events";

/**
 * Page component.
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
            PAGE_CONTENT_SELECTOR: `[data-region='page-content']`,
        };
        this.id = this.getElement().id;
        this.pageNumber = parseInt(this.getElement().dataset.page);
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
        return [
            {watch: `pages[${this.pageNumber}]:updated`, handler: this._renderPage},
        ];
    }

    /**
     * Initial state ready method.
     *
     * @return {Promise<void>}
     */
    async stateReady() {
        this.reactive.dispatch(ReactiveEvents.pageLoadsContent, this.pageNumber);
    }

    /**
     * Render a page.
     * @param {Object} element
     * @param {Object} element.element
     */
    async _renderPage({element}) {
        // Now find the page to replace.
        let pageContent = element.content;
        if (pageContent === "") {
            pageContent = await Templates.render('core/loading', {});
        }
        const currentElement = this.getElement(
            `[data-page="${element.pageNumber}"] ` + this.selectors.PAGE_CONTENT_SELECTOR);
        currentElement.innerHTML = pageContent;
        if (element.active) {
            currentElement.classList.remove('d-none');
        } else {
            currentElement.classList.add('d-none');
        }
    }
}