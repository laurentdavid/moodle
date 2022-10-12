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
 * Javascript module for applying presets.
 *
 * @module     mod_data/applypresets
 * @copyright  2022 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from "core/templates";
import Modal from 'core/modal';
import {get_string as getString} from 'core/str';
import Notification from "core/notification";

/**
 * Initialize module
 * @param {object} param
 * @param {object} param.mappingPageParams Information to build the Mapping button
 * @param {object} param.applyPresetParams Information to build the Apply Presets button
 * @param {string} param.presetLabel The actual preset label
 * @param {string} param.fieldsToCreate Fields to be created
 * @param {string} param.fieldsToUpdate Fields to be updated
 * @param {string} param.fieldsToDelete Fields to be deleted
 */
export const showApplyPresetsDialog = async({
                                                 mappingPageParams,
                                                 applyPresetParams,
                                                 presetLabel,
                                                 fieldsToCreate,
                                                 fieldsToUpdate,
                                                 fieldsToDelete,
                                             }) => {
    let title = '';
    if (presetLabel) {
        title = await getString('mapping:dialogtitle:usepreset', 'mod_data', presetLabel);
    } else {
        title = await getString('mapping:dialogtitle:import', 'mod_data');
    }

    let buttons, modalPromise;
    const hideDialogCallback = function(event) {
        this.hide();
        event.preventDefault();
        return false;
    };
    buttons = [
        {
            action: '#',
            formid: 'cancelbtn',
            label: await getString('cancel', 'moodle'),
            onsubmit: hideDialogCallback
        }];
    if (mappingPageParams) {
        buttons.push({
            ...mappingPageParams
        });
    }
    if (applyPresetParams) {
        buttons.push({
            ...applyPresetParams
        });
    }
    modalPromise = Templates.render('mod_data/fields_mapping_modal', {
        title: title,
        fieldstocreate: fieldsToCreate,
        fieldstoupdate: fieldsToUpdate,
        fieldstodelete: fieldsToDelete,
        buttons: buttons
    });
    modalPromise.then(function(html) {
        return new Modal(html);
    }).fail(Notification.exception)
        .then((modal) => {
            modal.show();
            buttons.forEach((buttonConfig) => {
                const attachmentPoint = modal.getAttachmentPoint()[0];
                const buttonFormElement = attachmentPoint.querySelector('#' + buttonConfig.formid);
                if (typeof buttonConfig.onsubmit !== 'undefined') {
                    buttonFormElement.addEventListener('submit', buttonConfig.onsubmit.bind(modal));
                }
            });
            return modal;
        }).fail(Notification.exception);
};
