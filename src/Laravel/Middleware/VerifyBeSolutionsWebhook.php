<?php

declare(strict_types=1);

namespace BeSolutions\Laravel\Middleware;

use BeSolutions\BeSolutionsException;
use BeSolutions\Webhook;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects requests that aren't genuine Be Solutions webhooks. The verified event is available as
 * $request->attributes->get('besolutions_event').
 *
 *   Route::post('/webhooks/be-solutions', WebhookController::class)
 *       ->middleware(\BeSolutions\Laravel\Middleware\VerifyBeSolutionsWebhook::class);
 *
 * Exclude the route from CSRF protection (bootstrap/app.php → validateCsrfTokens(except: [...])).
 */
final class VerifyBeSolutionsWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $event = Webhook::verify($request->getContent(), $request->headers->all(), (string) config('besolutions.project_secret'));
        } catch (BeSolutionsException) {
            abort(400, 'Invalid Be Solutions webhook signature');
        }
        $request->attributes->set('besolutions_event', $event);
        return $next($request);
    }
}
