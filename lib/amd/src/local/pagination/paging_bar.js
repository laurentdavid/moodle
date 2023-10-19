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
import ReactiveEvents from './reactive_events';

/**
 * A generic single state reactive module for pagination.
 *
 * @module     core/local/pagination/paging_bar
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export default class extends BaseComponent {
    /**
     * Constructor hook.
     */
    create() {
        this.name = 'paging-bar';
        this.selectors = {
            ROOT: `[data-region='paging-control-container']`,
            PREVIOUS_PAGE: `[data-region='page-item'][data-control='previous']`,
            NEXT_PAGE: `[data-region='page-item'][data-control='next']`,
            PAGE_SIZE_SELECTORS: `[data-region='paging-control-limit-container'] .dropdown-menu .dropdown-item`,
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
     * Initial state ready method.
     *
     * @return {Promise<void>}
     */
    async stateReady() {
        this.addEventListener(
            this.getElement(this.selectors.PREVIOUS_PAGE),
            'click',
            this._previousPage,
        );
        this.addEventListener(
            this.getElement(this.selectors.NEXT_PAGE),
            'click',
            this._nextPage,
        );
        var elements = this.getElements(this.selectors.PAGE_SIZE_SELECTORS);

        Array.from(elements).forEach((element) => {
            this.addEventListener(
                element,
                'click',
                this._changePageSize,
            );
        });
    }
    /**
     * Get Watcher
     * @return {[{handler: *, watch: string}]}
     */
    getWatchers() {
        return [
            {watch: `pages:updated`, handler: this._checkLastPage},
            {watch: `pages:updated`, handler: this._checkFirstPage},
        ];
    }
    /**
     * Next page.
     *
     * @param {Event} event
     */
    _nextPage(event) {
        event.preventDefault();
        this.reactive.dispatch(ReactiveEvents.pageChange, 'next');
    }

    /**
     * Previous page.
     *
     * @param {Event} event
     */
    _previousPage(event) {
        event.preventDefault();
        this.reactive.dispatch(ReactiveEvents.pageChange, 'previous');
    }

    /**
     * Change page size.
     *
     * @param {Event} event
     */
    _changePageSize(event) {
        event.preventDefault();
        this.reactive.dispatch(ReactiveEvents.changePageSize, Number.parseInt(event.target.dataset.limit));
    }

    /**
     * Check if we are on the last page.
     *
     * @param {Object} obj
     * @param {Object} obj.element
     * @param {Object} obj.state
     */
    _checkLastPage({element, state}) {
        if (element.active) {
            if (element.pageNumber === state.config.totalPages) {
                this.getElement(this.selectors.NEXT_PAGE).classList.add('disabled');
            } else {
                this.getElement(this.selectors.NEXT_PAGE).classList.remove('disabled');
            }
        }
    }
    /**
     * Check if we are on the first page.
     *
     * @param {Object} obj
     * @param {Object} obj.element
     */
    _checkFirstPage({element}) {
        if (element.active) {
            if (element.pageNumber === 1) {
                this.getElement(this.selectors.PREVIOUS_PAGE).classList.add('disabled');
            } else {
                this.getElement(this.selectors.PREVIOUS_PAGE).classList.remove('disabled');
            }
        }
    }
}