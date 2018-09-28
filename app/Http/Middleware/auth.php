<?php

namespace App\Http\Middleware;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Response;

use Closure;

class auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        try {
            try {
            $token = (new Parser())->parse((string) $request->token);
            } catch(\Exception $e){
                return Response::json(['status' => '0', 'message' => 'Token tidak valid']);
            }

            $data = new ValidationData();
            $data->setIssuer('shafly');
            $data->setId('4f1g23a12aa');
            $token->validate($data);
        } catch (InvalidToken $e) {
            return Response::json(['status' => '0', 'message' => 'Token tidak valid']);
        }

        return $next($request);
    }
}
