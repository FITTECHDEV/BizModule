<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Notification;
use App\Notifications\PushNotification;
use Illuminate\Support\Facades\Notification as FacadesNotification;

class PushController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store the PushSubscription.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'endpoint'    => 'required',
            'keys.auth'   => 'required',
            'keys.p256dh' => 'required'
        ]);
        $endpoint = $request->endpoint;
        $token = $request->keys['auth'];
        $key = $request->keys['p256dh'];
        $user = Auth::user();
        $user->updatePushSubscription($endpoint, $key, $token);

        return response()->json(['success' => true], 200);
    }

    /**
     * Send Push Notifications to all users.
     *
     * @return \Illuminate\Http\Response
     */
    public function push()
    {
        FacadesNotification::send(User::where('ID', '45ddfc27-f7f1-4b7e-9f9b-df6472fd8b27')->first(), new PushNotification);
        return redirect()->back();
    }
}
