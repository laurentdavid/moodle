<?php
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

namespace mod_data\output;

use data_portfolio_caller;
use mod_data\manager;
use moodle_url;
use portfolio_add_button;
use templatable;
use renderable;

/**
 * Renderable class for the action bar elements in the view pages in the database activity.
 *
 * @package    mod_data
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_action_bar implements templatable, renderable {

    /** @var \url_select $urlselect The URL selector object. */
    private $urlselect;

    /** @var bool $hasentries Whether entries exist. */
    private $hasentries;

    /** @var bool $mode The current view mode (list, view...). */
    private $mode;

    /** @var manager $manager Current manager. */
    private $manager;

    /**
     * The class constructor.
     *
     * @param int|null $id The database module id.
     * @param \url_select $urlselect The URL selector object.
     * @param bool $hasentries Whether entries exist.
     * @param string $mode The current view mode (list, view...).
     * @param manager|null $manager $manager current manager
     */
    public function __construct(?int $id = null, \url_select $urlselect, bool $hasentries, string $mode, manager $manager = null) {
        if (empty($manager)) {
            debugging('id parameter is deprecated. Please use manager as parameter.', DEBUG_DEVELOPER);
            [$course, $cm] = get_coursemodule_from_id(manager::MODULE, $id);
            $manager = manager::create_from_coursemodule($cm);
        }
        $this->manager = $manager;
        $this->urlselect = $urlselect;
        $this->hasentries = $hasentries;
        $this->mode = $mode;
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output The renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        global $PAGE, $CFG;

        $data = [
            'urlselect' => $this->urlselect->export_for_template($output),
        ];
        $cm = $this->manager->get_coursemodule();
        $instance = $this->manager->get_instance();

        $actionsselect = null;
        // Import entries.
        if (has_capability('mod/data:manageentries', $this->manager->get_context())) {
            $actionsselect = new \action_menu();
            $actionsselect->set_menu_trigger(get_string('actions'), 'btn btn-secondary');

            $importentrieslink = new moodle_url('/mod/data/import.php', ['id' => $cm->id,
                'backto' => $PAGE->url->out(false)]);
            $actionsselect->add(new \action_menu_link(
                $importentrieslink,
                null,
                get_string('importentries', 'mod_data'),
                false
            ));
        }

        // Export entries.
        if (has_capability(DATA_CAP_EXPORT, $this->manager->get_context()) && $this->hasentries) {
            if (!$actionsselect) {
                $actionsselect = new \action_menu();
                $actionsselect->set_menu_trigger(get_string('actions'), 'btn btn-secondary');
            }
            $exportentrieslink = new moodle_url('/mod/data/export.php', ['id' => $cm->id,
                'backto' => $PAGE->url->out(false)]);
            $actionsselect->add(new \action_menu_link(
                $exportentrieslink,
                null,
                get_string('exportentries', 'mod_data'),
                false
            ));
        }

        // Export to portfolio. This is for exporting all records, not just the ones in the search.
        if ($this->mode == '' && !empty($CFG->enableportfolios) && $this->hasentries) {
            if ($this->manager->can_export_entries()) {
                // Add the portfolio export button.
                require_once($CFG->libdir . '/portfoliolib.php');

                $cm = $this->manager->get_coursemodule();

                $button = new portfolio_add_button();
                $button->set_callback_options(
                    'data_portfolio_caller',
                    ['id' => $cm->id],
                    'mod_data'
                );
                if (data_portfolio_caller::has_files($instance)) {
                    // No plain HTML.
                    $button->set_formats([PORTFOLIO_FORMAT_RICHHTML, PORTFOLIO_FORMAT_LEAP2A]);
                }
                $exporturl = $button->to_html(PORTFOLIO_ADD_MOODLE_URL);
                if (!is_null($exporturl)) {
                    if (!$actionsselect) {
                        $actionsselect = new \action_menu();
                        $actionsselect->set_menu_trigger(get_string('actions'), 'btn btn-secondary');
                    }
                    $actionsselect->add(new \action_menu_link(
                        $exporturl,
                        null,
                        get_string('addtoportfolio', 'portfolio'),
                        false
                    ));
                }
            }
        }

        if ($actionsselect) {
            $data['actionsselect'] = $actionsselect->export_for_template($output);
        }

        return $data;
    }
}
