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

/**
 * Configurable Reports
 * A Moodle block for creating Configurable Reports
 * @package blocks
 * @author: Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date: 2009
 */

require_once("../../config.php");
require_once($CFG->dirroot."/blocks/configurable_reports/locallib.php");

$id = required_param('id', PARAM_INT);
$download = optional_param('download', false, PARAM_BOOL);
$format = optional_param('format', '', PARAM_ALPHA);
$courseid = optional_param('courseid', null, PARAM_INT);

if (!$report = $DB->get_record('block_configurable_reports', ['id' => $id])) {
    print_error('reportdoesnotexists', 'block_configurable_reports');
}

if ($courseid && $report->global) {
    $report->courseid = $courseid;
} else {
    $courseid = $report->courseid;
}

if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    print_error('No such course id');
}

// Force user login in course (SITE or Course).
if ($course->id == SITEID) {
    require_login();
    $context = context_system::instance();
} else {
    require_login($course);
    $context = context_course::instance($course->id);
}

require_once($CFG->dirroot.'/blocks/configurable_reports/report.class_embed.php');
require_once($CFG->dirroot.'/blocks/configurable_reports/reports/'.$report->type.'/report.class.php');

$reportclassname = 'report_'.$report->type;
$reportclass = new $reportclassname($report);

if (!$reportclass->check_permissions($USER->id, $context)) {
    print_error('badpermissions', 'block_configurable_reports');
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

$PAGE->requires->jquery();

$reportclass->create_report();

echo '<link rel="stylesheet" type="text/css" href="style/dash.css">';	
//echo $OUTPUT->header();

//echo '<script>
//function myFunction() {
//  var copyText = document.getElementById("ecopyInput");
//  copyText.select();
//  document.execCommand("Copy");
//  alert("Copied the text: " + copyText.value);
//}
//</script>';

/* Commented out temporarily 4-8-19

echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script>
function copyToClipboard(element) {
  var $temp = $("<input>");
  $("body").append($temp);
  $temp.val($(element).text()).select();
  document.execCommand("copy");
    alert("Copied the text: " + $(element).text());
  $temp.remove();
}
</script>';

*/

// Print the report HTML.
$reportclass->print_report_page($context);
