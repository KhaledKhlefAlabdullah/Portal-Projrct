<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\NotificationSettingRequest;
use App\Http\Resources\Notification\NotificationSettingResource;
use App\Models\Notifications\NotificationsSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use function App\Helpers\getAndCheckModelById;

class NotificationsSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all notifications settings by auth user
        $data = NotificationsSetting::where('user_id', auth()->user()->id)->get();

        return ($data->count() == 1)
            ? new NotificationSettingResource($data)
            : NotificationSettingResource::collection($data);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(NotificationSettingRequest $request)
    {
        // Check if validation
        $valid_data = $request->validated();

        // Create new notification setting
        $data = NotificationsSetting::create($valid_data);

        return new NotificationSettingResource($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Get the notification setting by id and check if existing
        try {
            $data = getAndCheckModelById(NotificationsSetting::class, $id);

            return new NotificationSettingResource($data);

        } catch (NotFoundResourceException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(NotificationSettingRequest $request, string $id)
    {
        try {
            // Get the notification setting by id and check if existing
            $data = getAndCheckModelById(NotificationsSetting::class, $id);

            $valid_data = $request->validated();

            $data->update($valid_data);

            return new NotificationSettingResource($data);
        } catch (NotFoundResourceException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Get the notification setting by id and check if existing
        try {
            $data = getAndCheckModelById(NotificationsSetting::class, $id);

            return new NotificationSettingResource($data);

        } catch (NotFoundResourceException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function view_all_notification_settings()
    {
        try {

            $user_id = Auth::id();

            // Retrieve notification settings data along with main category and subcategory information
            $notifications_settings = DB::table('notifications_settings')
                // Join the notifications_settings table with the categories table twice to get main and subcategories
                ->join('categories as main_category', 'notifications_settings.main_category_id', '=', 'main_category.id')
                ->join('categories as sub_categories', 'notifications_settings.sub_category_id', '=', 'sub_categories.id')
                // Select main category ID and name, renaming them for clarity
                ->select('main_category.id as main_category_id', 'main_category.name as main_category_name')
                // Aggregate notification settings IDs into a comma-separated string
                ->selectRaw('GROUP_CONCAT(notifications_settings.id) as notification_settings_ids')
                // Aggregate subcategory IDs into a comma-separated string
                ->selectRaw('GROUP_CONCAT(sub_categories.id) as sub_categories_ids')
                // Aggregate subcategory names into a comma-separated string
                ->selectRaw('GROUP_CONCAT(sub_categories.name) as sub_categories_name')
                // Aggregate notification states into a comma-separated string
                ->selectRaw('GROUP_CONCAT(notifications_settings.notification_state) as states')
                // Aggregate notification levels into a comma-separated string
                ->selectRaw('GROUP_CONCAT(notifications_settings.notification_level) as levels')
                // Aggregate notification priorities into a comma-separated string
                ->selectRaw('GROUP_CONCAT(notifications_settings.notification_priorities) as priorities')
                // Aggregate notification on/off states into a comma-separated string
                ->selectRaw('GROUP_CONCAT(notifications_settings.is_on) as on_off')
                // Filter the results by user ID
                ->where('notifications_settings.user_id', '=', $user_id)
                // Group the results by main category ID and name
                ->groupBy('main_category.id', 'main_category.name')
                // Execute the query and retrieve the results
                ->get();

            return response()->json([
                'data' => $notifications_settings,
                'message' => __('Successfully getting the notification setting data')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __($e->getMessage()),
                'message' => __('There error in getting the notifications setting6')
            ], 500);
        }
    }
}
