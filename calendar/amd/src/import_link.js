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
 * A javascript module to enhance the calendar import link.
 *
 * @module     core_calendar/import_link
 * @copyright  2022 Laurent David
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Selectors for the calendar export page.
 *
 * @property {string} manageSubscriptions The CSS class for all the manage subscription links.
 */
const localSelector = {
    manageSubscriptions: 'button[data-type=manage-subscription-link]',
};

/**
 * Initialises the click action so to check if we have selected a course or not.
 *
 * @param {Node} rootElement
 * @param {number} courseId
 * @method init
 */
export const init = (rootElement, courseId) => {
    const linkElement = rootElement.parentNode?.parentNode?.querySelector(localSelector.manageSubscriptions);
    if (linkElement) {
        const inputCourseElement = linkElement.parentNode.querySelector('input[name="course"]');
        inputCourseElement.value = courseId;
    }
};
