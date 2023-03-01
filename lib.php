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
 * Lib file to handle functions for plugin.
 * @package    local_coursestatusreport
 * @copyright  2023 Fin Pickford
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to get all users on the LMS.
 * @return object an object of the users on the LMS.
 */
function coursestatusreport_get_users() {
    global $DB;

    return $DB->get_records('user', array('deleted' => 0));
}

/**
 * Function to get the users courses and their status.
 * @param object $data the formdata object.
 * @return object the database results.
 */
function coursestatusreport_get_data($data) {
    global $DB;

    $userid = $data->userid;

    // Get all the courses a user is enrolled on and their current status.
    $sql = "SELECT c.id,
                   c.fullname,
                   IF(cc.timecompleted IS NOT NULL, 'completed',
                   IF(cc.timestarted IS NULL OR cc.timestarted = 0, 'notstarted', 'inprogress')) as 'completionstatus',
                   IF(cc.timecompleted = 0, '', FROM_UNIXTIME(cc.timecompleted, '%d/%m/%Y %H:%i')) AS 'completiontime'
              FROM {course_completions} cc
              JOIN {course} c ON cc.course = c.id
              JOIN {enrol} e ON c.id = e.courseid
              JOIN {user_enrolments} ue ON cc.userid = ue.userid AND e.id = ue.enrolid
             WHERE cc.userid = ?
                   AND e.status = 0
                   AND ue.status = 0
          GROUP BY c.id, ue.userid
          ORDER BY c.fullname";

    return $DB->get_records_sql($sql, array($userid));
}

/**
 * Function to convert the completion status to the users language.
 * @param string $completionstatus the completion status.
 * @return string $completionstatus the converted completion status.
 */
function coursestatusreport_convert_status($completionstatus) {
    switch ($completionstatus) {
        case 'completed':
            $completionstatus = get_string('completed', 'local_coursestatusreport');
            break;
        case 'notstarted':
            $completionstatus = get_string('notstarted', 'local_coursestatusreport');
            break;
        case 'inprogress':
            $completionstatus = get_string('inprogress', 'local_coursestatusreport');
            break;
    }

    return $completionstatus;
}
