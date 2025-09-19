<?php

namespace App\Plugins\User\app\Http\Controllers;

use App\Http\Controllers\Api\HomeApiController;
use App\Http\Controllers\Controller;
use App\Plugins\Product\app\Models\Product;
use App\Plugins\Product\app\Models\ProductCategory;
use App\Plugins\Product\app\Models\ProductType;
use App\Plugins\Product\app\Models\ProductVersion;
use App\Plugins\System\app\Models\User;
use App\Plugins\User\app\Http\Controllers\Api\BrokerApiController;
use App\Plugins\User\app\Http\Requests\BrokerStoreRequest;
use App\Plugins\User\app\Http\Requests\BrokerUpdateRequest;
use App\Plugins\User\app\Models\Broker;
use App\Plugins\User\app\Models\Candidate;
use App\Plugins\User\app\Models\Division;
use App\Plugins\User\app\Models\DivisionProduct;
use App\Plugins\User\app\Models\GroupProduct;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BrokerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['can:user.broker.create'])->only(['create', 'createFromCandidate', 'store', 'assignation', 'assign']);
        $this->middleware(['can:user.broker.read'])->only(['index', 'tree', 'show', 'productsTree']);
        $this->middleware(['can:user.broker.update'])->only(['edit', 'update']);
        $this->middleware(['can:user.broker.delete'])->only(['destroy']);

        $this->middleware('can_access_division')->only(['tree']);
        $this->middleware('can_access_broker')->except(['index', 'tree', 'create', 'createFromCandidate', 'store', 'assignation', 'assign']);
    }

    private static function getDivision($divisionId): Division|null
    {
        return $divisionId ? Division::withTrashed()->find($divisionId) : null;
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        return view('plugin.User::broker.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @param string $division
     * @return View
     */
    public function tree(string $division): View {
        $division = $this::getDivision($division);

        $brokers_tree = Broker::whereHas('division', function($query) use ($division) {
                $query->where('user_divisions.id', $division->id);
            })
            ->whereNull('parent_broker_id')
            ->with('self_children')->get();

        return view('plugin.User::broker.tree', [
            'brokers_tree' => $brokers_tree,
            'division' => $division,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string|null $divisionId
     * @return View
     */
    public function create(string $divisionId = null): View
    {
        $formFields = app(HomeApiController::class)->createFormFields('user', 'broker', true, [
            'user_division_id' => $divisionId,
        ])->getOriginalContent();


        return view('plugin.User::broker.create', [
            'formFields' => $formFields,
            'division' => $this::getDivision($divisionId) ?? null,
        ]);
    }

    public function createFromCandidate(Candidate $candidate, string $divisionId = null): View
    {
        $formFields = app(HomeApiController::class)->createFormFields('user', 'broker', true, [
            'user_division_id' => $divisionId,
            'assign_broker' => $candidate->broker ?? null,
            'candidate_id' => $candidate->id,
        ])->getOriginalContent();

        if($candidate->broker) {
            return view('plugin.User::broker.assignation', [
                'formFields' => $formFields,
                'division' => $this::getDivision($divisionId),
                'broker' => $candidate->broker,
            ]);
        } else {
            return view('plugin.User::broker.create', [
                'formFields' => $formFields,
                'division' => $this::getDivision($divisionId) ?? null,
                'candidate' => $candidate,
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BrokerStoreRequest $request
     * @param string|null $divisionId
     * @return RedirectResponse
     */
    public function store(BrokerStoreRequest $request, string $divisionId = null): RedirectResponse
    {
        $division = $this::getDivision($divisionId);

        try {
            $data = app(BrokerApiController::class)->store($request)->getOriginalContent();

            $new_id = $data['_old_input']['id'] ?? null;
            return redirect($division
                ? route('user.division.broker.show', [$division, $new_id])
                : route('user.broker.show', [$new_id])
            )->with(isset($data['warning']) ? 'warning' : 'status', $data['warning'] ?? ($data['message'] ?? null));
        } catch (\Exception $e) {
            return handleErrorReturn($e)->withInput();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param User $user
     * @param string|null $divisionId
     * @return View
     */
    public function assignation(User $user, string $divisionId = null): View
    {
        $formFields = app(HomeApiController::class)->createFormFields('user', 'broker', true, [
            'user_division_id' => $divisionId,
            'assign_user' => $user,
        ])->getOriginalContent();


        return view('plugin.User::broker.assignation', [
            'formFields' => $formFields,
            'division' => $this::getDivision($divisionId),
            'user' => $user,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BrokerUpdateRequest $request
     * @param User $user
     * @param string|null $divisionId
     * @return RedirectResponse
     */
    public function assign(BrokerUpdateRequest $request, User $user, string $divisionId = null): RedirectResponse
    {
        $division = $this::getDivision($divisionId);

        try {
            $data = app(BrokerApiController::class)->store($request, $user)->getOriginalContent();

            $broker_id = $data['_old_input']['id'] ?? null;
            return redirect($division
                ? route('user.division.broker.show', [$division, $broker_id])
                : route('user.broker.show', [$broker_id])
            )->with(isset($data['warning']) ? 'warning' : 'status', $data['warning'] ?? ($data['message'] ?? null));
        } catch (\Exception $e) {
            return handleErrorReturn($e)->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Broker $broker
     * @param string|null $divisionId
     * @return View
     */
    public function show(Broker $broker, string $divisionId = null): View
    {
        $formFields = app(HomeApiController::class)->showFormFields('user', 'broker', $broker, true, ['user_division_id' => optional($broker->division)->id])->getOriginalContent();
        if ($broker->candidate) {
            $candidateFormFields = app(HomeApiController::class)->createFormFields('user', 'broker', true, [
                'only_show' => true,
                'user_division_id' => $divisionId,
                'assign_broker' => null,
                'candidate_form' => 'show',
                'candidate_id' => $broker->candidate->id,
            ])->getOriginalContent();
        }

        return view('plugin.User::broker.show', [
            'formFields' => $formFields,
            'candidateFormFields' => $candidateFormFields ?? collect(),
            'division' => $this::getDivision($divisionId),
            'broker' => $broker
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Broker $broker
     * @param string|null $divisionId
     * @return View
     */
    public function edit(Broker $broker, string $divisionId = null): View
    {
        $division = $this::getDivision($divisionId);
        if (!$division) {
            $division = $broker->division;
        }

        $formFields = app(HomeApiController::class)->editFormFields('user', 'broker', $broker, true, $division ? ['user_division_id' => $division->id] : [])->getOriginalContent();

        return view('plugin.User::broker.edit', [
            'formFields' => $formFields,
            'division' => $this::getDivision($divisionId),
            'broker' => $broker
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BrokerUpdateRequest $request
     * @param Broker $broker
     * @param string|null $divisionId
     * @return RedirectResponse
     */
    public function update(BrokerUpdateRequest $request, Broker $broker, string $divisionId = null): RedirectResponse
    {
        $division = $this::getDivision($divisionId);

        try {
            $data = app(BrokerApiController::class)->update($request, $broker)->getOriginalContent();

            return redirect($division
                ? route('user.division.broker.show', [$division, $broker])
                : route('user.broker.show', [$broker])
            )->with(isset($data['warning']) ? 'warning' : 'status', $data['warning'] ?? ($data['message'] ?? null));
        } catch (\Exception $e) {
            return handleErrorReturn($e)->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Broker $broker
     * @param string|null $divisionId
     * @return RedirectResponse
     */
    public function destroy(Broker $broker, string $divisionId = null): RedirectResponse
    {
        $division = $this::getDivision($divisionId);

        try {
            $data = app(BrokerApiController::class)->destroy($broker)->getOriginalContent();
            return $division
                ? redirect()->route('user.division.show', [$division->id])->with('status', $data['message'] ?? null)
                : redirect()->route('user.broker.index')->with('status', $data['message'] ?? null);
        } catch (\Exception $e) {
            return handleErrorReturn($e)->withInput();
        }
    }

    public function productsTree(Broker $broker, string $format, string $divisionId = null): Response|View
    {
        if ($broker->division->type != 'percentage') {
            $value_broker = $broker->division->divisionBroker;
        } else {
            $value_broker = $broker;
        }

        $date = Carbon::parse(request()->input('date'));
        $adjustedDate = $date->copy()->addDay();

        $getLatestRelevantProducts = function ($query) use ($adjustedDate) {
            $products = $query->where('created_at', '<=', now())->get();

            $latestProducts = $products->groupBy('subject_id')->map(function ($productVersions) use ($adjustedDate) {
                return $productVersions->sortBy(function ($product) use ($adjustedDate) {
                    return $product->created_at->diffInSeconds($adjustedDate);
                })->first();
            });

            return $latestProducts;
        };

        $getLatestRelevantDivisionProducts = function ($divisionId, $subjectType) use ($getLatestRelevantProducts) {
            return $getLatestRelevantProducts(
                DivisionProduct::where('user_division_id', $divisionId)->where('subject_type', $subjectType)
            );
        };

        $div_categories = $getLatestRelevantDivisionProducts($value_broker->division->id, ProductCategory::class);
        $div_types = $getLatestRelevantDivisionProducts($value_broker->division->id, ProductType::class);
        $div_products = $getLatestRelevantDivisionProducts($value_broker->division->id, Product::class);

        $getLatestRelevantGroupProducts = function ($groupId, $subjectType) use ($getLatestRelevantProducts) {
            return $getLatestRelevantProducts(
                GroupProduct::where('user_group_id', $groupId)->where('subject_type', $subjectType)
            );
        };

        $group_categories = $getLatestRelevantGroupProducts($value_broker->user_group_id, ProductCategory::class);
        $group_products = $getLatestRelevantGroupProducts($value_broker->user_group_id, Product::class);
        $group_types = $getLatestRelevantGroupProducts($value_broker->user_group_id, ProductType::class);

        $data = [];
        $categories = ProductCategory::with('types.products')->get();
        foreach ($categories as $category) {
            $categoryData = [];
            $categoryData['name'] = $category->name;
            $categoryData['types'] = [];

            $types = $category->types;
            foreach ($types as $type) {
                $typeData = [];
                $typeData['name'] = $type['name'];
                $typeData['products'] = [];

                $categoryData['types'][$type->id] = $typeData;
            }

            $data[$category->id] = $categoryData;
        }

        $products = Product::with('type', 'category')->get();
        $versions = ProductVersion::where('validity_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->where('validity_to', '>=', $date)
                    ->orWhereNull('validity_to');
            })->get();

        foreach ($products as $product) {
            $productData = [];
            $productData['name'] = $product->name;
            $productData['partner'] = $product->partner->name;

            $productVersion = $versions->firstWhere('product_product_id', $product->id);
            $productData['initial_commission'] = $productVersion->initial_commission ?? 0;
            $productData['follow_up_commission'] = $productVersion->follow_up_commission ?? 0;

            $division_product = $div_products->firstWhere('subject_id', $product->id);
            if (!$division_product->status) {
                $division_type = $div_types->firstWhere('subject_id', $product->type->id);

                if (!$division_type->status) {
                    $division_category = $div_categories->firstWhere('subject_id', $product->category->id);

                    if (!$division_category->status) {
                        $productData['sfa_margin'] = $productVersion->sfa_margin;
                        $productData['storno_margin'] = $productVersion->storno_margin;
                    } elseif ($division_category->status == 1) {
                        $productData['sfa_margin'] = $division_category->sfa_margin;
                        $productData['storno_margin'] = $division_category->storno_margin;
                    } else {
                        continue;
                    }

                    $productData['group_commission'] = ($group_category = $group_categories->firstWhere('subject_id', $product->category->id)) ? $group_category->commission : $value_broker->group->provision;
                } elseif($division_type->status == 1) {
                    $productData['sfa_margin'] = $division_type->sfa_margin;
                    $productData['storno_margin'] = $division_type->storno_margin;
                    $productData['group_commission'] = ($group_type = $group_types->firstWhere('subject_id', $product->type->id)) ? $group_type->commission : $value_broker->group->provision;
                } else {
                    continue;
                }
            } elseif($division_product->status == 1) {
                $productData['sfa_margin'] = $division_product->sfa_margin;
                $productData['storno_margin'] = $division_product->storno_margin;
                $productData['group_commission'] = ($group_product = $group_products->firstWhere('subject_id', $product->id)) ? $group_product->commission : $value_broker->group->provision;
            } else {
                continue;
            }

            $storno = $broker->has_storno && $broker->division->has_storno;
            if ($broker->division->type == 'percentage') {
                $productData['broker_initial_commission'] = $productData['initial_commission'] * (1 - ($productData['sfa_margin'] / 100)) * ($productData['group_commission'] / 100);
                $productData['broker_initial_commission_storno'] = $storno ? $productData['broker_initial_commission'] - ($productData['broker_initial_commission'] * (($productData['storno_margin'] ?? 100) / 100)) : null;

                if ($productData['follow_up_commission']) {
                    $productData['broker_follow_up_commission'] = $productData['follow_up_commission'] * (1 - ($productData['sfa_margin'] / 100)) * ($productData['group_commission'] / 100);
                    $productData['broker_follow_up_commission_storno'] = $storno ? $productData['broker_follow_up_commission'] - ($productData['broker_follow_up_commission'] * (($productData['storno_margin'] ?? 100) / 100)) : null;
                }
            } else {
                if ($productData['initial_commission'] != 0 && $productData['sfa_margin'] != 0 && $productData['group_commission'] != 0) {
                    $productData['broker_initial_commission'] = 1 / (1 * ($productData['initial_commission'] / 100) * ( 1 - ($productData['sfa_margin'] / 100)) / ($productData['group_commission']));
                    $productData['broker_initial_commission_storno'] = $storno ? $productData['broker_initial_commission'] + ($productData['broker_initial_commission'] * (($productData['storno_margin'] ?? 100) / 100)) : null;

                    if ($productData['follow_up_commission']) {
                        $productData['broker_follow_up_commission'] = 1 / (1 * ($productData['follow_up_commission'] / 100) * ( 1 - ($productData['sfa_margin'] / 100)) / ($productData['group_commission']));
                        $productData['broker_follow_up_commission_storno'] = $storno ? $productData['broker_follow_up_commission'] + ($productData['broker_follow_up_commission'] * (($productData['storno_margin'] ?? 100) / 100)) : null;
                    }
                } else {
                    $productData['broker_initial_commission'] = 0;
                    $productData['broker_initial_commission_storno'] = 0;
                }
            }

            $data[$product->category->id]['types'][$product->type->id]['products'][] = $productData;
        }

        if ($format == 'file') {
            $pdf = PDF::loadView('plugin.User::templates.PDF_products_tree', [
                'data' => $data,
                'division' => $this::getDivision($divisionId),
                'group' => $broker->group,
                'broker' => $broker,
                'date' => $date->format('d.m.Y'),
            ], [], 'UTF-8');

            return $pdf->stream('moj_pdf.pdf');
        } else {
            return view('plugin.User::broker.products_tree', [
                'data' => $data,
                'division' => $this::getDivision($divisionId),
                'broker' => $broker,
                'date' => $date->format('d.m.Y'),
            ]);
        }
    }
}
