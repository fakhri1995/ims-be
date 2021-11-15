<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $request->headers->set('Authorization', "Bearer ".$request->headers->get('Authorization'));
        
        if ($this->auth->guard($guard)->guest()) {
            $response = ["success" => false, "message" => [
                "errorInfo" => [
                    "status" => 401, 
                    "reason" => "Unauthorized", 
                    "server_code" => 401, 
                    "status_detail" => "Invalid Token Payload"
                ]
            ]];
            return response()->json($response, 401);
        }
        
        if(!$this->auth->user()->company->is_enabled){
             $response = ["success" => false, "message" => [
                "errorInfo" => [
                    "status" => 401, 
                    "reason" => "Unauthorized", 
                    "server_code" => 401, 
                    "status_detail" => "User's Company Is Not Active"
                ]
            ]];
            return response()->json($response, 401);
        }

        if(!$this->auth->user()->is_enabled){
             $response = ["success" => false, "message" => [
                "errorInfo" => [
                    "status" => 401, 
                    "reason" => "Unauthorized", 
                    "server_code" => 401, 
                    "status_detail" => "User Is Not Active"
                ]
            ]];
            return response()->json($response, 401);
        }

        return $next($request);
    }
}
