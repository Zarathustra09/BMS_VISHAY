// Live alerts over the self-hosted Laravel Reverb server (see echo.js).
//
// Two sources, both private channels:
//   - `alerts`                    -> App\Events\AlertChanged: every raise / escalate / acknowledge /
//                                    resolve, for everyone who may view alerts. Re-emitted as an
//                                    `alert:changed` window event that each page listens to.
//   - `App.Models.User.{id}`      -> database notifications written for THIS user (the bell).
//
// Reverb is an add-on: while the socket is not connected the same refreshes run on a timer, so the
// pages stay current (a few seconds late) instead of silently going stale, and the header shows
// whether updates are live.

const live = document.querySelector('meta[name="live-alerts"]');
const POLL_MS = 15000;

let connected = false;
let pollTimer = null;

if (live) {
    const userId = document.querySelector('meta[name="live-user-id"]')?.content;

    const start = () => {
        const echo = window.Echo;
        if (!echo) return;

        echo.private('alerts').listen('.alert.changed', (event) => {
            window.dispatchEvent(new CustomEvent('alert:changed', { detail: event }));
        });

        if (userId) {
            echo.private(`App.Models.User.${userId}`).notification(addToBell);
        }

        const connection = echo.connector.pusher.connection;
        connection.bind('state_change', ({ current }) => setConnectionState(current));
        setConnectionState(connection.state);
    };

    if (window.Echo) {
        start();
    } else {
        // Echo was not created (no Reverb key) or has not loaded yet - poll until it shows up.
        window.addEventListener('echo:ready', start, { once: true });
        setConnectionState('unavailable');
    }

    window.addEventListener('alert:changed', (e) => {
        const alert = e.detail ?? {};

        if (['raised', 'escalated'].includes(alert.action)) {
            showToast(alert);
        }

        refreshMachinePanel(alert);
        refreshDashboard();
        refreshSidebarCount();
        reloadAlertPage(alert);
    });
}

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[c]));

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const capped = (n) => (n > 99 ? '99+' : String(n));

// ---- connection state + polling fallback ---------------------------------------------------

function setConnectionState(state) {
    const wasConnected = connected;
    connected = state === 'connected';

    const pill = document.getElementById('liveStatus');
    if (pill) {
        const dot = pill.querySelector('[data-live-dot]');
        const text = pill.querySelector('[data-live-text]');
        pill.classList.remove('hidden');
        pill.classList.add('flex');
        dot.className = `w-2 h-2 rounded-full ${connected ? 'bg-success' : (state === 'connecting' ? 'bg-warning animate-pulse' : 'bg-error')}`;
        text.textContent = connected ? 'Live' : (state === 'connecting' ? 'Connecting…' : 'Offline · refreshing');
        pill.title = connected
            ? 'Alerts and readings update the moment they happen.'
            : 'The live connection is down. The page refreshes itself every few seconds instead.';
    }

    if (connected) {
        clearInterval(pollTimer);
        pollTimer = null;
        // Anything missed while the socket was down.
        if (!wasConnected) refreshEverything();
    } else if (!pollTimer) {
        pollTimer = setInterval(() => {
            if (!document.hidden) refreshEverything();
        }, POLL_MS);
    }
}

// Same refreshes as a real event, without an alert to name - used after a reconnect and while polling.
function refreshEverything() {
    window.dispatchEvent(new CustomEvent('alert:changed', { detail: { poll: true } }));
}

// ---- toast ---------------------------------------------------------------------------------

function showToast(alert) {
    const container = document.getElementById('liveToasts');
    if (!container) return;

    const critical = alert.severity === 'critical';
    const bell = document.getElementById('notificationBell');
    const url = bell && alert.alert_id ? bell.dataset.alertUrl.replace(':id', alert.alert_id) : '#';

    const toast = document.createElement('a');
    toast.href = url;
    toast.className = `alert ${critical ? 'alert-error' : 'alert-warning'} shadow-lg flex-col items-start gap-0`;
    toast.innerHTML = `
        <span class="font-bold">${critical ? 'Critical' : 'Warning'}: ${escapeHtml(alert.machine_name ?? 'Machine')}</span>
        <span class="text-sm">${escapeHtml(alert.message)}</span>`;

    container.appendChild(toast);
    setTimeout(() => toast.remove(), 8000);
}

