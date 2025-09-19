<?php

namespace App\Http\Middleware;

use App\Plugins\User\app\Models\Broker;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanAccessBroker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!($broker = $request->route('broker'))) {
//            try { //TODO redirect na homepage a vypis hlasku?
            abort(400, "Chýba parameter 'broker'!");
//            } catch (\Exception $e) {
//                return handleErrorReturn($e)->withInput();
//            }
        }

        //Check ci je klient patriaci brokerovi alebo jeho podriadenym
        $broker = Broker::BrokerAccess(null, true)
            ->findOr($broker instanceof Broker ? $broker->id : $broker, function () {
//                try {
                throw new AuthorizationException;
//                } catch (\Exception $e) {
//                    return handleErrorReturn($e)->withInput();
//                }
            });

        return $next($request);
    }
}
