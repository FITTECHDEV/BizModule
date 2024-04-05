<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

use RootInc\LaravelAzureMiddleware\Azure as Azure;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Closure;

/**
 * Summary of AppAzure
 */
class AppAzure extends Azure
{
    protected function success(Request $request, $access_token, $refresh_token, $profile)
    {
        try {
            $graph = new Graph();
            $graph->setAccessToken($access_token);

            $graph_user = $graph->createRequest("GET", "/me")
                ->setReturnType(Model\User::class)
                ->execute();

            $login_id = explode('@', $graph_user->getUserPrincipalName())[0]; // from email get first index

            $user = User::where(['LoginID' => $login_id])->first();


            Auth::loginUsingId($user->EmployeeCode);


            return parent::success($request, $access_token, $refresh_token, $profile);
        } catch (Exception $ex) {
            Log::warning($ex->getMessage());
        }
    }

    protected function fail(Request $request, Exception $e)
    {
        Log::warning($e->getMessage());
    }

    protected function handlecallback(Request $request, Closure $next, $access_token, $refresh_token)
    {

        Log::notice(json_encode($request));

        return parent::handlecallback($request, $next, $access_token, $refresh_token);
    }

    protected function redirect(Request $request)
    {
        if (Auth::user() !== null) {
            Log::notice('redirecting to azure next');
            return $this->azure($request);
        } else {
            Log::notice('redirecting to parent next');
            return parent::redirect($request);
        }
    }

    /**
     * Summary of azurelogout
     * @return void
     */
    public function azurelogout(Request $request)
    {

        $request->session()->pull('_rootinc_azure_access_token');
        $request->session()->pull('_rootinc_azure_refresh_token');

        return redirect()->away($this->getLogoutUrl() . '?post_logout_redirect_uri=https://erpuat.freightintime.com');
    }
}
