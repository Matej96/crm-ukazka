<?php

namespace App\Api;

use Libraries\FinStatAPIPHPClient\FinStatApi\FinstatApi;
use Requests_Exception;

class CompanyFinstatAutocompleteApi {
    private FinstatApi $finstatApi;

    public function __construct() {
        $this->finstatApi = new FinstatApi(
            'https://www.finstat.sk/api/',
            env('FINSTAT_API_KEY'),
            env('FINSTAT_PRIVATE_KEY'),
            'Api',
            'Api'
        );
    }

    public function getCompanyByIco($ico, $legalForm) {
        try {
            $data = $this->finstatApi->Request($ico, "extended", true);

            if (($legalForm === 'natural_person' && $data->LegalFormCode > 110) ||
                ($legalForm === 'legal_person' && $data->LegalFormCode < 110)) {
                return [];
            }

            return $this->formatData($data);
        } catch (Requests_Exception $e) {
            return [];
        }
    }

    public function formatData($data)
    {
        if ($data->LegalFormCode <= 110) {
            if (!is_null($data->RegisterNumberText)) {
                preg_match('/^(.+?),\s*Číslo živnostenského registra:\s*(.+)$/', $data->RegisterNumberText, $matches);
                list($business_register_group, $business_register_id) = array_slice($matches, 1, 3);
            } else {
                list($business_register_group, $business_register_id) = ['', ''];
            }
        } else {
            if (!is_null($data->RegisterNumberText)) {
                preg_match('/^(.+?),\s*oddiel:\s*(.+?),\s*vložka\s*č\.\s*(.+)$/', $data->RegisterNumberText, $matches);
                list($business_register_group, $business_register_subgroup, $business_register_id) = array_slice($matches, 1, 3);
            } else {
                list($business_register_group, $business_register_subgroup, $business_register_id) = ['', '', ''];
            }
        }

        $street = ($data->Street ?? '') . (($data->Street ?? '') && ($data->StreetNumber ?? '') ? ' ' : '') . ($data->StreetNumber ?? '');
        $formatedData = [
            'business_id' => $data->Ico,
            'business_name' => $data->Name,
            'business_tax' => $data->Dic,
            'business_vat' => $data->IcDPH,
            'business_register_group' => $business_register_group,
            'business_register_subgroup' => $business_register_subgroup ?? null,
            'business_register_id' => $business_register_id,
            'business_address_street' => $street,
            'business_address_city' => $data->City,
            'business_address_zip' => $data->ZipCode,
        ];

        return $formatedData;
    }

    public function getCompanyByName($name, $legalForm) {
        try {
            $data = $this->finstatApi->RequestAutoComplete($name, true)->Results;

            $formattedData = [];
            for ($i = 0; $i < min(5, count($data)); $i++) {
                $ico = $data[$i]->Ico;
                $companyData = $this->finstatApi->Request($ico, "extended", true);

                if (($legalForm === 'natural_person' && $companyData->LegalFormCode > 110) ||
                    ($legalForm === 'legal_person' && $companyData->LegalFormCode < 110)) {
                    continue;
                }

                $formattedData []= $this->formatData($companyData);
            }

            return $formattedData;
        } catch (Requests_Exception $e) {
            return [];
        }
    }
}
