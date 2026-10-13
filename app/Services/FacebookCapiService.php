<?php

namespace App\Services;

use FacebookAds\Api;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;
use Illuminate\Support\Facades\Log;

class FacebookCapiService
{
    /**
     * Send a single server-side event to the Meta Conversions API.
     *
     * @param string $eventName   e.g. 'Purchase', 'ViewContent', 'InitiateCheckout'
     * @param array  $userData    Keys: ip, user_agent, fbc, fbp, email, phone, first_name, last_name
     * @param array  $customData  Keys: value, content_ids, content_name, content_type, num_items, order_id
     * @param string $eventId     Unique ID shared with the browser pixel for deduplication
     * @param string $sourceUrl   Full URL where the event occurred
     */
    public function sendEvent(
        string $eventName,
        array  $userData,
        array  $customData,
        string $eventId,
        string $sourceUrl = ''
    ): void {
        $pixelId = config('services.facebook.pixel_id');
        $token   = config('services.facebook.capi_token');

        if (empty($pixelId) || empty($token)) {
            return; // not configured — silently skip
        }

        Api::init(null, null, $token);

        // ── UserData ────────────────────────────────────────────────
        $ud = (new UserData())
            ->setClientIpAddress($userData['ip'] ?? null)
            ->setClientUserAgent($userData['user_agent'] ?? null);

        if (!empty($userData['fbc']))        $ud->setFbc($userData['fbc']);
        if (!empty($userData['fbp']))        $ud->setFbp($userData['fbp']);
        if (!empty($userData['external_id'])) $ud->setExternalId($userData['external_id']);
        if (!empty($userData['fb_login_id'])) $ud->setFbLoginId($userData['fb_login_id']);
        // SDK normalizes + hashes PII automatically
        if (!empty($userData['email']))      $ud->setEmails([$userData['email']]);
        if (!empty($userData['phone']))      $ud->setPhones([$userData['phone']]);
        if (!empty($userData['first_name'])) $ud->setFirstNames([$userData['first_name']]);
        if (!empty($userData['last_name']))  $ud->setLastNames([$userData['last_name']]);

        // ── CustomData ──────────────────────────────────────────────
        $cd = (new CustomData())->setCurrency('BDT');

        if (isset($customData['value']))         $cd->setValue((float) $customData['value']);
        if (!empty($customData['content_ids']))  $cd->setContentIds(array_map('strval', (array) $customData['content_ids']));
        if (!empty($customData['content_name'])) $cd->setContentName((string) $customData['content_name']);
        if (!empty($customData['content_type'])) $cd->setContentType((string) $customData['content_type']);
        if (isset($customData['num_items']))     $cd->setNumItems((int) $customData['num_items']);
        if (!empty($customData['order_id']))     $cd->setOrderId((string) $customData['order_id']);

        // ── Event ───────────────────────────────────────────────────
        $event = (new Event())
            ->setEventName($eventName)
            ->setEventTime(time())
            ->setEventId($eventId)
            ->setActionSource(ActionSource::WEBSITE)
            ->setUserData($ud)
            ->setCustomData($cd);

        if (!empty($sourceUrl)) {
            $event->setEventSourceUrl($sourceUrl);
        }

        try {
            (new EventRequest($pixelId))
                ->setEvents([$event])
                ->execute();

            Log::info('[CAPI] Event sent', [
                'event'    => $eventName,
                'event_id' => $eventId,
                'url'      => $sourceUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('[CAPI] Failed to send event', [
                'event'    => $eventName,
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
