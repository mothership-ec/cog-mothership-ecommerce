Mothership Ecommerce
====================

Gateways
--------

Payment gateways allow you to process payments through an external service.

There are two components to a gateway within mothership; an implementation of `Gateway\GatewayInterface` to communicate with the external service, and controllers to handle purchase and refund requests.

### Default gateways

#### ZeroPayment

The zero payment gateway is the most basic implementation, it simply completes the order and redirects straight to the success url.

#### LocalPayment

The local payment gateway is an extension of zero payment. If there is any outstanding payment to be made on the order it adds a manual payment to match the remaining difference before completing the order.

### Extending with new gateway providers

To add a new gateway provider you'll need to create a new adapter that implements `Gateway\GatewayInterface` and append it to the `gateway.collection` service.

Secondly you'll need to implement the `Controllers\Gateway\PurchaseControllerInterface` and `Controllers\Gateway\RefundControllerInterface`. If your new gateway does not support refunds then the `refund()` method should just return a `404`.

Of course your gateway and controller(s) can use additional methods for handling the specific functionality and process flow for the new provider, such as callbacks from the external service.


Purchases
--------

There can be several different reasons for making a purchase within mothership, e.g. checkout. When writing a new purchase process the final confirmation action should forward the request to the current gateway's purchase controller reference.

This forward request should pass the instance of `PayableInterface` that is being purchased and the stages configuration.

```php
return $this->forward($this->get('gateway')->getPurchaseControllerReference(), [
    'payable' => $instanceOfPayableInterface,
    'stages'  => [
        'cancelRoute'       => '', // route to display after cancelling the purchase
        'failureRoute'      => '', // route to display purchase errors
        'successRoute'      => '', // route to display purchase success
        'completeReference' => '', // controller method reference to complete the purchase
    ]
]);
```

The purchase process requires a controller that implements `Gateway\CompletePurchaseController`.

The `complete()` method should turn the payable into a saved instance of the object it represents, e.g. an order, store any payments as necessary and return a success url in a `JsonResponse`. This completion process should be called by the gateway purchase controller when confirming the purchase with the external provider.

### Checkout

The checkout process is the standard implementation of the purchase process.


Refunds
-------

...