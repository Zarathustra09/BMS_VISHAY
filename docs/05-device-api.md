# Device / Sensor API

This is the integration guide for hardware devices (e.g. the temperature sensor Justin tested) that report readings into the BMS. It covers everything needed to wire up a new device: getting a key, the request format, and example code.

## 1. Get a device key

Each **machine** has its own API key. A user with the `machines.view-device-key` permission (admin, manager, or technician by default — see [06-security.md](06-security.md)) generates one from the machine's page in the web UI:

1. Log in, go to **Machines → (the machine) → Device API Key** card.
2. Click **Generate Key** (or **Regenerate Key** if one already exists).
3. Copy the key shown — it's also visible again later via the Show/Copy control on that same card, for anyone with the permission.

Regenerating a key immediately invalidates the previous one. There is exactly one active key per machine at a time.

## 2. Authenticate every request

Send the key in a custom header on every request:

```
X-Device-Key: <the plaintext key>
```

No `machine_id` needs to be sent in the request body — the server identifies the machine from the key itself, so a device can never accidentally (or maliciously) write logs for a different machine.

If the header is missing or the key doesn't match any machine, the server responds `401 Unauthorized` before touching the database.

## 3. Endpoint

```
POST /api/machine-logs
Content-Type: application/json
X-Device-Key: <your machine's key>
```

Rate limit: **120 requests per minute** per client (Laravel's default throttle keying, i.e. per IP). A temperature sensor reporting every few seconds is well within this; if you need higher-frequency reporting, batch readings client-side or raise the limit in [routes/api.php](../routes/api.php).

## 4. Payload fields

All fields except `log_type` are optional — send only what your device actually measures.

| Field | Type | Constraints | Units / Notes |
|---|---|---|---|
| `log_type` | string | **required.** One of: `status_change`, `maintenance`, `alarm`, `sensor_reading`, `operation`, `error` | Use `sensor_reading` for routine telemetry like temperature |
| `event` | string | max 255 chars | short human-readable description |
| `severity` | string | one of `info`, `warning`, `error`, `critical` | defaults to `info` if omitted |
| `previous_status` / `current_status` | string | max 255 chars | for `status_change` logs |
| `temperature` | number | -999999.99 to 999999.99 | °C, can be negative |
| `humidity` | number | 0 to 100 | % |
| `pressure` | number | -99999999.99 to 99999999.99 | bar |
| `power_consumption` | number | ≥ 0, max 99999999.99 | kW |
| `voltage` | number | ≥ 0, max 999999.99 | V |
| `current` | number | ≥ 0, max 999999.99 | A |
| `frequency` | number | ≥ 0, max 999999.99 | Hz |
| `runtime_hours` | integer | ≥ 0 | |
| `vibration_rms` | number | ≥ 0, max 99999.999 | RMS |
| `bearing_temp` | number | -999999.99 to 999999.99 | °C |
| `oil_level` | number | 0 to 9999.99 | % or litres depending on the machine |
| `notes` | string | max 65535 chars | |
| `performed_by` | string | max 255 chars | e.g. device identifier, if useful for debugging |
| `logged_at` | ISO 8601 date/time string | | defaults to the server's current time if omitted |

Any field not listed here is rejected by validation (`422`).

## 5. Example: curl

```bash
curl -X POST https://your-bms-host/api/machine-logs \
  -H "Content-Type: application/json" \
  -H "X-Device-Key: 7f3c9e2a1b8d4f6e0a5c3b9d7e1f2a4c6b8d0e2f" \
  -d '{
    "log_type": "sensor_reading",
    "temperature": 42.7,
    "humidity": 55.2,
    "logged_at": "2026-09-26T14:30:00+08:00"
  }'
```

## 6. Example: ESP32 / Arduino (HTTPClient)

```cpp
#include <HTTPClient.h>
#include <WiFi.h>

const char* deviceKey = "7f3c9e2a1b8d4f6e0a5c3b9d7e1f2a4c6b8d0e2f";
const char* endpoint  = "https://your-bms-host/api/machine-logs";

void sendReading(float temperatureC, float humidityPct) {
  HTTPClient http;
  http.begin(endpoint);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Device-Key", deviceKey);

  String body = String("{\"log_type\":\"sensor_reading\",\"temperature\":") + temperatureC
              + ",\"humidity\":" + humidityPct + "}";

  int status = http.POST(body);
  if (status == 201) {
    // accepted
  } else if (status == 401) {
    // key invalid/revoked — device needs a new key from an admin
  } else if (status == 429) {
    // rate limited — back off and retry later
  } else {
    // 422 = payload rejected, check the response body for which field failed
  }
  http.end();
}
```

## 7. Responses

| Status | Meaning | Body |
|---|---|---|
| `201 Created` | Reading stored | `{"success": true, "message": "...", "data": { ...the created log... }}` |
| `401 Unauthorized` | Missing or invalid `X-Device-Key` | `{"success": false, "message": "Missing X-Device-Key header."}` or `"Invalid device key."` |
| `422 Unprocessable Entity` | A field failed validation | Standard Laravel validation error body: `{"message": "...", "errors": {"field": ["reason"]}}` |
| `429 Too Many Requests` | Rate limit exceeded (120/min) | Standard Laravel throttle response |

## 8. Retry guidance

- On `401`: stop retrying with the same key — it's been revoked or was never valid. The device needs a new key issued from the web UI.
- On `429`: back off (e.g. exponential backoff starting at a few seconds) and retry.
- On `5xx` or a network error: retry with backoff; consider buffering readings locally if the device may be offline for extended periods, and replaying them with their original `logged_at` timestamp once connectivity returns.
- There is currently no idempotency key — sending the same reading twice creates two rows. This is acceptable for telemetry but worth knowing if you build retry logic.
