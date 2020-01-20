<?php

namespace local_tlcore\db\factories;

use local_tlcore\db\factory;
use stdClass;

defined('MOODLE_INTERNAL') || die;

class user extends factory {
    protected function generate(?stdClass $defaults = null): stdClass {
        $defaults = $defaults ? $defaults : new stdClass();

        $tomerge = [
            'auth'         => 'manual',
            'username'     => $this->faker->userName,
            'password'     => hash_internal_user_password('secret'),
            'idnumber'     => $this->faker->uuid,
            'firstname'    => $this->faker->firstName,
            'lastname'     => $this->faker->lastName,
            'email'        => $this->faker->email,
            'city'         => $this->faker->city,
            'country'      => strtoupper($this->faker->countryCode),
            'lang'         => strtolower($this->faker->languageCode),
            'calendartype' => 'gregorian',
            'timezone'     => get_user_timezone($tz = 99)
        ];

        return (object) array_merge($tomerge, (array) $defaults);
    }
}