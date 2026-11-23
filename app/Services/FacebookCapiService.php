<?php

namespace App\Services;

use App\Models\Setting;
use FacebookAds\Api;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Content;
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
     * @param array  $userData    Keys: ip, user_agent, fbc, fbp, email, phone, first_name, last_name, city, state, zip, country
     * @param array  $customData  Keys: value, content_ids, contents, content_name, content_type, num_items, order_id, search_string
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
        $pixelId = trim((string) Setting::effective('analytics.meta.pixel_id', config('services.facebook.pixel_id')));
        $token   = trim((string) Setting::effective('analytics.meta.capi_token', ''))
            ?: trim((string) config('services.facebook.capi_token'));
        $testEventCode = trim((string) Setting::effective('analytics.meta.test_event_code', ''));

        if ($pixelId === '' || $token === '') {
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
        if (!empty($userData['city']))       $ud->setCities([$userData['city']]);
        if (!empty($userData['state']))      $ud->setStates([$userData['state']]);
        if (!empty($userData['zip']))        $ud->setZipCodes([$userData['zip']]);
        if (!empty($userData['country']))    $ud->setCountryCodes([strtoupper((string) $userData['country'])]);

        // ── CustomData ──────────────────────────────────────────────
        $cd = (new CustomData())->setCurrency('BDT');

        if (isset($customData['value']))         $cd->setValue((float) $customData['value']);
        if (!empty($customData['content_ids']))  $cd->setContentIds(array_map('strval', (array) $customData['content_ids']));
        if (!empty($customData['content_name'])) $cd->setContentName((string) $customData['content_name']);
        if (!empty($customData['content_type'])) $cd->setContentType((string) $customData['content_type']);
        if (isset($customData['num_items']))     $cd->setNumItems((int) $customData['num_items']);
        if (!empty($customData['order_id']))     $cd->setOrderId((string) $customData['order_id']);
        if (!empty($customData['search_string'])) $cd->setSearchString((string) $customData['search_string']);

        if (!empty($customData['contents']) && is_array($customData['contents'])) {
            $contents = [];

            foreach ($customData['contents'] as $content) {
                if (!is_array($content) || empty($content['id'])) {
                    continue;
                }

                $payload = (new Content())
                    ->setProductId((string) $content['id'])
                    ->setQuantity((int) ($content['quantity'] ?? 1))
                    ->setItemPrice((float) ($content['item_price'] ?? 0));

                if (!empty($content['title'])) {
                    $payload->setTitle((string) $content['title']);
                }

                $contents[] = $payload;
            }

            if ($contents !== []) {
                $cd->setContents($contents);
            }
        }

        // ── Event ───────────────────────────────────────────────────
        $event = (new Event())
            ->setEventName($eventName)
            ->setEventTime(time())
            ->setEventId($eventId)
            ->setActionSource(ActionSource::WEBSITE)
            ->setUserData($ud)
            ->setCustomData($cd);

        if ($sourceUrl !== '') {
            $event->setEventSourceUrl($sourceUrl);
        }

        try {
            $request = (new EventRequest($pixelId))->setEvents([$event]);

            if ($testEventCode !== '') {
                $request->setTestEventCode($testEventCode);
            }

            $request->execute();

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
