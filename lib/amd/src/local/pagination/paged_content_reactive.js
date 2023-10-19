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


import {Reactive} from 'core/reactive';
import ReactiveEvents from './reactive_events';

/**
 * All reactives for paged content.
 * @type {*[]}
 */
const pagedContentReactives = new Map();

/**
 * Get reactive for given id.
 *
 * @method getPagedContentReactive
 * @param {string} id id of the reactive component
 */
export const getPagedContentReactive = (id) => {
    if (!pagedContentReactives.has(id)) {
        let reactiveParam = {
            name: 'PagedContentReactive',
            eventDispatch: dispatchStateChangedEvent,
            eventName: ReactiveEvents.pageContentChanged,
        };
        const pagedContentReactive = new Reactive(reactiveParam);
        pagedContentReactives.set(id, pagedContentReactive);
    }
    return pagedContentReactives.get(id);
};

/**
 * Trigger a global state changed event.
 *
 * @method dispatchStateChangedEvent
 * @param {object} detail the full state
 * @param {object} target the custom event target (document if none provided)
 */
const dispatchStateChangedEvent = (detail, target) => {
    if (target === undefined) {
        target = document;
    }
    target.dispatchEvent(new CustomEvent(ReactiveEvents.pageContentChanged, {
        bubbles: true,
        detail: detail,
    }));
};
