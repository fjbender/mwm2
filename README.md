# Mollie Webhook Multiplexer 2

## What is this?

Unfortunately, Mollie can only handle one Webhook endpoint per payment transaction. This application tries to circumvent this limitation by providing a lean multiplexer, that can forward the same webhook to different applications.

You can specify the endpoints in the `endpoints.neon` file. See `endpoints.neon.dist` for an explanation of the format.

This is the second iteration of the project, the [first one](https://github.com/fjbender/mollie-webhook-multiplexer) did not include a queueing mechanism and was based on Slim instead of Symfony.
## Requirements

* Some recent version of PHP (8.1 or newer should do) with `ext-amqp` if you wanna use the default RabbitMQ configuration.
* A web server that allows URL rewrites
* Optionally, but highly recommended: Either a RabbitMQ or Redis Server that can serve as a transport for the [Symfony Messenger](https://symfony.com/doc/current/messenger.html). In the default configuration we expect a RabbitMQ at `localhost:5672`. You can (and should) reconfigure `config/packages/messenger.yaml` to suit your needs. See the [docs for Symfony Messenger](https://symfony.com/doc/current/messenger.html#transport-configuration) to see what you can do.
* Optionally, for local testing: Docker to run a RabbitMQ server.

## Install the Application locally

* `git clone https://github.com/fjbender/mwm2`
* `cd mwm2`
* `composer install`
* `cp endoints.neon.dist endpoints.neon`
* Edit `endpoints.neon` to your needs
* Ensure that `.env` is configured correctly
* Serve, e.g. using the `symfony` CLI tool: `symfony server:start`
* Spin up a quick RabbitMQ: `docker run -d --hostname my-rabbit --name some-rabbit -p 5672:5672 -p 15672:15672 rabbitmq:3-management`

You can then test the application by posting a `x-www-form-urlencoded` POST request with `id=tr_12345` as payload to `http://localhost:8000/webhook` (or wherever you run the application).

To consume the queued up webhooks and forward them to the configured endpoints, you'd need to spin up a [worker](https://symfony.com/doc/current/messenger.html#consuming-messages-running-the-worker):

`php bin/console messenger:consume async -vv`

When deploying in production, observe the [hints](https://symfony.com/doc/current/messenger.html#deploying-to-production) in the Symfony docs about running the workers.

## Shop configuration

If you're using any of the Mollie default plugins, chances are that they'll set the webhook URL automatically. You'll have to hack the generation of the `webhookUrl` parameter, for example in Oxid 6:

```php
    # In vendor/mollie/mollie-oxid/Application/Model/Request/Base.php
    /**
     * Return the Mollie webhook url
     *
     * @return string
     */
    protected function getWebhookUrl()
    {
        // Hack to override webhookUrl
        return 'http://mollie-multiplexer.example.com/webhooks?cl=mollieWebhook';
        // End Hack
    }
```

In this example, you'd need to set `preserve_query: true` in the endpoints file.

## License

[BSD (Berkeley Software Distribution) License](https://opensource.org/licenses/bsd-license.php).

Copyright (c) 2022, Florian Bender
