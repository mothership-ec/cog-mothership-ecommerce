Mothership Ecommerce
====================

Configuration
-------------

### Dispatch

- **printer-name**: ...

### Payment

- **gateway**: Name of gateway to use. Note the gateway must be available to the system.
- **use-test-payments**: Use the test environment of external payment gateways.
- **salt**: Used when hashing data in the payment processes.


Gateways
--------

Payment gateways allow you to process payments through an external service.

There are two components to a gateway within mothership; an implementation of `Gateway\GatewayInterface` to communicate with the external service, and controllers to handle purchase and refund requests.

### Default gateways

#### ZeroPayment

The zero payment gateway is the most basic implementation, it simply completes the order and redirects straight to the success url.

#### LocalPayment

The local payment gateway is an extension of zero payment.

### Extending with new gateway providers

To add a new gateway provider you'll need to create a new adapter that implements `Gateway\GatewayInterface` and append it to the `gateway.collection` service.

Secondly you'll need to implement the `Controllers\Gateway\PurchaseControllerInterface` and `Controllers\Gateway\RefundControllerInterface`. If your new gateway does not support refunds then the `refund()` method should just return `$this->createNotFoundException()`.

Of course your gateway and controller(s) can use additional methods for handling the specific functionality and process flow for the new provider, such as callbacks from the external service.


Purchases
--------

A purchase process is a system which sends a payment request to a gateway and creates / modifies an object on a success response and reacts accordingly to cancelled and failed purchases. For example, the standard checkout process which on success saves the order to the database and records the payment against it.

When writing a new purchase process the 'continue to payment' action should forward the request to the current gateway's purchase controller reference using `$this->get('gateway')->getPurchaseControllerReference()`.

This forward request should pass the instance of `PayableInterface` that is being purchased and the stages configuration.

```php
$controller = 'Message:Mothership:Foo::Controller:Bar';
return $this->forward($this->get('gateway')->getPurchaseControllerReference(), [
    'payable' => $instanceOfPayableInterface,
    'stages'  => [
        'cancel'  => $controller . '#cancel',  // Method for reacting to cancelled purchases
        'failure' => $controller . '#failure', // Method for reacting to failed purchases
        'success' => $controller . '#success', // Method for reacting to successful purchases
    ]
]);
```

The purchase process requires a controller that implements `Controllers\Gateway\CompleteControllerInterface`. This should implement the `success`, `cancel` and `failure` methods.

The `success()` method should turn the payable into a saved instance of the object it represents, e.g. an order, store any payments as necessary and return a success url in a `JsonResponse`. This completion process should be called by the gateway purchase controller when confirming the purchase with the external provider.


Refunds
-------

A refund process works in the same way as a purchase purchase, except you forward the request to `$this->get('gateway')->getRefundControllerReference()` and pass an additional `reference` parameter. The `cancel` stage is not required for refunds.

```php
$controller = 'Message:Mothership:Foo::Controller:Bar';
return $this->forward($this->get('gateway')->getRefundControllerReference(), [
    'payable'   => $instanceOfPayableInterface,
    'reference' => 'reference for the payment made previously being refunded',
    'stages'    => [
        'failure' => $controller . '#failure', // Method for reacting to failed refunds
        'success' => $controller . '#success', // Method for reacting to successful refunds
    ]
]);
```

