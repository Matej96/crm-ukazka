<?php

namespace App\Api;

use libphonenumber\PhoneNumberFormat as libPhoneNumberFormat;
use Propaganistas\LaravelPhone\PhoneNumber;

class UserAutocompleteApi {

    public static function formatBirthId($birth_id): ?string
    {
        return preg_replace('/[^0-9.]+/', '', $birth_id);
    }

    public static function getBirthDateFromBirthId($birth_id): ?string
    {
        $birth_id = self::formatBirthId($birth_id);

        // Skontrolujte formát rodného čísla
        if (!preg_match('/^\d{6}\d{4}$/', $birth_id)) {
            return null; //"Neplatný formát rodného čísla";
        }

        // Rozdelenie rodného čísla na časť dátumu narodenia a ostatné informácie
        $dateParts = substr($birth_id, 0, 6);

        // Rozdelenie dátumu na deň, mesiac a rok
        $den = substr($dateParts, 4, 2);
        $mesiac = substr($dateParts, 2, 2);
        $rok = substr($dateParts, 0, 2);

        // Priradte 50 k mesiacu narodenia pre ženy
        if ($mesiac > 50) {
            $mesiac -= 50;
        }


        // Ak je rodné číslo pridelené po roku 1954, pridaj 2000, inak pridaj 1900
        $rok = ($rok >= 54) ? (1900 + $rok) : (2000 + $rok);

        // Sestavenie dátumu narodenia vo formáte YYYY-MM-DD
        return sprintf("%04d-%02d-%02d", $rok, $mesiac, $den);
    }

    public static function getGenderFromBirthId($birth_id): ?string
    {
        $birth_id = self::formatBirthId($birth_id);

        return in_array((strlen($birth_id) >= 3) ? $birth_id[2] : 0, [5, 6]) ? 'female' : 'male';
    }

    public static function formatPhone($phone): string|PhoneNumber
    {
        return phone($phone, app()->getLocale(), libPhoneNumberFormat::E164);
    }
}
