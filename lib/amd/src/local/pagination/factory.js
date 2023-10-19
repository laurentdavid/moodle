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
 * Paged content factory
 *
 * @module     core/local/pagination/factory
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import Notification from 'core/notification';
import {getPagedContentReactive} from "./paged_content_reactive";
import $ from 'jquery';
import {DEFAULT} from "./defaults";
import PagedContentComponent from "./paged_content";
import PagedContentMutations from "./mutations";

/**
 * Create a paged content widget where the complete list of items is not loaded
 * up front but will instead be loaded by an ajax request (or similar).
 *
 * The client code must provide a callback function which loads and renders the
 * items for each page. See PagedContent.init for more details.
 *
 * The function will return a deferred that is resolved with a jQuery object
 * for the HTML content and a string for the JavaScript.
 *
 * The current list of configuration options available are:
 *      dropdown {bool} True to render the page control as a dropdown (paging bar is default).
 *      maxPages {Number} The maximum number of pages to show in the dropdown (only works with dropdown option)
 *      ignoreControlWhileLoading {bool} Disable the pagination controls while loading a page (default to true)
 *      controlPlacementBottom {bool} Render controls under paged content (default to false)
 *
 * @param  {int|null} numberOfItems How many items are there in total.
 * @param  {int|array|null} itemsPerPage  How many items will be shown per page.
 * @param  {function} renderPagesContentCallback  Callback for loading and rendering the items.
 * @param  {object} config  Configuration options provided by the client.
 * @return {Promise} Resolved with jQuery HTML and string JS.
 */
export const createWithTotalAndLimit = (numberOfItems, itemsPerPage, renderPagesContentCallback, config) => {

    let templateContext = {... DEFAULT.DEFAULT_PAGE_CONTEXT };
    if (config.hasOwnProperty('controlPlacementBottom')) {
        templateContext.controlplacementbottom = config.controlPlacementBottom;
    }
    var deferred = $.Deferred();

    // Create a random id for the element.
    let id = 'paged-content-' + Math.floor(Math.random() * 1000000);
    // Set the id to the custom namespace provided
    if (config.hasOwnProperty('eventNamespace')) {
        id = config.eventNamespace;
    }
    templateContext.pagecontainerid = id;
    // Initialise the reactive component.
    const reactive = getPagedContentReactive(id);
    const defaultLimit = (itemsPerPage ? itemsPerPage : DEFAULT.DEFAULT_ITEMS_PER_PAGE).find((item) => item.active).value;
    reactive.setInitialState({
        config: {
            numberOfItems: numberOfItems,
            itemsPerPage: itemsPerPage,
            hasPagingBar: true,
            totalPages: 0,
            ...config
        },
        pages: [{
            id: 1,
            pageNumber: 1,
            offset: 0,
            limit: defaultLimit,
            active: true,
            content: "",
        }],
    });
    reactive.setMutations(new PagedContentMutations(renderPagesContentCallback));
    // Set up the mutation observer (Vanilla Javascript) so we can check whenever the paged content has been inserted.
    const observer = new MutationObserver((mutations, observerInstance) => {
        if (document.getElementById(id)) {
            observerInstance.disconnect(); // Stop observing
            PagedContentComponent.init(document.getElementById(id), []);
        }
    });
    // We pas the renderPageContentCallback so we can then deal with data in the mutation controller.
    Templates.renderForPromise('core/local/pagination/paged_content', templateContext).then(
        ({html, js}) => {
                deferred.resolve(html, js); // This will just call the deferred method and resolve the renderPagesContentCallback.
        }).catch(Notification.exception);
    // Start observing the document with the configured parameters
    observer.observe(document.body, {childList: true, subtree: true});
    return deferred.promise();
};

/**
 * Create a paged content widget where the complete list of items is not loaded
 * up front but will instead be loaded by an ajax request (or similar).
 *
 * The client code must provide a callback function which loads and renders the
 * items for each page. See PagedContent.init for more details.
 *
 * The function will return a deferred that is resolved with a jQuery object
 * for the HTML content and a string for the JavaScript.
 *
 * The current list of configuration options available are:
 *      dropdown {bool} True to render the page control as a dropdown (paging bar is default).
 *      maxPages {Number} The maximum number of pages to show in the dropdown (only works with dropdown option)
 *      ignoreControlWhileLoading {bool} Disable the pagination controls while loading a page (default to true)
 *      controlPlacementBottom {bool} Render controls under paged content (default to false)
 *
 * @param  {int|array|null} itemsPerPage  How many items will be shown per page.
 * @param  {function} renderPagesContentCallback  Callback for loading and rendering the items.
 * @param  {object} config  Configuration options provided by the client.
 * @return {promise} Resolved with jQuery HTML and string JS.
 */
export const createWithLimit = (itemsPerPage, renderPagesContentCallback, config) => {
    return createWithTotalAndLimit(null, itemsPerPage, renderPagesContentCallback, config);
};
