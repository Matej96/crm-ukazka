<?php

namespace App\Http\Controllers\Api;

use App\Console\Commands\MigratePlugins;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class AdminApiController extends Controller
{
    public function databaseReset(): JsonResponse
    {
        if(!App::isProduction()) {
            Artisan::call(MigratePlugins::class, [
                '--force' => true,
            ]);

            return response()->json(['message' => response_message('restore.success_text', 1, ['model' => lcfirst(__('Databáza'))])]);
        } else {
            abort(500, 'Nie je povolené!');
        }
    }
}
