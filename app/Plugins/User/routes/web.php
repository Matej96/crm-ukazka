<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'lang', 'client'])->group(function () {
//plugin prefix
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('division/{division}/division-product/{division_product}/group/create', [App\Plugins\User\app\Http\Controllers\GroupController::class, 'createProductGroup'])->name('division.division-product.group.create');
        Route::post('division/{division}/division-product/{division_product}/group/store', [App\Plugins\User\app\Http\Controllers\GroupController::class, 'setProductGroup'])->name('division.division-product.group.store');
        Route::post('division/{division}/division-product/{division_product}/group/cancel', [App\Plugins\User\app\Http\Controllers\GroupController::class, 'cancelProductGroup'])->name('division.division-product.group.cancel');
        Route::get('division/{division}/division-product/tree', [App\Plugins\User\app\Http\Controllers\DivisionProductController::class, 'tree'])->name('division.division-product.tree');
        Route::resource('division.division-product', App\Plugins\User\app\Http\Controllers\DivisionProductController::class)->except(['index', 'create', 'store', 'show']);

        Route::get('candidate/{candidate}/create-broker', [App\Plugins\User\app\Http\Controllers\BrokerController::class, 'createFromCandidate'])->name('candidate.create-broker');
//        Route::get('candidate/create-from-broker/{broker}', [App\Plugins\User\app\Http\Controllers\CandidateController::class, 'createFromBroker'])->name('candidate.create-from-broker');
//        Route::post('candidate/store-candidate/{broker}', [App\Plugins\User\app\Http\Controllers\CandidateController::class, 'storeFromBroker'])->name('candidate.store-from-broker');
        Route::resource('candidate', \App\Plugins\User\app\Http\Controllers\CandidateController::class)->except(['index', 'destroy']);
        Route::middleware(['null_nested'])->group(function () {
            Route::get('division/{division}/candidate/{candidate}/create-broker', [App\Plugins\User\app\Http\Controllers\BrokerController::class, 'createFromCandidate'])->name('division.candidate.create-broker');
//            Route::post('division/{division}/candidate/store-candidate/{broker}', [App\Plugins\User\app\Http\Controllers\CandidateController::class, 'storeFromBroker'])->name('division.candidate.store-from-broker');
            Route::resource('division.candidate', \App\Plugins\User\app\Http\Controllers\CandidateController::class)->except(['index', 'destroy']);
        });

        Route::resource('broker.broker-relation', App\Plugins\User\app\Http\Controllers\BrokerRelationController::class)->except(['index', 'show']);
        Route::middleware(['null_nested'])->group(function () {
            Route::resource('division.broker.broker-relation', App\Plugins\User\app\Http\Controllers\BrokerRelationController::class)->except(['index', 'show']);
        });
        Route::resource('broker.broker-sector', App\Plugins\User\app\Http\Controllers\BrokerSectorController::class)->except(['index', 'create', 'store']);
        Route::middleware(['null_nested'])->group(function () {
            Route::resource('division.broker.broker-sector', App\Plugins\User\app\Http\Controllers\BrokerSectorController::class)->except(['index', 'create', 'store']);
        });
        Route::get('user/{user}/assignation', [App\Plugins\User\app\Http\Controllers\BrokerController::class, 'assignation'])->name('user.assignation');
        Route::post('user/{user}/assign', [App\Plugins\User\app\Http\Controllers\BrokerController::class, 'assign'])->name('user.assign');
        Route::get('broker/{broker}/products/{format}', [App\Plugins\User\app\Http\Controllers\BrokerController::class, 'productsTree'])->name('broker.products.tree');
        Route::resource('broker', App\Plugins\User\app\Http\Controllers\BrokerController::class);
        //TODO middleware ktory by checkoval ci $broker->division == $division
        Route::get('division/{division}/broker/tree', [App\Plugins\User\app\Http\Controllers\BrokerController::class, 'tree'])->name('division.broker.tree');
        Route::middleware(['null_nested'])->group(function () {
            Route::get('division/{division}/user/{user}/assignation', [App\Plugins\User\app\Http\Controllers\BrokerController::class, 'assignation'])->name('division.user.assignation');
            Route::post('division/{division}/user/{user}/assign', [App\Plugins\User\app\Http\Controllers\BrokerController::class, 'assign'])->name('division.user.assign');
            Route::get('division/{division}/broker/{broker}/products/{format}', [App\Plugins\User\app\Http\Controllers\BrokerController::class, 'productsTree'])->name('division.broker.products.tree');
            Route::resource('division.broker', \App\Plugins\User\app\Http\Controllers\BrokerController::class)->except(['index']);
        });

        Route::get('division/{division}/group/tree', [App\Plugins\User\app\Http\Controllers\GroupController::class, 'tree'])->name('division.group.tree');
        Route::resource('division.group', App\Plugins\User\app\Http\Controllers\GroupController::class)->except(['index', 'show']);
        Route::resource('division', App\Plugins\User\app\Http\Controllers\DivisionController::class);
    });
    Route::prefix('api/home/user')->name('api.user.')->group(function () {
        Route::post('division/{division}/division-product/status', [App\Plugins\User\app\Http\Controllers\Api\DivisionProductApiController::class, 'setProductStatus'])->name('division.division-product.set-status');

        Route::post('candidate/{candidate}/email/form', [App\Plugins\User\app\Http\Controllers\Api\CandidateApiController::class, 'candidateFormEmail'])->name('candidate.email.form');
        Route::post('candidate/{candidate}/email/documentation', [App\Plugins\User\app\Http\Controllers\Api\CandidateApiController::class, 'candidateDocumentationEmail'])->name('candidate.email.documentation');
        Route::post('candidate/{candidate}/extend-validity', [App\Plugins\User\app\Http\Controllers\Api\CandidateApiController::class, 'extendValidity'])->name('candidate.extend-validity');

        Route::get('broker/{broker}/sector/{sector}/get-current', [\App\Plugins\User\app\Http\Controllers\Api\BrokerSectorApiController::class, 'getCurrentSectors'])->name('broker.sector.get-current');

        Route::post('broker/{broker}/user/assign', [\App\Plugins\User\app\Http\Controllers\Api\BrokerApiController::class, 'assignUser'])->name('broker.user.assign');
        Route::post('broker/{broker}/user/unassign', [\App\Plugins\User\app\Http\Controllers\Api\BrokerApiController::class, 'unassignUser'])->name('broker.user.unassign');
        Route::put('broker/{broker}/active', [\App\Plugins\User\app\Http\Controllers\Api\BrokerApiController::class, 'setActive'])->name('broker.active');
        Route::post('broker/{broker}/change-career-status',  [\App\Plugins\User\app\Http\Controllers\Api\BrokerApiController::class, 'changeCareerStatus'])->name('broker.change-career-status');
        Route::get('broker/{broker}/get-products-by-date/{date}',  [App\Plugins\User\app\Http\Controllers\Api\BrokerApiController::class, 'getBrokerProductsByDate'])->name('broker.get-products-by-date');
        Route::get('broker/{broker}/get-guarantor-by-date/{date}',  [App\Plugins\User\app\Http\Controllers\Api\BrokerApiController::class, 'getGuarantorByDate'])->name('broker.get-guarantor-by-date');
        Route::get('broker/get-short-name-by-career-id/{career_id}', [App\Plugins\User\app\Http\Controllers\Api\BrokerApiController::class, 'getBrokerShortNameByCareerId'])->name('broker.get-short-name-by-career-id');

        Route::post('broker/{broker}/partner/assign', [\App\Plugins\User\app\Http\Controllers\Api\BrokerApiController::class, 'assignBrokerPartner'])->name('broker.partner.assign');
        Route::post('broker/{broker}/partner/{brokerPartner}/edit', [\App\Plugins\User\app\Http\Controllers\Api\BrokerApiController::class, 'editBrokerPartner'])->name('broker.partner.edit');
        Route::post('broker/{broker}/partner/{brokerPartner}/destroy', [\App\Plugins\User\app\Http\Controllers\Api\BrokerApiController::class, 'destroyBrokerPartner'])->name('broker.partner.destroy');

//        Route::get('division/{division}/get-available-code', [App\Plugins\User\app\Http\Controllers\Api\DivisionApiController::class, 'getAvailableCode'])->name('division.get-available-code');
        Route::get('division/{division}/get-available-broker-code', [App\Plugins\User\app\Http\Controllers\Api\DivisionApiController::class, 'getAvailableBrokerCode'])->name('division.get-available-broker-code');
    });
});
Route::middleware(['signed', 'lang'])->group(function () {
    Route::prefix('signed/user')->name('signed.user.')->group(function () {
        Route::get('candidate/{candidate}/form', [App\Plugins\User\app\Http\Controllers\CandidateController::class, 'form'])->name('candidate.form');
        Route::match(['PUT', 'PATCH'], 'candidate/{candidate}/confirm', [App\Plugins\User\app\Http\Controllers\CandidateController::class, 'confirm'])->name('candidate.confirm');
    });
});
