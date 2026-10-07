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

Only `log_type` is required. **Sensor values do not go at the top level** — they go in a `readings` object, keyed by the parameter keys defined for *this machine* (see below).

| Field | Type | Constraints | Notes |
|---|---|---|---|
| `log_type` | string | **required.** One of: `status_change`, `maintenance`, `alarm`, `sensor_reading`, `operation`, `error` | Use `sensor_reading` for routine telemetry |
| `readings` | object | keys must be this machine's parameter keys | `{"temperature": 42.7, "door_open": false}` — see [Readings](#readings-and-machine-parameters) |
| `event` | string | max 255 chars | short human-readable description |
| `severity` | string | one of `info`, `warning`, `error`, `critical` | defaults to `info` if omitted |
| `previous_status` / `current_status` | string | max 255 chars | for `status_change` logs |
| `notes` | string | max 65535 chars | |
| `performed_by` | string | max 255 chars | e.g. device identifier, if useful for debugging |
| `logged_at` | ISO 8601 date/time string | | defaults to the server's current time if omitted |

Any other top-level field is ignored. The old fixed sensor fields (`temperature`, `humidity`, `pressure`, `power_consumption`, `voltage`, `current`, `frequency`, `runtime_hours`, `vibration_rms`, `bearing_temp`, `oil_level`) are **no longer accepted at the top level** — posting one returns `422` pointing you at `readings`, rather than storing nothing and answering `201`.

### Readings and machine parameters

What a machine can report is defined per machine, not by the API: on the machine's **edit page → Sensor Parameters**, a user adds one parameter per reading (e.g. "Coolant temperature", unit °C). A new machine can copy another machine's parameters instead of starting from scratch. The machine's **show page → API Integration** card then lists the exact keys, an example body, a copy-ready `curl` command and example responses for that machine — start there when wiring up a device.

| Parameter setting | Effect on the API |
|---|---|
| `key` | The name to use inside `readings`. Lowercase letters, digits and underscores; generated from the label, never changes after the parameter is saved |
| `data_type` = `number` | Value must be numeric (`42.7` or `"42.7"`); stored as a number |
| `data_type` = `boolean` | Value must be `true`/`false`/`1`/`0`; stored as a real boolean |
| `data_type` = `text` | Value must be a string, max 255 chars |
| `min` / `max` (number only) | A value outside the range is rejected (`422`) |
| `required` | Must be present in every `sensor_reading` log (not enforced for other log types, so an `alarm` log isn't rejected for lacking telemetry) |

- An **unknown key** in `readings` is rejected with `422`, and the message lists the keys this machine accepts — a typo can't silently store nothing.
- A `null` value is dropped rather than stored.
- A machine with no parameters accepts no readings at all (any `readings` key is "unknown").

## 5. Example: curl

```bash
curl -X POST https://your-bms-host/api/machine-logs \
  -H "Content-Type: application/json" \
  -H "X-Device-Key: 7f3c9e2a1b8d4f6e0a5c3b9d7e1f2a4c6b8d0e2f" \
  -d '{
    "log_type": "sensor_reading",
    "readings": {
      "temperature": 42.7,
      "humidity": 55.2
    },
    "logged_at": "2026-09-26T14:30:00+08:00"
  }'
```

(`temperature` and `humidity` only work if the machine has parameters with those keys.)

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

  String body = String("{\"log_type\":\"sensor_reading\",\"readings\":{\"temperature\":") + temperatureC
              + ",\"humidity\":" + humidityPct + "}}";

  int status = http.POST(body);
  if (status == 201) {
    // accepted
  } else if (status == 401) {
    // key invalid/revoked — device needs a new key from an admin
  } else if (status == 429) {
    // rate limited — back off and retry later
  } else {
    // 422 = payload rejected, check the response body for which reading failed
  }
  http.end();
}
```
## 7. Responses

| Status | Meaning | Body |
|---|---|---|
| `201 Created` | Reading stored | `{"success": true, "message": "...", "data": { ...the created log... }}` |
| `401 Unauthorized` | Missing or invalid `X-Device-Key` | `{"success": false, "message": "Missing X-Device-Key header."}` or `"Invalid device key."` |
| `422 Unprocessable Entity` | A field or reading failed validation (unknown parameter key, wrong type, outside min/max, missing required parameter, or a sensor value sent at the top level) | Standard Laravel validation error body: `{"message": "...", "errors": {"readings.temperature": ["reason"]}}` — an unknown key is reported under `readings` |
| `429 Too Many Requests` | Rate limit exceeded (120/min) | Standard Laravel throttle response |

## 8. Threshold alerts (Phase 5)

Every stored reading is checked against the alert thresholds on that machine's number [parameters](03-data-model.md#machine_parameters) (set on the machine's edit page in the web UI — there's no API for this) before the response is returned. This happens synchronously in `MachineLogService::store()`, so it doesn't add a separate request, but it does mean a reading that breaches a critical bound will queue an admin/manager notification (database + email) before your `201` comes back.

- The `severity` field you send is independent of this — it's your own event classification (e.g. a device might send `severity: "info"` for a routine reading that still happens to breach a threshold). Alerts are driven entirely by the numeric parameter values against the configured bounds, not by whatever `severity` you pass.
- Sending the same out-of-range value repeatedly does **not** create a new alert each time — there's at most one open alert per machine per parameter, and it just gets its `value`/timestamp updated. You'll only trigger a fresh notification if the reading escalates the alert from `warning` to `critical`.
- A reading back within both the warning and critical bounds automatically resolves whatever alert was open for that parameter — no separate "all clear" call needed.
- See [02-architecture.md](02-architecture.md) for the full evaluate/escalate/resolve sequence, and [06-security.md](06-security.md) for who receives the notification.

## 9. Offline detection (Phase 6)

If a device stops POSTing entirely, the platform notices on its own — there's nothing extra for the device to send. A scheduled check runs every minute and raises an "offline" alert (in-app + email, same as a threshold alert) for any machine that hasn't had an accepted POST in the last 10 minutes. A machine that has never sent a single reading is **not** flagged this way — offline detection only applies once a device has proven it can report at least once.

- Any successful POST clears the offline alert automatically, immediately, on that same request — including a request that's just replaying a backlog of old, buffered readings after a reconnect. `last_seen_at` is stamped to *now* (when the request arrived), not to the reading's own timestamp, so replaying old data correctly marks the device as back online even though the data itself is old.
- There's no way to mark a device offline early or extend the window from the API side — the 10-minute threshold is a server-side setting (`AlertInterface::OFFLINE_AFTER_MINUTES`, overridable per-run via `--minutes` on the underlying command, not per-device).
- A machine explicitly set to `inactive` status in the web UI is excluded from this check — expected to be silent, so it won't page anyone.

## 10. Retry guidance

- On `401`: stop retrying with the same key — it's been revoked or was never valid. The device needs a new key issued from the web UI.
- On `429`: back off (e.g. exponential backoff starting at a few seconds) and retry.
- On `5xx` or a network error: retry with backoff; consider buffering readings locally if the device may be offline for extended periods, and replaying them with their original `logged_at` timestamp once connectivity returns.
- There is currently no idempotency key — sending the same reading twice creates two rows. This is acceptable for telemetry but worth knowing if you build retry logic.
