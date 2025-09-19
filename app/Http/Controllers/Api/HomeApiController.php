<?php

namespace App\Http\Controllers\Api;

use App\Api\CompanyAutocompleteApi;
use App\Api\CompanyFinstatAutocompleteApi;
use App\Api\UserAutocompleteApi;
use App\Http\Controllers\Controller;
use App\Services\XlsxDataProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeApiController extends Controller
{
    /**
     * Get a listing of the table.
     *
     * @param string $plugin
     * @param string $table
     * @param bool $asDatatable
     * @return JsonResponse
     */
    public function get(string $plugin, string $table, bool $asDatatable = false): JsonResponse {
        if(($class = getTableModel($plugin, $table)) && (method_exists($class, 'getTableData'))) {
            if($asDatatable) {
                return $class::getTableData(true, request()->get('filters') ?? []);
            } else {
                return response()->json(['data' => $class::getTableData(false, request()->get('filters') ?? [])->skipPaging()->make()->getData(true)['data']]);
            }
        } else {
            return response()->json();
        }
    }

    public function export(string $plugin, string $table) {
        $class = getTableModel($plugin, $table);

        if(($class) && (method_exists($class, 'getTableData'))) {
            $dt = $class::getTableData(false, request()->get('filters') ?? []);

            $cols = collect($dt->request->columns())
                ->where('visible', '!=', 'false')
                ->pluck('name')->whereNotNull();

            $data = [$i = 0 => []];
            $data[++$i] = $class::getTableFields()->only($cols)->pluck('label')->toArray();
            foreach ($dt->skipPaging()->make()->getData(true)['data'] as $row) {
                $data[++$i] = collect($row)->only($cols)->sortBy(function($item, $key) use ($cols) {
                    return key($cols->intersect([$key])->toArray());
                })->toArray();
            }

            $xlsx_path = XlsxDataProcessor::generate([__(Str::studly(Str::plural($table))) => $data], 'Export '. __(Str::studly(Str::plural($table))));

            return response()->download($xlsx_path, basename($xlsx_path))->deleteFileAfterSend();
        } else {
            abort(404);
        }
    }

    /**
     * Get a single record of the table.
     *
     * @param string $plugin
     * @param string $table
     * @param Model|int|string $model
     * @return JsonResponse
     */
    public function single(string $plugin, string $table, Model|int|string $model, array $filters = []): JsonResponse {
        if(($class = getTableModel($plugin, $table)) && (method_exists($class, 'getTableData'))) {
            if(!$model instanceof Model) {$model = $class::findOrFail($model);}

            if($data = $class::getTableData(false, array_merge($filters, request()->get('filters') ?? []))
                ->filter(fn ($query) => $query->where('id', $model->id))->skipPaging()->make()->getData(true)['data'][0] ?? []) {
                return response()->json($data);
            } else {
                abort(404);
            }
        } else {
            return response()->json();
        }
    }

    /**
     * Get a listing of the table fields.
     *
     * @param string $plugin
     * @param string $table
     * @param bool $asCollection
     * @return JsonResponse
     */
    public function fields(string $plugin, string $table, bool $asCollection = false, $data = []): JsonResponse {
//        if(Str::startsWith($table, 'p.')) {
//            $class = getPluginTableModel(Str::replaceFirst('p.', '', $table));
//        } else {
//            $class = getTableModel($table);
//        }

        $class = getTableModel($plugin, $table);

        if (($class) && (method_exists($class, 'getTableFields'))) {
            $fields = $class::getTableFields($data['filters'] ?? request()->get('filters') ?? []);
            return response()->json($asCollection ? $fields : $fields->toArray());
        } else {
            return response()->json();
        }
    }

    /**
     * Get a listing of the table form fields for creation.
     *
     * @param string $plugin
     * @param string $table
     * @param bool $asCollection
     * @param array $filters
     * @return JsonResponse
     */
    public function createFormFields(string $plugin, string $table, bool $asCollection = false, array $filters = []): JsonResponse {
        if(($class = getTableModel($plugin, $table)) && (method_exists($class, 'getFormFields'))) {
            $fields = $class::getFormFields(null, array_merge($filters, ['only_create' => true]));

            return response()->json($asCollection ? $fields : $fields->toArray());
        } else {
            return response()->json();
        }
    }

    /**
     * Get a listing of the table form fields for update.
     *
     * @param string $plugin
     * @param string $table
     * @param Model|int|string $model
     * @param bool $asCollection
     * @param array $filters
     * @return JsonResponse
     */
    public function showFormFields(string $plugin, string $table, Model|int|string $model, bool $asCollection = false, array $filters = []): JsonResponse {
        if(($class = getTableModel($plugin, $table)) && (method_exists($class, 'getFormFields'))) {
            if(!$model instanceof Model) {$model = $class::findOrFail($model);}

            $fields = $class::getFormFields($model, array_merge($filters, ['only_show' => true]));

            return response()->json($asCollection ? $fields : $fields->toArray());
        } else {
            return response()->json();
        }
    }

    /**
     * Get a listing of the table form fields for update.
     *
     * @param string $plugin
     * @param string $table
     * @param Model|int|string $model
     * @param bool $asCollection
     * @param array $filters
     * @return JsonResponse
     */
    public function editFormFields(string $plugin, string $table, Model|int|string $model, bool $asCollection = false, array $filters = []): JsonResponse {
        if(($class = getTableModel($plugin, $table)) && (method_exists($class, 'getFormFields'))) {
            if(!$model instanceof Model) {$model = $class::findOrFail($model);}

            $fields = $class::getFormFields($model, array_merge($filters, ['only_edit' => true]));

            return response()->json($asCollection ? $fields : $fields->toArray());
        } else {
            return response()->json();
        }
    }

    /**
     * Get a listing of the table form field options.
     *
     * @param string $plugin
     * @param string $table
     * @param string $field
     * @param bool $asCollection
     * @param array $filters
     * @return JsonResponse
     */
    public function formFieldOptions(string $plugin, string $table, string $field, bool $asCollection = false, array $filters = []): JsonResponse {
        if(($class = getTableModel($plugin, $table)) && (method_exists($class, 'getFormFieldOptions'))) {
            $fields = $class::getFormFieldOptions($field, $filters);

            return response()->json($asCollection ? $fields : $fields->toArray());
        } else {
            return response()->json();
        }
    }

    /**
     * Change the theme of the app.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setTheme(Request $request) {
        $request->validate(['theme' => 'required']);
        session()->put('theme', $request->get('theme'));

        return response()->json(['success' => true]);
    }

    public function setSidebar(Request $request) {
        $request->validate(['sidebar' => 'required']);
        session()->put('sidebar', $request->get('sidebar'));

        return response()->json(['success' => true]);
    }

    /**
     * Change the locale of the app.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setLocale(Request $request) {
        $request->validate(['lang' => 'required']);
        App::setLocale($request->get('lang'));
        session()->put('lang', $request->get('lang'));
        if(auth()->id()) {
            auth()->user()->last_locale = $request->get('lang');
            auth()->user()->save();
        }

        return response()->json(['success' => true]);
    }

    public function file(Request $request, $path) {
        //TODO custom validovanie kto moze/nemoze vidiet
        if ($rangeRequestHeader = $request->header('Range')) {
            $stream = Storage::readStream($path);
            $fileSize = Storage::size($path);

            $fileSizeMinusOneByte = $fileSize - 1; //because it is 0-indexed. https://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.16
            list($param, $rangeHeader) = explode('=', $rangeRequestHeader);
            if (strtolower(trim($param)) !== 'bytes') {
                abort(400, "Invalid byte range request"); //Note, this is not how https://stackoverflow.com/a/29997555/470749 did it
            }
            list($from, $to) = explode('-', $rangeHeader);
            if ($from === '') {
                $end = $fileSizeMinusOneByte;
                $start = $end - intval($from);
            } elseif ($to === '') {
                $start = intval($from);
                $end = $fileSizeMinusOneByte;
            } else {
                $start = intval($from);
                $end = intval($to);
            }
            $length = $end - $start + 1;
            $httpStatusCode = 206;

            $responseHeaders = [
                'Content-Type' => Storage::mimeType($path),
                'Content-Range' => sprintf('bytes %d-%d/%d', $start, $end, $fileSize)
            ];

            return response()->stream(function() use ($stream, $start, $length) {
                fseek($stream, $start, SEEK_SET);
                echo fread($stream, $length);
                fclose($stream);
            }, $httpStatusCode, $responseHeaders);
        } else {
            if (Storage::exists($path)) {
                return Storage::response($path);
            } else {
                abort(404, __('Not Found'));
            }
        }
    }

    /**
     * Get a company data array from company identifier.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function companyAutocomplete(Request $request): JsonResponse
    {
        //TODO validation on column
        //TODO rozdelit adresu na street, zip city
        //TODO prementovat keys
        $request->validate([
//            'column'  => ['required', Rule::in(['business_id', 'business_tax', 'business_name'])],
            'value' => 'required'
        ]);

        $value = str_replace(' ', '', $request->get('value'));

//        if(($company = Company::withTrashed()->where($request->get('column'), $value)->first()) && ($company->id != $request->get('company_id'))) {
//
//            $response = ["warning" => "Spoločnosť s takýmto ".(getColumns('companies')->get($request->get('column')) ?? $request->get('column'))." už existuje."];
//
//            if($company->is_author || Permissions::permissionCheck('read_all_clients')) {
//                $response['company_id'] = $company->id;
//                if($company->trashed()) {
//                    $response = array_merge($response, [
//                        'is_trashed'    => $company->trashed(),
//                        'is_author'     => $company->is_author,
//                        'author_name'   => $company->author->name
//                    ]);
//                }
//            }
//
//            return response()->json($response);
//        }

        return response()->json(["data" => (new CompanyAutocompleteApi())->getCompanyByQuery($value, $request->get('column'))]);
    }

    /**
     * Get a company data array from company identifier.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function companyFinstatAutocomplete(Request $request): JsonResponse
    {
        if(auth()->guest() && !$request->hasValidSignature()) {
            abort(404);
        }

        $request->validate([
//            'column'  => ['required', Rule::in(['business_id', 'business_tax', 'business_name'])],
            'value' => 'required'
        ]);

        $value = str_replace(' ', '', $request->get('value'));
        $column = $request->get('column');
        if ($column === 'business_id') {
            return response()->json(["data" => (new CompanyFinstatAutocompleteApi())->getCompanyByIco($value, $request->get('legal_form'))]);
        } else {
            return response()->json(["data" => (new CompanyFinstatAutocompleteApi())->getCompanyByName($value, $request->get('legal_form'))]);
        }
    }

    /**
     * Get a user birth data array from user identifier.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function userBirthAutocomplete(Request $request): JsonResponse
    {
        if(auth()->guest() && !$request->hasValidSignature()) {
            abort(404);
        }

        $request->validate([
            'value' => 'required'
        ]);

        $data = [];
        if($birth_id = UserAutocompleteApi::formatBirthId($request->get('value'))) $data['birth_id'] = $birth_id;
        if($gender = UserAutocompleteApi::getGenderFromBirthId($birth_id)) $data['gender'] = $gender;
        if($birth_date = UserAutocompleteApi::getBirthDateFromBirthId($birth_id)) $data['birth_date'] = $birth_date;
        return response()->json(["data" => $data]);
    }
}
