<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('session.{id}', function ($user, $id) {
    return ['id' => $user->id, 'name' => $user->fio];
});

Broadcast::channel('training.{id}', function ($user, $id) {
    return ['id' => $user->id, 'name' => $user->fio];
});

Broadcast::channel('test.{id}', function ($user, $id) {
    return ['id' => $user->id, 'name' => $user->fio];
});
