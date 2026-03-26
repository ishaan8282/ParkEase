<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'ziggy' => fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'auth' => [
                'user' => $request->user() ? [
                    'id'     => $request->user()->id,
                    'name'   => $request->user()->name,
                    'email'  => $request->user()->email,
                    'phone'  => $request->user()->phone,
                    'avatar' => $request->user()->avatar,
                    'role'   => $request->user()->getRoleNames()->first(),
                    'status' => $request->user()->status,
                ] : null,
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error'),
                'reservation' => fn() => $request->session()->get('reservation'),
            ],
        ];
    }
}
