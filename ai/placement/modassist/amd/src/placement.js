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
 * Module to load and render the tools for the AI assist plugin.
 *
 * @module     aiplacement_modassist/placement
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import Ajax from 'core/ajax';
import 'core/copy_to_clipboard';
import Notification from 'core/notification';
import Selectors from 'aiplacement_modassist/selectors';
import Policy from 'core_ai/policy';
import AIHelper from 'core_ai/helper';
import DrawerEvents from 'core/drawer_events';
import {subscribe} from 'core/pubsub';
import * as MessageDrawerHelper from 'core_message/message_drawer_helper';
import ModalForm from 'core_form/modalform';
import {getString} from 'core/str';

const AIModAssist = class {

    /**
     * The user ID.
     * @type {Integer}
     */
    userId;
    /**
     * The context ID.
     * @type {Integer}
     */
    contextId;

    /**
     * The current action
     * @type {String}
     */
    currentAction;
    /**
     * The current action data
     * @type {String}
     */
    currentActionData;

    /**
     * The current generated content data
     * @type {String}
     */
    currentGeneratedContent;

    /**
     * Constructor.
     * @param {Integer} userId The user ID.
     * @param {Integer} contextId The context ID.
     */
    constructor(userId, contextId) {
        this.userId = userId;
        this.contextId = contextId;

        this.aiDrawerElement = document.querySelector(Selectors.ELEMENTS.AIDRAWER);
        this.aiDrawerBodyElement = document.querySelector(Selectors.ELEMENTS.AIDRAWER_BODY);
        this.pageElement = document.querySelector(Selectors.ELEMENTS.PAGE);
        this.clearActions();
        this.registerEventListeners();
    }

    /**
     * Register event listeners.
     */
    registerEventListeners() {
        const actionButtons = document.querySelectorAll(Selectors.ACTIONS.RUN);
        if (!actionButtons) {
            return;
        }
        actionButtons.forEach((element) => {
            element.addEventListener('click', async(event) => {
                event.preventDefault();
                this.toggleAIDrawer();
                const isPolicyAccepted = await this.isPolicyAccepted();
                if (!isPolicyAccepted) {
                    // Display policy.
                    this.displayPolicy();
                    return;
                }
                const modalForm = new ModalForm({
                    modalConfig: {
                        title: element.dataset.actionDescription,
                    },
                    formClass: 'aiplacement_modassist\\form\\mod_assist_action_form',
                    args: {
                        userid: this.userId,
                        action: element.dataset.actionSubtype,
                        component: element.dataset.component,
                        cmid: element.dataset.cmid
                    },
                    saveButtonText: getString('continue'),
                });

                modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, event => {
                    if (event.detail.result) {
                        // Notify the user that the action was successful.
                        this.setCurrentAction(element.dataset.actionSubtype, event.detail.actiondata);
                        this.generateContent();
                    } else {
                        this.clearActions();
                        Notification.addNotification({
                            type: 'error',
                            message: event.detail.errors.join('<br>')
                        });
                    }
                });
                modalForm.show();
            });
        });

        // Close AI drawer if message drawer is shown.
        subscribe(DrawerEvents.DRAWER_SHOWN, () => {
            if (this.isAIDrawerOpen()) {
                this.closeAIDrawer();
            }
        });
    }

    /**
     * Register event listeners for the policy.
     */
    registerPolicyEventListeners() {
        const acceptAction = document.querySelector(Selectors.ACTIONS.ACCEPT);
        const declineAction = document.querySelector(Selectors.ACTIONS.DECLINE);
        if (acceptAction) {
            acceptAction.addEventListener('click', (e) => {
                e.preventDefault();
                this.acceptPolicy().then(() => {
                    return this.generateContent();
                }).catch(Notification.exception);
            });
        }
        if (declineAction) {
            declineAction.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeAIDrawer();
            });
        }
    }

    /**
     * Register event listeners for the error.
     */
    registerErrorEventListeners() {
        const retryAction = document.querySelector(Selectors.ACTIONS.RETRY);
        if (retryAction) {
            retryAction.addEventListener('click', (e) => {
                e.preventDefault();
                this.aiDrawerBodyElement.dataset.hasdata = '0';
                this.generateContent();
            });
        }
    }

    /**
     * Register event listeners for the response.
     */
    registerResponseEventListeners() {
        const regenerateAction = document.querySelector(Selectors.ACTIONS.REGENERATE);
        if (regenerateAction) {
            regenerateAction.addEventListener('click', (e) => {
                e.preventDefault();
                this.aiDrawerBodyElement.dataset.hasdata = '0';
                this.generateContent();
            });
        }
        const applyAction = document.querySelector(Selectors.ACTIONS.APPLY);
        if (applyAction) {
            applyAction.addEventListener('click', (e) => {
                e.preventDefault();
                this.applyActions();
            });
        }
    }

    registerLoadingEventListeners() {
        const cancelAction = document.querySelector(Selectors.ACTIONS.CANCEL);
        if (cancelAction) {
            cancelAction.addEventListener('click', (e) => {
                e.preventDefault();
                this.setRequestCancelled();
                this.toggleAIDrawer();
            });
        }
    }

    /**
     * Check if the AI drawer is open.
     * @return {boolean} True if the AI drawer is open, false otherwise.
     */
    isAIDrawerOpen() {
        return this.aiDrawerElement.classList.contains('show');
    }

    /**
     * Check if the request is cancelled.
     * @return {boolean} True if the request is cancelled, false otherwise.
     */
    isRequestCancelled() {
        return this.aiDrawerBodyElement.dataset.cancelled === '1';
    }

    setRequestCancelled() {
        this.aiDrawerBodyElement.dataset.cancelled = '1';
    }

    /**
     * Open the AI drawer.
     */
    openAIDrawer() {
        // Close message drawer if it is shown.
        MessageDrawerHelper.hide();
        this.aiDrawerElement.classList.add('show');
        this.aiDrawerBodyElement.setAttribute('aria-live', 'polite');
        if (!this.pageElement.classList.contains('show-drawer-right')) {
            this.addPadding();
        }
        // Disable the summary button.
        this.disableActionButton();
    }

    /**
     * Close the AI drawer.
     */
    closeAIDrawer() {
        this.aiDrawerElement.classList.remove('show');
        this.aiDrawerBodyElement.removeAttribute('aria-live');
        if (this.pageElement.classList.contains('show-drawer-right') && this.aiDrawerBodyElement.dataset.removepadding === '1') {
            this.removePadding();
        }
        // Enable the summary button.
        this.enableActionButton();
    }

    /**
     * Toggle the AI drawer.
     */
    toggleAIDrawer() {
        if (this.isAIDrawerOpen()) {
            this.closeAIDrawer();
        } else {
            this.openAIDrawer();
        }
    }

    /**
     * Add padding to the page to make space for the AI drawer.
     */
    addPadding() {
        this.pageElement.classList.add('show-drawer-right');
        this.aiDrawerBodyElement.dataset.removepadding = '1';
    }

    /**
     * Remove padding from the page.
     */
    removePadding() {
        this.pageElement.classList.remove('show-drawer-right');
        this.aiDrawerBodyElement.dataset.removepadding = '0';
    }

    /**
     * Disable the relevant button.
     */
    disableActionButton() {
        const currentAction = this.getCurrentAction();
        if (!currentAction) {
            return;
        }
        const summaryButton = document.querySelector(
            Selectors.ACTIONS.RUN + '[data-action-subtype="' + currentAction.action + '"]'
        );
        if (summaryButton) {
            summaryButton.setAttribute('disabled', 1);
        }
    }

    /**
     * Enable the summary button and focus on it.
     */
    enableActionButton() {
        const currentAction = this.getCurrentAction();
        if (!currentAction) {
            return;
        }
        const summaryButton = document.querySelector(
            Selectors.ACTIONS.RUN + '[data-action-subtype="' + currentAction.action + '"]'
        );
        if (summaryButton) {
            summaryButton.removeAttribute('disabled');
            summaryButton.focus();
        }
    }

    /**
     * Check if the policy is accepted.
     * @return {bool} True if the policy is accepted, false otherwise.
     */
    async isPolicyAccepted() {
        return await Policy.getPolicyStatus(this.userId);
    }

    /**
     * Accept the policy.
     * @return {Promise<Object>}
     */
    acceptPolicy() {
        return Policy.acceptPolicy();
    }

    /**
     * Check if the AI drawer has generated content or not.
     * @return {boolean} True if the AI drawer has generated content, false otherwise.
     */
    hasGeneratedContent() {
        return this.aiDrawerBodyElement.dataset.hasdata === '1';
    }

    /**
     * Display the policy.
     */
    displayPolicy() {
        Templates.render('core_ai/policyblock', {}).then((html) => {
            this.aiDrawerBodyElement.innerHTML = html;
            this.registerPolicyEventListeners();
            return;
        }).catch(Notification.exception);
    }

    /**
     * Display the loading spinner.
     */
    displayLoading() {
        Templates.render('aiplacement_modassist/loading', {}).then((html) => {
            this.aiDrawerBodyElement.innerHTML = html;
            this.registerLoadingEventListeners();
            return;
        }).catch(Notification.exception);
    }

    /**
     * Display the summary.
     */
    async generateContent() {
        const currentAction = this.getCurrentAction();
        if (!currentAction) {
            return;
        }
        if (!this.hasGeneratedContent() && currentAction) {
            // Display loading spinner.
            this.displayLoading();
            // Clear the drawer content to prevent sending some unnecessary content.
            this.aiDrawerBodyElement.innerHTML = '';
            const request = {
                methodname: 'aiplacement_modassist_generate_content',
                args: {
                    contextid: this.contextId,
                    action: currentAction.action,
                    data: JSON.stringify(currentAction.actionData)
                }
            };
            try {
                const responseObj = await Ajax.call([request])[0];
                this.aiDrawerBodyElement.dataset.rawGeneratedContent = "";
                if (responseObj.error) {
                    this.displayError();
                    return;
                } else {
                    if (!this.isRequestCancelled()) {
                        // Replace double line breaks with <br> and with </p><p> for paragraphs.
                        this.aiDrawerBodyElement.dataset.rawGeneratedContent = responseObj.generatedcontent;
                        const generatedContent = AIHelper.replaceLineBreaks(responseObj.generatedcontent);
                        this.displayResponse(generatedContent);
                        return;
                    } else {
                        this.aiDrawerBodyElement.dataset.cancelled = '0';
                    }
                }
            } catch (error) {
                window.console.log(error);
                this.displayError();
            }
        }
    }

    async applyActions() {
        const currentAction = this.getCurrentAction();
        if (!currentAction) {
            return;
        }
        if (this.hasGeneratedContent()) {
            try {
                this.displayLoading();
                // Clear the drawer content to prevent sending some unnecessary content.
                this.aiDrawerBodyElement.innerHTML = '';
                const request = {
                    methodname: 'aiplacement_modassist_process_response',
                    args: {
                        contextid: this.contextId,
                        action: currentAction.action,
                        generatedcontent: this.aiDrawerBodyElement.dataset.rawGeneratedContent
                    }
                };
                const responseObj = await Ajax.call([request])[0];
                if (responseObj.error) {
                    this.displayError();
                    return;
                } else {
                    Notification.addNotification({
                        type: 'success',
                        message: responseObj.message
                    });
                    window.location.reload();
                }
            } catch (error) {
                window.console.log(error);
                this.displayError();
            }
        }

    }
    /**
     * Display the response.
     * @param {String} content The content to display.
     */
    displayResponse(content) {
        Templates.render('aiplacement_modassist/response', {content: content}).then((html) => {
            this.aiDrawerBodyElement.innerHTML = html;
            this.aiDrawerBodyElement.dataset.hasdata = '1';
            this.registerResponseEventListeners();
            return;
        }).catch(Notification.exception);
    }

    /**
     * Display the error.
     */
    displayError() {
        Templates.render('aiplacement_modassist/error', {}).then((html) => {
            this.aiDrawerBodyElement.innerHTML = html;
            this.registerErrorEventListeners();
            return;
        }).catch(Notification.exception);
    }

    /**
     * Get the text content of the main region.
     * @return {String} The text content.
     */
    getTextContent() {
        const mainRegion = document.querySelector(Selectors.ELEMENTS.MAIN_REGION);
        return mainRegion.innerText || mainRegion.textContent;
    }

    /**
     * Finish the current action.
     */
    clearActions() {
        this.aiDrawerBodyElement.dataset.currentAction = '';
        this.aiDrawerBodyElement.dataset.currentActionData = '';
    }

    /**
     * Set the current action.
     * @param {String} action
     * @param {Object} actionData
     */
    setCurrentAction(action, actionData) {
        this.aiDrawerBodyElement.dataset.currentAction = action;
        this.aiDrawerBodyElement.dataset.currentActionData = JSON.stringify(actionData);
    }

    /**
     * Get current action.
     * @return {{action: string, actionData: any}|null}
     */
    getCurrentAction() {
        if (!this.aiDrawerBodyElement.dataset.currentAction) {
            return null;
        }
        return {
            action: this.aiDrawerBodyElement.dataset.currentAction,
            actionData: JSON.parse(this.aiDrawerBodyElement.dataset.currentActionData)
        };
    }
};

export default AIModAssist;
