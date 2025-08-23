<?php

use Illuminate\Support\Facades\Broadcast;

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

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('driver.new-order.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('private-driver.new-order.{driverId}', function ($user, $driverId) {
    // Authorize that the user can listen to this channel (check if the user is the driver)
    return (int)$user->id === (int) $driverId;
});

Broadcast::channel('orders.updated.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::findOrFail($orderId);  // Will throw a 404 error if not found
    return $order->user_id === $user->id;
});

Broadcast::channel('driver.order.updated.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::findOrFail($orderId);  // Will throw a 404 error if not found
    return $order->driver_id === $user->id;
});

Broadcast::channel('vendor.order.updated.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::findOrFail($orderId);  // Will throw a 404 error if not found
    return $order->vendor_id === $user->vendor_id;
});