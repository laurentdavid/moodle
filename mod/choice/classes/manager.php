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

namespace mod_choice;


use cm_info;
use context_module;
use stdClass;

/**
 * Class manager for choice activity
 *
 * @package    mod_choice
 * @copyright  2025 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** Module name. */
    const MODULE = 'choice';

    /** The plugin name. */
    const PLUGINNAME = 'mod_choice';

    /** @var stdClass course_module record. */
    private $instance;

    /** @var context_module the current context. */
    private $context;

    /** @var cm_info course_modules record. */
    private $cm;

    /**
     * Class constructor.
     *
     * @param cm_info $cm course module info object
     * @param stdClass $instance activity instance object.
     */
    public function __construct(cm_info $cm, stdClass $instance) {
        global $CFG;
        $this->cm = $cm;
        $this->instance = $instance;
        $this->context = context_module::instance($cm->id);
        $this->instance->cmidnumber = $cm->idnumber;
    }

    /**
     * Create a manager instance from an instance record.
     *
     * @param stdClass $instance an activity record
     * @return manager
     */
    public static function create_from_instance(stdClass $instance): self {
        $cm = get_coursemodule_from_instance(self::MODULE, $instance->id);
        // Ensure that $this->cm is a cm_info object.
        $cm = cm_info::create($cm);
        return new self($cm, $instance);
    }

    /**
     * Create a manager instance from a course_modules record.
     *
     * @param stdClass|cm_info $cm an activity record
     * @return manager
     */
    public static function create_from_coursemodule($cm): self {
        global $DB;
        // Ensure that $this->cm is a cm_info object.
        $cm = cm_info::create($cm);
        $instance = $DB->get_record(self::MODULE, ['id' => $cm->instance], '*', MUST_EXIST);
        return new self($cm, $instance);
    }

    /**
     * Create a manager instance from a data_record entry.
     *
     * @param stdClass $record the data_record record
     * @return manager
     */
    public static function create_from_choice_record($record): self {
        global $DB;
        $instance = $DB->get_record(self::MODULE, ['id' => $record->dataid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance(self::MODULE, $instance->id);
        $cm = cm_info::create($cm);
        return new self($cm, $instance);
    }

    /**
     * Return the current context.
     *
     * @return context_module
     */
    public function get_context(): context_module {
        return $this->context;
    }

    /**
     * Return the current instance.
     *
     * @return stdClass the instance record
     */
    public function get_instance(): stdClass {
        return $this->instance;
    }

    /**
     * Return the current cm_info.
     *
     * @return cm_info the course module
     */
    public function get_coursemodule(): cm_info {
        return $this->cm;
    }

    /**
     * Return the current course module id.
     *
     * @return int the course module id
     */
    public function get_coursemodule_id(): int {
        return $this->cm->id;
    }

    /**
     * Return the current answers for this choice module
     *
     * @param int|null $userid the user id, if null for all users
     * @return array the answers
     */
    public function get_answers(?int $userid = null): array {
        global $DB;
        $conditions = ['choiceid' => $this->instance->id];
        if ($userid) {
            $conditions['userid'] = $userid;
        }
        return $DB->get_records('choice_answers', $conditions, 'id', '*');
    }

    /**
     * Return the current answers count for this choice module
     *
     * @param int|null $userid the user id, if null for all users
     * @return int the answers
     */
    public function get_answers_count(?int $userid = null): int {
        global $DB;
        $conditions = ['choiceid' => $this->instance->id];
        if ($userid) {
            $conditions['userid'] = $userid;
        }
        return $DB->count_records('choice_answers', $conditions);
    }
}
