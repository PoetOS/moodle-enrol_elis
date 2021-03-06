<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2016 Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    enrol_elis
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2008-2016 Remote-Learner.net Inc (http://www.remote-learner.net)
 * @author     Brent Boghosian <brent.boghosian@remote-learner.net>
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_elis_upgrade($oldversion = 0) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();
    $result = true;

    if ($result && $oldversion < 2015102201) {
        // Must update all enrol_elis instances with new customint2, if set.
        if (get_config('enrol_elis', 'unenrol_from_course_catalog') == '1') {
            $sql = 'UPDATE {enrol} SET customint2 = 1 WHERE enrol = "elis"';
            $DB->execute($sql);
        }
        upgrade_plugin_savepoint($result, '2015102201', 'enrol', 'elis');
    }

    if ($result && $oldversion < 2016052301) {
        $sql = "SELECT DISTINCT e1.courseid
                  FROM mdl_enrol e1
                 WHERE e1.enrol = 'elis'
                       AND EXISTS (SELECT *
                                     FROM mdl_enrol e2
                                    WHERE e2.enrol = 'elis'
                                          AND e1.courseid = e2.courseid
                                          AND e1.id != e2.id)";
        $reqfixes = $DB->get_recordset_sql($sql);
        if ($reqfixes && $reqfixes->valid()) {
            $plugin = enrol_get_plugin('elis');
            foreach ($reqfixes as $reqfix) {
                $plugin->get_or_create_instance($DB->get_record('course', ['id' => $reqfix->courseid]));
            }
        }
        unset($reqfixes);
        upgrade_plugin_savepoint($result, '2016052301', 'enrol', 'elis');
    }

    return $result;
}
