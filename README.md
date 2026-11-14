# be-solutions/be-solutions-php

PHP SDK for [Be Solutions](https://be-solutions.live): video calls, email and SMS APIs. PHP 8.1+, Laravel ready.

```bash
composer require be-solutions/be-solutions-php
```

## Plain PHP

```php
$bes = new \BeSolutions\Client([
    'api_key'  => getenv('BES_API_KEY'),          // "bes_…" from Dashboard → your project → API keys
    'base_url' => 'https://be-solutions.live',
]);

// Video: a token for one user and one room, to pass to the browser SDK
$token = $bes->tokens->create(['room' => 'consult-42', 'identity' => (string) $user->id, 'name' => $user->name])['token'];

// Email (from a domain verified in the dashboard)
$email = $bes->emails->send([
    'from'    => 'Acme <hello@acme.com>',
    'to'      => 'ada@example.com',
    'subject' => 'Votre rendez-vous est confirmé',
    'html'    => '<p>À lundi 10 h.</p>',
], idempotencyKey: 'appointment-' . $appointment->id);

// SMS
$sms = $bes->sms->send(['to' => '+237650000000', 'body' => 'Votre code : 482913']);
```

Errors throw `\BeSolutions\BeSolutionsException` with `$e->status` (HTTP code, e.g. `402` when credit runs out) and `$e->getMessage()`.

Sign access tokens locally (no network call) with the project key and secret:

```php
$local = new \BeSolutions\Client(['project_key' => getenv('BES_PROJECT_KEY'), 'project_secret' => getenv('BES_PROJECT_SECRET')]);
$token = $local->createAccessToken(['room' => 'consult-42', 'identity' => 'user-17', 'grants' => ['canPublish' => true]])['token'];
```

## Laravel

The service provider and the `BeSolutions` facade are registered automatically. Add to `.env`:

```dotenv
BES_BASE_URL=https://be-solutions.live
BES_API_KEY=bes_...
BES_PROJECT_KEY=AK...
BES_PROJECT_SECRET=...
```

Inject the client, or use the facade:

```php
use BeSolutions\Client;

class CallController
{
    public function token(Request $request, Client $bes)
    {
        return $bes->tokens->create(['room' => 'consult-' . $request->route('id'), 'identity' => (string) $request->user()->id]);
    }
}

// Anywhere:
\BeSolutions::client()->sms->send(['to' => $user->phone, 'body' => 'Votre code : ' . $code]);
```

### Webhooks

```php
// routes/web.php
Route::post('/webhooks/be-solutions', function (Request $request) {
    $event = $request->attributes->get('besolutions_event');
    if ($event['event'] === 'email.bounced') { /* … */ }
    return response()->noContent();
})->middleware(\BeSolutions\Laravel\Middleware\VerifyBeSolutionsWebhook::class);
```

Exclude that route from CSRF protection. Without Laravel, verify with
`\BeSolutions\Webhook::verify(file_get_contents('php://input'), getallheaders(), getenv('BES_PROJECT_SECRET'))`.

Publish the config file if you need to change it: `php artisan vendor:publish --tag=besolutions-config`.