// ---- bell + sidebar ------------------------------------------------------------------------

function addToBell(notification) {
    const bell = document.getElementById('notificationBell');
    const list = document.getElementById('bellList');
    if (!bell || !list) return;

    document.getElementById('bellEmpty')?.remove();

    const severity = notification.severity === 'critical' ? 'error' : (notification.severity === 'info' ? 'info' : 'warning');
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = bell.dataset.readUrl.replace(':id', notification.id);
    form.innerHTML = `
        <input type="hidden" name="_token" value="${escapeHtml(csrfToken())}">
        <button type="submit" class="alert alert-${severity} w-full text-left">
            <div class="text-sm">
                <div class="font-semibold">${escapeHtml(notification.machine_name ?? 'Machine')}</div>
                <div class="opacity-70">${escapeHtml(notification.message ?? '')}</div>
            </div>
        </button>`;
    list.prepend(form);

    const count = document.getElementById('bellCount');
    const next = ((count ? parseInt(count.dataset.count || '0', 10) : 0) || 0) + 1;

    if (count) {
        count.dataset.count = String(next);
        count.textContent = `${next} new`;
        count.classList.remove('hidden');
    }

    const badge = document.getElementById('bellBadge');
    if (badge) {
        badge.textContent = capped(next);
        badge.classList.remove('hidden');
    }
}

// The sidebar's Alerts badge counts OPEN alerts, so it follows the same counters the Alerts page uses.
function refreshSidebarCount() {
    const badge = document.getElementById('sidebarAlertCount');
    if (!badge?.dataset.statsUrl) return;

    fetch(badge.dataset.statsUrl, { headers: { Accept: 'application/json' } })
        .then((r) => (r.ok ? r.json() : Promise.reject()))
        .then((stats) => {
            badge.textContent = capped(stats.open);
            badge.classList.toggle('hidden', stats.open === 0);
        })
        .catch(() => {});
}

// ---- pages ---------------------------------------------------------------------------------

// The machine page: the Alerts tab (active alerts + trail), the Overview's "needs attention" card and
// every open-alert counter (tab badge, KPI) come from one JSON response.
function refreshMachinePanel(alert) {
    const panel = document.getElementById('machineAlertsPanel');
    if (!panel?.dataset.panelUrl) return;
    if (!alert.poll && String(panel.dataset.machineId) !== String(alert.machine_id)) return;

    fetch(panel.dataset.panelUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then((r) => (r.ok ? r.json() : Promise.reject()))
        .then((data) => {
            const fill = (id, html) => { const el = document.getElementById(id); if (el) el.innerHTML = html; };
            fill('machine-alerts-active', data.active_html);
            fill('machine-alerts-trail', data.trail_html);
            fill('machine-alerts-compact', data.compact_html);

            document.querySelectorAll('[data-open-alert-count]').forEach((el) => {
                el.textContent = data.open_count;
                if (el.classList.contains('badge')) {
                    el.classList.toggle('hidden', data.open_count === 0);
                } else {
                    el.classList.toggle('text-error', data.open_count > 0);
                    el.classList.toggle('text-success', data.open_count === 0);
                    const desc = el.parentElement?.querySelector('.stat-desc');
                    if (desc) desc.textContent = data.open_count > 0 ? 'Need attention' : 'All clear';
                }
            });
        })
        .catch(() => {});
}

// The dashboard's alert counters and Recent Alerts list.
function refreshDashboard() {
    const list = document.getElementById('recentAlertsList');
    const hasStats = document.querySelector('[data-live-stat]');
    if (!list && !hasStats) return;

    const url = list?.dataset.liveRecentUrl;
    if (!url) return;

    fetch(url, { headers: { Accept: 'application/json' } })
        .then((r) => (r.ok ? r.json() : Promise.reject()))
        .then((data) => {
            document.querySelectorAll('[data-live-stat]').forEach((el) => {
                const value = data[el.dataset.liveStat];
                if (value !== undefined) el.textContent = value;
            });
            if (list) list.innerHTML = data.recent_html;
        })
        .catch(() => {});
}

// An open alert's own page just reloads - its detail card and trail both changed.
function reloadAlertPage(alert) {
    const page = document.querySelector('[data-live-alert-page]');
    if (page && !alert.poll && String(page.dataset.alertId) === String(alert.alert_id)) {
        window.location.reload();
    }
}
