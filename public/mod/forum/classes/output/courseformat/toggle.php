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

namespace mod_forum\output\courseformat;

use core\external\exporter;
use core\output\externable;
use core\output\named_templatable;
use core_course\cm_info;
use mod_forum\external\toggle_exporter;

/**
 * Base class to render an activity badge.
 *
 * Plugins can extend this class and override some methods to customize the content to be displayed in the activity badge.
 *
 * @package    mod_forum
 * @copyright  2025 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class toggle implements externable, named_templatable, \renderable {
    /**
     * Constructor.
     *
     * @param cm_info $cm the course module.
     * @param string $type the type of toggle.
     * @param bool $targetstate The target state of the toggle, used in the HTML value attribute.
     * @param bool $checked Whether the toggle is checked or not, used in the HTML checked attribute.
     * @param bool $disabled Whether the toggle is disabled or not.
     * @param string|null $label The label of the toggle.
     */
    public function __construct(
        /** @var cm_info $cm the course module. */
        public cm_info $cm,
        /** @var string $type The type of toggle. */
        public string $type = 'forum-track-toggle',
        /** @var bool $targetstate The target state of the toggle, used in the HTML value attribute. */
        protected bool $targetstate = true,
        /** @var string $checked The current state of the toggle, used in the HTML value attribute. */
        public bool $checked = true,
        /** @var bool $disabled Whether the toggle is disabled or not. */
        public bool $disabled = false,
        /** @var string|null $label The label of the toggle. */
        public ?string $label = null,
    ) {
    }

    #[\Override]
    public function get_template_name(\renderer_base $renderer): string {
        return 'core/toggle';
    }

    #[\Override]
    public function export_for_template(\core\output\renderer_base $output) {
        $id = "{$this->type}-{$this->cm->instance}";
        $data = [
            'id' => $id,
            'checked' => $this->checked,
            'disabled' => $this->disabled,
            'dataattributes' => [
                ['name' => 'type', 'value' => $this->type],
                ['name' => 'forumid', 'value' => $this->cm->instance],
                ['name' => 'targetstate', 'value' => $this->targetstate],
                ['name' => 'action', 'value' => 'toggle'],
            ],
            'label' => $this->label,
            'labelclasses' => 'visually-hidden',
        ];
        $output->get_page()->requires->js_call_amd(
            'mod_forum/forum_overview_toggle',
            'init',
            [$id],
        );
        return (object) $data;
    }

    #[\Override]
    public function get_exporter(?\core\context $context = null): exporter {
        $context = $context ?? $this->cm->context;
        return new toggle_exporter($this, ['context' => $context]);
    }


    #[\Override]
    public static function get_read_structure(
        int $required = VALUE_REQUIRED,
        mixed $default = null
    ): \core_external\external_single_structure {
        return toggle_exporter::get_read_structure($required, $default);
    }

    #[\Override]
    public static function read_properties_definition(): array {
        return toggle_exporter::read_properties_definition();
    }

}
