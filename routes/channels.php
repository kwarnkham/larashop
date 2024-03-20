<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('App.Models.Order.{id}', function (User $user, $id) {
    return (int) $user->id === (int) Order::findOrNew($id)->user_id;
});

Broadcast::channel('App.Models.Order', function ($user) {
    return $user->hasRole('admin');
});
