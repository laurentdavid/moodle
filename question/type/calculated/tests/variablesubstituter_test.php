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

namespace qtype_calculated;

use qtype_calculated_variable_substituter;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/calculated/question.php');
require_once($CFG->dirroot . '/question/type/calculated/questiontype.php');

/**
 * Unit tests for {@link qtype_calculated_variable_substituter}.
 *
 * @package    qtype_calculated
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class variablesubstituter_test extends \advanced_testcase {
    public function test_simple_expression(): void {
        $vs = new qtype_calculated_variable_substituter(array('a' => 1, 'b' => 2), '.');
        $this->assertEquals(3, $vs->calculate('{a} + {b}'));
    }

    public function test_simple_expression_negatives(): void {
        $vs = new qtype_calculated_variable_substituter(array('a' => -1, 'b' => -2), '.');
        $this->assertEquals(1, $vs->calculate('{a}-{b}'));
    }

    public function test_cannot_use_nonnumbers(): void {
        $this->expectException(\moodle_exception::class);
        $vs = new qtype_calculated_variable_substituter(array('a' => 'frog', 'b' => -2), '.');
    }

    public function test_invalid_expression(): void {
        $vs = new qtype_calculated_variable_substituter(array('a' => 1, 'b' => 2), '.');
        $this->expectException(\moodle_exception::class);
        $vs->calculate('{a} + {b}?');
    }

    public function test_tricky_invalid_expression(): void {
        $vs = new qtype_calculated_variable_substituter(array('a' => 1, 'b' => 2), '.');
        $this->expectException(\moodle_exception::class);
        $vs->calculate('{a}{b}'); // Have to make sure this does not just evaluate to 12.
    }

    public function test_division_by_zero_expression(): void {
        $vs = new qtype_calculated_variable_substituter(array('a' => 1, 'b' => 0), '.');
        $this->expectException(\moodle_exception::class);
        $vs->calculate('{a} / {b}');
    }

    public function test_replace_expressions_in_text_simple_var(): void {
        $vs = new qtype_calculated_variable_substituter(array('a' => 1, 'b' => 2), '.');
        $this->assertEquals('1 + 2', $vs->replace_expressions_in_text('{a} + {b}'));
    }

    public function test_replace_expressions_in_confusing_text(): void {
        $vs = new qtype_calculated_variable_substituter(array('a' => 1, 'b' => 2), '.');
        $this->assertEquals("(1) 1\n(2) 2", $vs->replace_expressions_in_text("(1) {a}\n(2) {b}"));
    }

    public function test_replace_expressions_in_text_formula(): void {
        $vs = new qtype_calculated_variable_substituter(array('a' => 1, 'b' => 2), '.');
        $this->assertEquals('= 3', $vs->replace_expressions_in_text('= {={a} + {b}}'));
    }

    public function test_expression_has_unmapped_placeholder(): void {
        $this->expectException('moodle_exception');
        $this->expectExceptionMessage(get_string('illegalformulasyntax', 'qtype_calculated', '{c}'));
        $vs = new qtype_calculated_variable_substituter(array('a' => 1, 'b' => 2), '.');
        $vs->calculate('{c} - {a} + {b}');
    }

    public function test_replace_expressions_in_text_negative(): void {
        $vs = new qtype_calculated_variable_substituter(array('a' => -1, 'b' => 2), '.');
        $this->assertEquals('temperatures -1 and 2',
                $vs->replace_expressions_in_text('temperatures {a} and {b}'));
    }

    public function test_replace_expressions_in_text_commas_for_decimals(): void {
        $vs = new qtype_calculated_variable_substituter(
                array('phi' => 1.61803399, 'pi' => 3.14159265), ',');
        $this->assertEquals('phi (1,61803399) + pi (3,14159265) = 4,75962664',
                $vs->replace_expressions_in_text('phi ({phi}) + pi ({pi}) = {={phi} + {pi}}'));
    }

    public function test_format_float_dot(): void {
        $vs = new qtype_calculated_variable_substituter(array('a' => -1, 'b' => 2), '.');
        $this->assertSame('0.12345', $vs->format_float(0.12345));

        $this->assertSame('0', $vs->format_float(0.12345, 0, 1));
        $this->assertSame('0.12', $vs->format_float(0.12345, 2, 1));
        $this->assertSame('0.1235', $vs->format_float(0.12345, 4, 1));

        $this->assertSame('0.12', $vs->format_float(0.12345, 2, 2));
        $this->assertSame('0.0012', $vs->format_float(0.0012345, 4, 1));
    }

    public function test_format_float_comma(): void {
        $vs = new qtype_calculated_variable_substituter(array('a' => -1, 'b' => 2), ',');
        $this->assertSame('0,12345', $vs->format_float(0.12345));

        $this->assertSame('0', $vs->format_float(0.12345, 0, 1));
        $this->assertSame('0,12', $vs->format_float(0.12345, 2, 1));
        $this->assertSame('0,1235', $vs->format_float(0.12345, 4, 1));

        $this->assertSame('0,12', $vs->format_float(0.12345, 2, 2));
        $this->assertSame('0,0012', $vs->format_float(0.0012345, 4, 1));
    }

    public function test_format_float_nan_inf(): void {
        $vs = new qtype_calculated_variable_substituter([ ], '.');

        $this->assertSame('NAN', $vs->format_float(NAN));
        $this->assertSame('INF', $vs->format_float(INF));
        $this->assertSame('-INF', $vs->format_float(-INF));
    }
}
