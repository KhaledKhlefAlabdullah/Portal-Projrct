<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomersRequest;
use App\Http\Requests\EntityRequest;
use App\Http\Requests\MaterialRequest;
use App\Http\Resources\EntityResource;
use App\Models\Category;
use App\Models\Entity;
use App\Models\Shipment;
use App\Models\Stakeholder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Exception;

use function App\Helpers\api_response;
use function App\Helpers\count_items;
use function App\Helpers\find_and_update;
use function App\Helpers\getAndCheckModelById;
use function App\Helpers\getIdByName;
use function App\Helpers\stakeholder_id;


class EntityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get Entity using relationship
        $entities = Stakeholder::with('entities')->get();

        return ($entities->count() == 1)
            ? new EntityResource($entities->first())
            : EntityResource::collection($entities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EntityRequest $request)
    {
        // Validate the request
        $valid_data = $request->validated();

        // Create the Entity
        $entity = Entity::create($valid_data);

        return new EntityResource($entity);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Get the entity
        $entity = Entity::find($id);

        if (!$entity) {
            return response()->json(['message' => 'Entity not found'], 404);
        }

        // Return the entity
        return new EntityResource($entity);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EntityRequest $request, string $id)
    {
        // Get the entity
        $entity = Entity::fined($id);

        if (!$entity) {
            return response()->json(['message' => 'Entity not found'], 404);
        }

        // Validate the request
        $valid_data = $request->validated();

        // Update the entity
        $entity->update($valid_data);

        return new EntityResource($entity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Get the entity by ID
        $entity = Entity::find($id);

        if (!$entity) {
            return response()->json(['message' => 'Entity not found'], 404);
        }

        // Delete the entity
        $entity->delete();

        return response()->json(['message' => 'Entity deleted successfully']);
    }

    /**
     * Get all routes in database with route usage
     */
    public function get_routes()
    {
        try {

            $stakeholder_id = stakeholder_id();

            $routes = DB::table('categories')
                ->join('entities', 'categories.id', '=', 'entities.category_id')
                ->select('entities.id as route_id', 'entities.public_id as id', 'entities.from as from', 'entities.to as to', 'entities.usage as usage')
                ->where(['entities.stakeholder_id' => $stakeholder_id, 'categories.name' => 'Route'])->whereNull('entities.deleted_at')->get();

            return response()->json([
                'data' => $routes,
                'message' => __('routes-getting-success')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('there error in server side try another time')
            ], 500);
        }
    }

    /**
     * Add new route details
     */
    public function add_new_route(Request $request)
    {
        try {

            $request->validate([
                'from' => 'required|string',
                'to' => 'required|string',
                'usage' => 'required|string|in:Employees transportation,Shipping,Supplies,waste'
            ]);

            $category_id = getIdByName(Category::class, 'Route');

            $routes_count = count_items(Entity::class, ['stakeholder_id' => stakeholder_id(), 'category_id' => $category_id]);

            Entity::create([
                'stakeholder_id' => stakeholder_id(),
                'category_id' => $category_id,
                'public_id' => $routes_count . 'R',
                'from' => $request->input('from'),
                'to' => $request->input('to'),
                'usage' => $request->input('usage'),
                'is_available' => true
            ]);

            return response()->json([
                'message' => __('Successfully adding new route')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('There error in adding rout try again')
            ], 500);
        }
    }

    /**
     * edit route details
     */
    public function edit_route_details(Request $request, string $id)
    {
        try {

            $request->validate([
                'from' => 'required|string',
                'to' => 'required|string',
                'usage' => 'required|string|in:Employees transportation,Shipping,Supplies,waste'
            ]);

            find_and_update(Entity::class, $id, ['from', 'to', 'usage'],
                ['from' => $request->input('from'), 'to' => $request->input('to'), 'usage' => $request->input('usage')]);

            return response()->json([
                'message' => __('Successfully adding new route')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('There error in editing rout details try again')
            ], 500);
        }
    }

    /**
     * View production sites list
     */
    public function production_sites()
    {
        try {

            $category_id = getIdByName(Category::class, 'Production Site');

            $production_sites = Entity::where(['category_id' => $category_id, 'stakeholder_id' => stakeholder_id()])
                ->select('entities.id', 'entities.name as name', 'entities.location')->get();

            return response()->json([
                'data' => $production_sites,
                'message' => __('production-s-getting-success')
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('production-s-getting-error')
            ], 500);
        }
    }

    /**
     * Add new production site
     */
    public function add_new_production_site(Request $request)
    {
        try {

            $request->validate([
                'name' => 'required|string|min:5',
                'location' => 'required|string|min:5'
            ]);

            $category_id = getIdByName(Category::class, 'Production Site');

            $production_sites_count = count_items(Entity::class, ['stakeholder_id' => stakeholder_id(), 'category_id' => $category_id]);

            Entity::create([
                'stakeholder_id' => stakeholder_id(),
                'category_id' => $category_id,
                'public_id' => $production_sites_count . 'PS',
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'is_available' => true
            ]);

            return response()->json([
                'message' => __('add-production-site')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('There error in adding the production sites try again')
            ], 500);
        }
    }

    /**
     * edit production site details
     */
    public function edit_production_site(Request $request, string $id)
    {
        try {

            $request->validate([
                'name' => 'required|string|min:5',
                'location' => 'required|string'
            ]);

            find_and_update(Entity::class, $id, ['name', 'location'], ['name' => $request->input('name'), 'location' => $request->input('location')]);

            return response()->json([
                'message' => __('Successfully editing the production site details')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('There error in editing production site details')
            ], 500);
        }
    }

    /**
     * View list of customers details
     */
    public function get_customers()
    {
        try {

            $customers = DB::table('entities as customers')
                ->join('shipments', 'customers.id', '=', 'shipments.customer_id')
                ->join('entities as products', 'shipments.product_id', '=', 'products.id')
                ->join('entities as routes', 'shipments.route_id', '=', 'routes.id')
                ->select('customers.id as customer_id', 'shipments.id as shipment_id', 'customers.name as customer_name',
                    'customers.public_id as id', 'products.name as shipped_product', 'shipments.location', 'customers.phone_number as phone', 'routes.public_id as route')
                ->where('customers.stakeholder_id', stakeholder_id())
                ->whereNull('customers.deleted_at')
                ->get();

            return response()->json([
                'data' => $customers,
                'message' => __('customer-getting-success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('customer-getting-error')
            ], 500);
        }

    }

    /**
     * Get the products for customers
     */
    public function get_products()
    {
        try {

            $category_id = getIdByName(Category::class, 'Product');

            $products = Entity::where(['stakeholder_id' => stakeholder_id(), 'category_id' => $category_id])->select('entities.id', 'entities.name')->get();

            return response()->json([
                'data' => $products,
                'message' => __('products-getting-success')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('products-getting-error')
            ], 500);
        }
    }

    /**
     * Add new customer
     */
    public function add_customer(CustomersRequest $request)
    {
        try {

            $customers_count = count_items(Entity::class, ['stakeholder_id' => stakeholder_id(), 'category_id' => getIdByName(Category::class, 'Customer')]);

            $customer = Entity::create([
                'stakeholder_id' => stakeholder_id(),
                'category_id' => getIdByName(Category::class, 'Customer'),
                'name' => $request->input('customer_name'),
                'public_id' => $customers_count . "C",
                'phone_number' => $request->input('phone_number'),
            ]);

            $shipments_count = count_items(Shipment::class, ['customer_id' => $customer->id, 'stakeholder_id' => stakeholder_id()]);

            Shipment::create([
                'route_id' => $request->input('reoute_id'),
                'product_id' => $request->input('shiped_product_id'),
                'customer_id' => $customer->id,
                'stakeholder_id' => stakeholder_id(),
                'public_id' => $shipments_count . 'SH',
                'name' => $customer->name . '-Shipment',
                'location' => $request->input('location'),
                'contact_info' => $customer->phone_number
            ]);

            return response()->json([
                'message' => __('customer-creating-success')
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('customer-creating-error')
            ], 500);
        }
    }

    /**
     * Eedit Customer details
     */
    public function edit_customer(CustomersRequest $request, string $customer_id, string $shipment_id)
    {
        try {

            $customer = getAndCheckModelById(Entity::class, $customer_id);

            $shipment = getAndCheckModelById(Shipment::class, $shipment_id);

            $customer->update([
                'name' => $request->input('customer_name'),
                'phone_number' => $request->input('phone_number'),
            ]);

            $shipment->update([
                'route_id' => $request->input('reoute_id'),
                'product_id' => $request->input('shiped_product_id'),
                'customer_id' => $customer->id,
                'name' => $customer->name . '-Shipment',
                'location' => $request->input('location'),
                'contact_info' => $customer->phone_number
            ]);

            return response()->json([
                'message' => __('customer-editing-success')
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('customer-editing-error')
            ], 500);
        }
    }


    /**
     * This case needs a flood api.
     * View the current status of the Production site (how many days are expected for the flood to reach the site)
     * @return array
     */
    public function view_status_production_site()
    {
        return api_response();
    }


    /*
     * view_future_status_production_site
     *
     */

    public function view_future_status_production_site()
    {
        return api_response();

    }





    // Materials Start

    /**
     * Get all materials in database with material usage
     */
    public function get_materials()
    {
        $stakeholder_id = stakeholder_id();

        // Fetching specific columns, namely the identifier, the general identifier, and the name of the material,
        // also merging the items table and the entity table,
        // which currently expresses the materials and returns data that has not been deleted.
        $material = DB::table('categories')
            ->join('entities', 'categories.id', '=', 'entities.category_id')
            ->select('entities.id as material_id', 'entities.public_id as id', 'entities.name as name')
            ->where(['entities.stakeholder_id' => $stakeholder_id, 'categories.name' => 'Material'])->whereNull('entities.deleted_at')->get();

        return response()->json([
            'materials' => $material,
            'message' => __('materials-getting-success')
        ], 200);
    }

    /**
     * Add new material details
     */
    public function add_new_material(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required|string|unique:entities,public_id',
                'name' => 'required|string',
                'description' => 'nullable|string',
            ]);


            $entity = Entity::create([
                'stakeholder_id' => stakeholder_id(),
                'category_id' => getIdByName(Category::class, 'Material'),
                'public_id' => $request->input('id'),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            return response()->json([
                'data' => $entity,
                'message' => __('Successfully adding new Material')
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('There error in adding Material try again')
            ], 500);
        }
    }
    // Materials End

}
