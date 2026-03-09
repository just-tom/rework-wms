<?php

declare(strict_types=1);

arch('models are final classes')
    ->expect('App\Models')
    ->classes()
    ->toBeFinal();

arch('models extend eloquent model')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Models\Traits');

arch('model traits are traits')
    ->expect('App\Models\Traits')
    ->toBeTraits();

arch('actions are final')
    ->expect('App\Actions')
    ->toBeFinal();

arch('value objects are final and readonly')
    ->expect('App\ValueObjects')
    ->toBeFinal()
    ->toBeReadonly();

arch('enums live in the Enums namespace')
    ->expect('App\Enums')
    ->toBeEnums();

arch('controllers are invokable')
    ->expect('App\Http\Controllers')
    ->toBeInvokable()
    ->ignoring('App\Http\Controllers\Controller');

arch('env is not used outside config')
    ->expect('env')
    ->not->toBeUsedIn('App');
