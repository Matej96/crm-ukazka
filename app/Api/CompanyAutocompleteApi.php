<?php

namespace App\Api;

use Illuminate\Support\Facades\Http;
use Mtownsend\XmlToArray\XmlToArray;

class CompanyAutocompleteApi {

    const API_RUZ_URL = "https://www.registeruz.sk/cruz-public/domain/suggestion/search";
    const API_RUZ_URL_API = "https://www.registeruz.sk/cruz-public/api/uctovna-jednotka";

    const API_FS_URL = 'https://iz.opendata.financnasprava.sk/api/data/ds_dphs/search';
    const API_FS_KEY = 'eIHm42p7MCLPxzpZQ1pt3VfS73cSgAYc4PtylJ4QL1siArR2Fx8SCgeSfXS4MDBgQ1XgM7xLUASayzNbG9kPdGIPZLVslDEqGjYMRENa0BGjLvjYVYCpfIle1b8BQBQVKodBK97wdgi1Er74uHoDwkjbnBx8LL8O7OS8FMETVJHRHNIV9anc5afZhJPqR0kS8fIWgoXzZiIw2VtVJwUih3NLJwpn6JHq0ZMrsQOIRuLLnXFlT3Gf34ZaIt';

    const API_ORSR_URL = "http://api.register-firiem.sk/Service.svc/";

    public function getCompanyByQuery($query, $type, $country_code = 'sk') {

        $query = rawurlencode(str_replace(' ', '', $query));

        //business_id
        //headquarter
        //business_name
        //business_tax
        if($country_code == 'sk') {
            return $this->getCompanyByQuerySK($query, $type);
        } elseif($country_code == 'cz') {
            return $this->getCompanyByQueryCZ($query, $type);
        } else {
            return [];
        }
    }

    private function getCompanyByQuerySK($query, $type) {
        $finalData = [];
        foreach (Http::withoutVerifying()->get(self::API_RUZ_URL, ['query' => $query])->json() ?? [] as $company) {
            foreach ($company as $key => &$value) {
                $value = str_replace(['<b>', '</b>'], '', $value);
            }
            if(in_array($query, $company)) {
                if ($data = Http::withoutVerifying()->get(self::API_RUZ_URL_API, ['id' => $company['id']])->json()) {
                    $finalData['business_id'] = $data['ico'];
                    $finalData['headquarter'] = (isset($data['ulica']) ? ($data['ulica'] . ", ") : '') .
                        (isset($data['mesto']) ? (substr($data['mesto'], 0, strpos($data['mesto'], ' - ') ?: strlen($data['mesto'])) . " ") : '') .
                        (isset($data['psc']) ? ($data['psc']) : '');
                    if(isset($data['nazovUJ'])) { $finalData['business_name'] = $data['nazovUJ']; }

                    if(isset($data['dic'])) {
                        $finalData['business_tax'] = $data['dic'];
                        if (Http::withoutVerifying()->withHeaders(['key' => self::API_FS_KEY])->get(self::API_FS_URL, [
                            'page' => 1,
                            'column' => 'ic_dph',
                            'search' => $icdph = 'SK' . $data['dic']
                        ])->ok()) {
                            $finalData['business_vat'] = $icdph;
                        }
                    }
                }
            }
        }

        if(empty($finalData) && $type == 'business_id') {
            $response = Http::withoutVerifying()->get(self::API_ORSR_URL . "ICO/" . $query);
            try {
                $data = simplexml_load_string(htmlspecialchars($response->body()));
            } catch (\ErrorException $e) {
                $data = simplexml_load_string($response->body());
            }
            $finalData['business_id'] = $query;
            if((string) $data->Zaznam->Sidlo != '') $finalData['headquarter'] = (string) $data->Zaznam->Sidlo;
            if((string) $data->Zaznam->ObchodneMeno != '') $finalData['business_name'] = (string) $data->Zaznam->ObchodneMeno;
        }

        return $finalData;
    }

}
