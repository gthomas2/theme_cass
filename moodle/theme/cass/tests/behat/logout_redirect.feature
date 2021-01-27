# This file is part of Moodle - http://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
#
# Tests for log out redirects.
#
# @package    theme_cass
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@theme @theme_cass
Feature: When configured correctly, logging out will redirect a user.

  Background:
    Given the following config values are set as admin:
      | logoutredirection       | http://www.google.com | theme_cass |
      | personalmenulogintoggle | 0                     | theme_cass |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@example.com |

  @javascript
  Scenario: Logging out takes you to redirect site.
    Given I log in as "student1"
    When I log out
    Then the current url contains "www.google.com"