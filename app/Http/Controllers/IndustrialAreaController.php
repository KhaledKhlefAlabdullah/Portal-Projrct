<?php

namespace App\Http\Controllers;

use App\Models\IndustrialArea;
use App\Models\User;
use App\Models\UserProfile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\Helpers\fake_register_request;
use function App\Helpers\find_and_update;


class IndustrialAreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            // get all industrial areas in database
            $industrial_areas = DB::table('user_profiles')
            ->join('users','user_profiles.user_id','=','users.id')
            ->join('industrial_areas','users.industrial_area_id','=','industrial_areas.id')
            ->select('industrial_areas.id as id','industrial_areas.name as industrial_area_name','industrial_areas.address','users.email','user_profiles.name as user_name')->get();

            // return the data
            return response()->json([
                'industrial_areas' => $industrial_areas,
                'message' => __('Successfully request')
            ], 201);


        } // handling the exceptions
        catch (Exception $e) {

            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('Failed to get any thing')
            ], 501);

        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            // validate the inputs data
            $request->validate([
                'name' => ['required', 'string', 'min:5'],
                'address' => ['required', 'string'],
                'representative_name' => ['required', 'string'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class]
            ]);

            // create new industrial area
            $industrial_area = IndustrialArea::create([
                'name' => $request->input('name'),
                'address' => $request->input('address'),
            ]);

            // create industrial area representative (user)
            // Simulate a request to the RegisteredUserController@store method
            $response = fake_register_request(
                industrial_area_id: $industrial_area->id,
                name: $request->input('representative_name'),
                email: $request->input('email'),
                password: 'P@ssword',
                password_confirmation: 'P@ssword',
                stakeholder_type: 'Industrial_area_representative',
                location: $industrial_area->address
            );

            return $response;
        } catch (Exception $e) {

            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('there ara problem, some thing went wrong')
            ]);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required|string|exists:industrial_areas,id'
            ]);

            // get all industrial areas in database
            $industrial_area = IndustrialArea::findOrFail($request->input('id'))->with('user')->get();

            // check if their industrial areas in database
            if(!empty($industrial_area)){

                // return the data
                return response()->json([
                    'industrial_area' => $industrial_area,
                    'message' => __('Successfully request')
                ],201);

            }

            return response()->json([
                'message' => __('Successfully request but there now industrial areas in database yeet')
            ],402);

        }
            // handling the exceptions
        catch (\Exception $e){

            return response()->json([
                'error' => __($e->getMessage()),
                'message'=> __('Failed to get any thing')
            ],501);

        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try{

            // validate the inputs data
            $request->validate([
                'id' => 'required|string|exists:industrial_areas,id',
                'name' => ['required', 'string', 'min:5'],
                'address' => ['required', 'string'],
                'representative_name' => ['required','string'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class]
            ]);

            // get the industrial area want to edite
            $industrial_area = IndustrialArea::findOrFail($request->input('id'));

            $user = find_and_update(User::class,$industrial_area->id,['email'],[$request->input('email')]);

            $user_profile = find_and_update(UserProfile::class,$user->id,['name'],[$request->input('representative_name')]);

            $user_profile->update([
                'name' => $request->input('representative_name')
            ]);

            // update industrial area details
            $industrial_area->update([
                'name' => $request->input('name'),
                'address' => $request->input('address'),
            ]);

            return response()->json([
                'industrial_area' => $industrial_area,
                'user_email' => $user->email,
                'user_name' => $user_profile->name,
                'message' => __('successfully editing industrial area details')
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('there ara problem, some thing went wrong')
            ]);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IndustrialArea $industrial_area)
    {
        //
    }
}
