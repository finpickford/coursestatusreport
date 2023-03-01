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
 * File to show report to administrator.
 * @package    local_coursestatusreport
 * @copyright  2023 Fin Pickford
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir .'/adminlib.php');
require_once($CFG->dirroot . '/local/coursestatusreport/classes/forms/report_form.php');
require_once($CFG->dirroot . '/local/coursestatusreport/lib.php');

require_login();

// The users in the LMS.
$users = coursestatusreport_get_users();

// Setup the page.
$systemcontext = context_system::instance();
$heading = get_string('title', 'local_coursestatusreport');

$PAGE->set_url('/local/coursestatusreport/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title($heading);
$PAGE->set_heading($heading);

// Security.
if (!is_siteadmin($USER)) {
    throw new moodle_exception('nopermission', 'local_coursestatusreport');
}

$params = [
    'users' => $users,
];

$mform = new report_form(null, $params);

// Process the form.
if ($mform->is_cancelled()) {
    // If the form is cancelled, take the user back to the reports page.
    $url = new moodle_url('/admin/category.php?category=reports');
    redirect($url);
} else if ($formdata = $mform->get_data()) {
    $reportdata = coursestatusreport_get_data($formdata);
}

// Page Output.
echo $OUTPUT->header();

echo $OUTPUT->container_start('', 'coursestatusreport');

$mform->display();

// If there is data for that user, output it to the interface.
// Else, output a warning that there is no data for this user.
if (isset($reportdata)) {
    if ($reportdata) {
        $table = new html_table;
        $table->id = 'coursestatusreport-table';
        $table->attributes['class'] = 'table table-striped table-condensed';

        // Create the table headings.
        $headers = [
            ucfirst(get_string('course', 'local_coursestatusreport')),
            ucfirst(get_string('completionstatus', 'local_coursestatusreport')),
            ucfirst(get_string('completiontime', 'local_coursestatusreport')),
        ];

        $table->head = $headers;

        foreach ($reportdata as $r) {
            // Create the course URL link.
            $courseurl = new moodle_url('/course/view.php', array('id' => $r->id));
            $courselink = html_writer::link($courseurl, ucfirst($r->fullname));

            // Convert the completion status to the users language settings.
            $completionstatus = coursestatusreport_convert_status($r->completionstatus);

            $cell1 = new html_table_cell($courselink);
            $cell1->style = 'width:50%';
            $cell2 = new html_table_cell($completionstatus);
            $cell2->style = 'width:25%';
            $cell3 = new html_table_cell($r->completiontime);
            $cell3->style = 'width:25%';

            $row = new html_table_row(array(
                $cell1,
                $cell2,
                $cell3,
            ));

            $table->data[] = $row;
        }

        echo html_writer::table($table);

    } else {
        echo $OUTPUT->notification(get_string('noreportdata', 'local_coursestatusreport'), 'warning');
    }
} else {
    echo get_string('reportdesc', 'local_coursestatusreport');
}

echo $OUTPUT->container_end();
echo $OUTPUT->footer();
