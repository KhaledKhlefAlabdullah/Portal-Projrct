<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatResource;
use App\Models\Chat;
use Exception;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
            return ChatResource::collection(Chat::paginate());

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $valid_data = $request->validate([
            'chat_name' => ['required', 'string', 'max:255',],
        ]);

            $chat = Chat::create($valid_data);

            return new ChatResource($chat);

    }

    /**
     * Display the specified resource.
     * // TODO: I'm don't check this function
     */
    public function show(Chat $chat)
    {
        // way 1
        // Assuming $chat is an instance of the Chat model
//            return (new ChatResource($chat))->additional([
//                'messages' => $chat->messages()->orderBy('created_at', 'desc')->paginate(),
//                 'users' => $chat->users(),
//            ]);

        // way 2
        // Assuming $chat is an instance of the Chat model
        return (new ChatResource($chat))->additional([
            'messages' => $chat->messages()->latest()->paginate(),
            'users' => $chat->users(),
        ]);



    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chat $chat)
    {
        $valid_data = $request->validate([
            'chat_name' => ['sometimes', 'required', 'string', 'max:255',],
        ]);

            $chat->update($valid_data);

            return new ChatResource($chat);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chat $chat)
    {
            $chat->delete();

            return response()->json(null, 204);

    }
}
