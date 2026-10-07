<?php

use App\Http\Middleware\AturSubpath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

it('URL update Livewire hanya memuat prefix subpath satu kali', function () {
    $root = 'http://supportfkip.unsil.ac.id/kerjasama';
    config(['app.url' => $root]);
    URL::forceRootUrl($root);

    Request::setTrustedProxies(['10.0.0.1'], Request::HEADER_X_FORWARDED_PREFIX);
    $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '10.0.0.1']);
    app()->instance('request', $request);
    app('url')->setRequest($request);

    (new AturSubpath)->handle($request, fn () => response('ok'));

    $uri = url(app('livewire')->getUpdateUri());

    expect($uri)->toStartWith($root.'/livewire-')
        ->and($uri)->not->toContain('/kerjasama/kerjasama');

    URL::forceRootUrl(null);
    Request::setTrustedProxies([], Request::HEADER_X_FORWARDED_FOR);
});
