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
 * File to display form details.
 * @package    local_coursestatusreport
 * @copyright  2023 Fin Pickford
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

/**
 * Class to create a form to show all users on the LMS.
 */
class report_form extends moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        $users = $this->_customdata['users'];

        $options = [
            -1 => get_string('pleaseselect', 'local_coursestatusreport')
        ];
        foreach ($users as $user) {
            $options[$user->id] = fullname($user);
        }
        $select = $mform->addElement('select', 'userid',
            get_string('users', 'local_coursestatusreport'), $options);

        $elemarray = array();
        $elemarray[] =& $mform->createElement('submit', 'submit', get_string('showreport', 'local_coursestatusreport'));
        $elemarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));

        $mform->addGroup($elemarray, 'buttonar', '', array(' '), false);
    }

    /**
     * Form validation.
     * @param array $data the forms data.
     * @param array $files the forms files.
     * @return array $errors the errors to display.
     */
    public function validation($data, $files) {
        $errors = array();

        // Cannot choose 'Please select option'.
        if (isset($data['userid'])) {
            if ($data['userid'] == -1) {
                $errors["userid"] = ucfirst(get_string('requiredfield', 'local_coursestatusreport'));
            }
        }

        return $errors;
    }
}
