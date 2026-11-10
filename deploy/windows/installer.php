<?php

/**
 * Helper behind deploy\windows\install.bat and test-install.bat (Windows, XAMPP).
 *
 * Plain PHP on purpose: everything except `test` has to work before `composer install` has
 * created vendor/, and `test` keeps reporting even when the app itself fails to boot.
 *
 *   php installer.php check --httpd=<httpd.exe> --nssm=<nssm.exe>   prerequisites, changes nothing
 *   php installer.php php-ini                                        offer to switch on OPcache and intl
 *   php installer.php configure                                      ask the questions, write .env, create the database
 *   php installer.php web-port                                       print the website port (from APP_URL)
 *   php installer.php get KEY                                        print one .env value
 *   php installer.php apache --httpd=<httpd.exe>                     write the Apache site and point httpd.conf at it
 *   php installer.php test [--no-post]                               smoke-test a running install
 *
 * Exit code 0 = fine, 1 = something to fix (already explained on screen).
 */
const APP_ROOT = __DIR__.'/../..';

/** Reverb's port in this installer's layout, the same as Docker: the website owns 80/8080. */
const REVERB_PORT = 8081;

const DEFAULT_DEVICE_KEY = 'nodemcu-demo-key';

const SERVICES = ['Apache2.4', 'BMS-Reverb', 'BMS-Queue', 'BMS-Scheduler'];

const WEB_FIREWALL_RULE = 'LogXense website (LAN)';

const REVERB_FIREWALL_RULE = 'LogXense Reverb (LAN)';

exit(main(array_slice($argv, 1)));

/**
 * @param  list<string>  $arguments
 */
function main(array $arguments): int
{
    $command = $arguments[0] ?? '';
    $options = parseOptions(array_slice($arguments, 1));

    return match ($command) {
        'check' => checkPrerequisites($options),
        'php-ini' => enablePhpExtensions(),
        'configure' => configure(),
        'web-port' => printWebPort(),
        'get' => printEnvValue($arguments[1] ?? ''),
        'apache' => writeApacheSite($options),
        'test' => smokeTest($options),
        default => usage(),
    };
}

function usage(): int
{
    fwrite(STDERR, "Usage: php installer.php check|php-ini|configure|web-port|get|apache|test [options]\n");

    return 1;
}

/**
 * @param  list<string>  $arguments
 * @return array<string, string|bool>
 */
function parseOptions(array $arguments): array
{
    $options = [];

    foreach ($arguments as $argument) {
        if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $argument, $match)) {
            $options[$match[1]] = $match[2] ?? true;
        }
    }

    return $options;
}

// ---- output ----------------------------------------------------------------

function ok(string $message): void
{
    echo "[ok] {$message}\n";
}

function bad(string $message): void
{
    echo "[xx] {$message}\n";
}

function note(string $message): void
{
    echo "[--] {$message}\n";
}

function ask(string $question, string $default = ''): string
{
    $suffix = $default === '' ? '' : " [{$default}]";
    echo "     {$question}{$suffix}: ";
    $answer = fgets(STDIN);

    if ($answer === false) {
        echo "\n";

        return $default;
    }

    // A byte-order mark arrives when the answers are piped in from PowerShell.
    $answer = trim(str_replace("\u{FEFF}", '', $answer));

    return $answer === '' ? $default : $answer;
}

function confirm(string $question, bool $default = true): bool
{
    $answer = strtolower(ask($question.($default ? ' (Y/n)' : ' (y/N)')));

    if ($answer === '') {
        return $default;
    }

    return str_starts_with($answer, 'y');
}

/**
 * Reads a password without showing it. LOGXENSE_DB_PASSWORD, when set, is used instead of
 * asking (for an unattended run).
 */
function askSecret(string $question): string
{
    $fromEnvironment = getenv('LOGXENSE_DB_PASSWORD');

    if ($fromEnvironment !== false) {
        return $fromEnvironment;
    }

    echo "     {$question} (typing is hidden, Enter for none): ";

    $script = '$ProgressPreference = "SilentlyContinue"; $s = Read-Host -AsSecureString; '
        .'$b = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($s); '
        .'Write-Output ("<<" + [Runtime.InteropServices.Marshal]::PtrToStringBSTR($b) + ">>")';
    // Not -NonInteractive: that switches Read-Host off.
    $output = shell_exec('powershell -NoProfile -EncodedCommand '.base64_encode(mb_convert_encoding($script, 'UTF-16LE', 'UTF-8')));

    if (is_string($output) && preg_match('/<<(.*)>>/s', $output, $match)) {
        echo "\n";

        return $match[1];
    }

    echo "\n     (Could not hide the typing on this console - the password will be visible.)\n";

    return ask($question);
}

// ---- .env -------------------------------------------------------------------

function envPath(): string
{
    return APP_ROOT.'/.env';
}

/**
 * @return array<string, string>
 */
function readEnv(string $contents): array
{
    $values = [];

    foreach (preg_split('/\r\n|\n/', $contents) as $line) {
        if (! preg_match('/^\s*([A-Z0-9_]+)\s*=(.*)$/', $line, $match)) {
            continue;
        }

        $value = trim($match[2]);

        if (strlen($value) >= 2 && $value[0] === "'" && str_ends_with($value, "'")) {
            $value = substr($value, 1, -1);
        } elseif (strlen($value) >= 2 && $value[0] === '"' && str_ends_with($value, '"')) {
            $value = stripcslashes(substr($value, 1, -1));
        } else {
            $value = preg_replace('/\s+#.*$/', '', $value);
        }

        $values[$match[1]] = $value;
    }

    return $values;
}

/**
 * Quotes a value so phpdotenv reads it back exactly: bare when harmless, single quotes (taken
 * literally, so a "$" in a password is never expanded) otherwise, double quotes with escapes
 * only when the value itself holds a single quote.
 */
function quoteEnv(string $value): string
{
    if ($value === '' || preg_match('/^[A-Za-z0-9_.:\/@+=,-]+$/', $value)) {
        return $value;
    }

    if (! str_contains($value, "'")) {
        return "'{$value}'";
    }

    return '"'.addcslashes($value, '\\"$').'"';
}

/**
 * Sets KEY in the .env text, replacing its line or appending it. $raw writes the value as given
 * (for "${OTHER}" references).
 */
function setEnv(string $contents, string $key, string $value, bool $raw = false): string
{
    $line = $key.'='.($raw ? $value : quoteEnv($value));
    $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

    if (preg_match($pattern, $contents)) {
        return preg_replace_callback($pattern, fn () => $line, $contents, 1);
    }

    return rtrim($contents, "\r\n")."\n{$line}\n";
}

function webPortFromUrl(string $url): ?int
{
    $parts = parse_url($url);

    if (! is_array($parts) || ! isset($parts['host'])) {
        return null;
    }

    return $parts['port'] ?? 80;
}

function webUrl(string $address, int $port): string
{
    return 'http://'.$address.($port === 80 ? '' : ":{$port}");
}

function printEnvValue(string $key): int
{
    if (! is_file(envPath())) {
        return 1;
    }

    echo readEnv(file_get_contents(envPath()))[$key] ?? '';

    return 0;
}

function printWebPort(): int
{
    if (! is_file(envPath())) {
        fwrite(STDERR, ".env not found\n");

        return 1;
    }

    $port = webPortFromUrl(readEnv(file_get_contents(envPath()))['APP_URL'] ?? '');

    if ($port === null) {
        fwrite(STDERR, "APP_URL in .env is not a URL\n");

        return 1;
    }

    echo $port;

    return 0;
}

// ---- check ------------------------------------------------------------------

/**
 * @param  array<string, string|bool>  $options
 */
function checkPrerequisites(array $options): int
{
    $problems = 0;

    if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
        ok('PHP '.PHP_VERSION.'    '.PHP_BINARY);
    } else {
        bad('PHP '.PHP_VERSION.' is too old - LogXense needs PHP 8.2 or newer (XAMPP 8.2).');
        $problems++;
    }

    $required = ['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'gd', 'hash', 'iconv', 'json', 'libxml', 'mbstring',
        'openssl', 'pcre', 'pdo_mysql', 'session', 'simplexml', 'tokenizer', 'xml', 'xmlreader', 'xmlwriter', 'zip', 'zlib'];
    $missing = array_values(array_filter($required, fn (string $extension) => ! extension_loaded($extension)));

    if ($missing === []) {
        ok('PHP extensions: all '.count($required).' required ones are on');
    } else {
        bad('PHP extensions missing: '.implode(', ', $missing).' - enable them in '.php_ini_loaded_file());
        $problems++;
    }

    foreach (['Zend OPcache' => 'opcache', 'intl' => 'intl'] as $loadedName => $name) {
        if (! extension_loaded($loadedName)) {
            note("Recommended PHP extension {$name} is off - the installer offers to switch it on next");
        }
    }

    $problems += checkTool('Composer', 'composer', 'https://getcomposer.org/download/');

    $node = trim((string) shell_exec('node -v 2>nul'));

    if ($node === '') {
        bad('Node.js not found - install the LTS from https://nodejs.org/');
        $problems++;
    } elseif (! nodeVersionIsSupported($node)) {
        bad("Node.js {$node} is too old - the build needs 20.19+ or 22.12+ (https://nodejs.org/)");
        $problems++;
    } else {
        ok("Node.js {$node}");
    }

    $problems += checkTool('npm', 'npm', 'https://nodejs.org/ (it comes with Node.js)');

    $httpd = (string) ($options['httpd'] ?? '');

    if ($httpd !== '' && is_file($httpd)) {
        preg_match('/Apache\/([\d.]+)/', (string) shell_exec('"'.$httpd.'" -v 2>&1'), $version);
        ok('Apache '.($version[1] ?? '').'    '.$httpd);
    } else {
        bad('XAMPP Apache not found (C:\xampp\apache\bin\httpd.exe) - install XAMPP from https://www.apachefriends.org/');
        $problems++;
    }

    $nssm = (string) ($options['nssm'] ?? '');

    if ($nssm !== '' && is_file($nssm)) {
        ok('NSSM    '.$nssm);
    } else {
        bad('NSSM not found - run "winget install NSSM.NSSM", then copy the 64-bit nssm.exe to C:\tools\nssm\nssm.exe');
        $problems++;
    }

    if (! is_file(APP_ROOT.'/artisan')) {
        bad('No artisan file in '.realpath(APP_ROOT).' - run this from deploy\windows inside the project');
        $problems++;
    }

    $mysql = @fsockopen('127.0.0.1', 3306, $errorNumber, $errorText, 2);

    if ($mysql) {
        fclose($mysql);
        ok('Something answers on 127.0.0.1:3306 (MySQL) - the login is checked in the next step');
    } else {
        note('Nothing on 127.0.0.1:3306 - fine if MySQL is on another PC or port, otherwise install MySQL Community Server 8 (https://dev.mysql.com/downloads/mysql/)');
    }

    echo "\n";

    if ($problems > 0) {
        echo "{$problems} problem(s) to fix first. Install what is missing, open a NEW command prompt (so PATH is fresh) and run this again.\n";

        return 1;
    }

    echo "Prerequisites are fine.\n";

    return 0;
}

function checkTool(string $label, string $command, string $where): int
{
    $path = strtok((string) shell_exec("where {$command} 2>nul"), "\r\n");

    if ($path === false || $path === '') {
        bad("{$label} not found - {$where}");

        return 1;
    }

    ok("{$label}    {$path}");

    return 0;
}

/**
 * Vite 7 needs ^20.19.0 or >=22.12.0.
 */
function nodeVersionIsSupported(string $version): bool
{
    if (! preg_match('/^v?(\d+)\.(\d+)/', $version, $match)) {
        return false;
    }

    [$major, $minor] = [(int) $match[1], (int) $match[2]];

    return ($major === 20 && $minor >= 19) || ($major === 22 && $minor >= 12) || $major >= 23;
}

// ---- php.ini ------------------------------------------------------------------

/**
 * XAMPP ships with OPcache and intl switched off. OPcache makes every page (and every device
 * POST) much cheaper under Apache; intl is what Laravel's number and locale helpers use.
 */
function enablePhpExtensions(): int
{
    $ini = php_ini_loaded_file();
    $lines = [
        'Zend OPcache' => ['/^;\s*(zend_extension\s*=\s*"?(?:php_)?opcache(?:\.dll)?"?)\s*$/m', '/^;\s*(opcache\.enable\s*=\s*1)\s*$/m'],
        'intl' => ['/^;\s*(extension\s*=\s*"?(?:php_)?intl(?:\.dll)?"?)\s*$/m'],
    ];
    $off = array_values(array_filter(array_keys($lines), fn (string $name) => ! extension_loaded($name)));

    if ($off === []) {
        ok('OPcache and intl are already on');

        return 0;
    }

    if ($ini === false) {
        note('No php.ini is loaded, so OPcache/intl stay off (the app still works, just slower)');

        return 0;
    }

    $names = implode(' and ', array_map(fn (string $name) => $name === 'Zend OPcache' ? 'OPcache' : $name, $off));

    if (! confirm("Switch on {$names} in {$ini}? Recommended")) {
        note("{$names} left off");

        return 0;
    }

    $modulesBefore = (string) shell_exec('"'.PHP_BINARY.'" -m 2>&1');
    $original = file_get_contents($ini);
    $contents = $original;

    foreach ($off as $name) {
        foreach ($lines[$name] as $pattern) {
            $contents = preg_replace($pattern, '$1', $contents, 1);
        }
    }

    if ($contents === $original) {
        note("Could not find the {$names} lines in {$ini} - enable them by hand if you want them");

        return 0;
    }

    $backup = $ini.'.logxense-backup';

    if (! is_file($backup)) {
        copy($ini, $backup);
    }

    file_put_contents($ini, $contents);

    // A DLL that fails to load only shows as a startup warning, so check from a fresh process.
    $modulesAfter = (string) shell_exec('"'.PHP_BINARY.'" -m 2>&1');
    $nowLoaded = array_filter($off, fn (string $name) => preg_match('/^'.preg_quote($name, '/').'\s*$/mi', $modulesAfter));

    if (count($nowLoaded) !== count($off) || substr_count($modulesAfter, 'Warning') > substr_count($modulesBefore, 'Warning')) {
        file_put_contents($ini, $original);
        bad("PHP could not load {$names}, so php.ini was put back as it was:\n".trim($modulesAfter));

        return 0;
    }

    ok("{$names} switched on (backup: {$backup})");

    return 0;
}

// ---- configure -----------------------------------------------------------------

function configure(): int
{
    $isFresh = ! is_file(envPath());
    $contents = $isFresh ? file_get_contents(APP_ROOT.'/.env.example') : file_get_contents(envPath());
    $env = readEnv($contents);

    echo $isFresh
        ? "     No .env yet - a new one is made from .env.example. Press Enter to accept [defaults].\n\n"
        : "     Using the existing .env (a backup is kept). Press Enter to keep [current values].\n\n";

    $currentPort = $isFresh ? null : webPortFromUrl($env['APP_URL'] ?? '');

    while (true) {
        $webPort = ask('Website port - 80, or 8080 if 80 is taken', (string) ($currentPort ?? 80));

        if (ctype_digit($webPort) && (int) $webPort > 0 && (int) $webPort < 65536 && (int) $webPort !== REVERB_PORT) {
            $webPort = (int) $webPort;
            break;
        }

        bad('Enter a port number other than '.REVERB_PORT.' (that one is for live updates)');
    }

    $currentHost = $isFresh ? null : parse_url($env['APP_URL'] ?? '', PHP_URL_HOST);
    $defaultAddress = in_array($currentHost, [null, false, 'localhost', '127.0.0.1'], true) ? detectLanAddress() : $currentHost;
    $address = ask("This PC's address on the network (its fixed IP)", $defaultAddress);

    [$database, $changedDatabase] = askDatabase($env, $isFresh);

    if ($database === null) {
        return 1;
    }

    $edition = $env['APP_EDITION'] ?? 'full';

    if ($isFresh) {
        $edition = strtolower(ask('Edition - lite (machines, alerts, users, sensor API) or full (adds buildings, work orders, maintenance, reports)', 'lite')) === 'full' ? 'full' : 'lite';
    }

    $production = $isFresh
        || (($env['APP_ENV'] ?? '') === 'production' && ($env['APP_DEBUG'] ?? '') === 'false')
        || confirm('This .env is in '.($env['APP_ENV'] ?? '?').' mode with APP_DEBUG='.($env['APP_DEBUG'] ?? '?').'. Switch it to production (hides error details from visitors)?');

    $contents = setEnv($contents, 'APP_URL', webUrl($address, $webPort));

    if ($production) {
        $contents = setEnv($contents, 'APP_ENV', 'production');
        $contents = setEnv($contents, 'APP_DEBUG', 'false');
    }

    if (($env['APP_KEY'] ?? '') === '') {
        $contents = setEnv($contents, 'APP_KEY', 'base64:'.base64_encode(random_bytes(32)));
    }

    if ($isFresh || $changedDatabase) {
        foreach (['DB_CONNECTION' => 'mysql', 'DB_HOST' => $database['host'], 'DB_PORT' => $database['port'],
            'DB_DATABASE' => $database['name'], 'DB_USERNAME' => $database['user'], 'DB_PASSWORD' => $database['password']] as $key => $value) {
            $contents = setEnv($contents, $key, $value);
        }
    }

    if ($isFresh) {
        $contents = setEnv($contents, 'APP_EDITION', $edition);
        $contents = setEnv($contents, 'LOG_LEVEL', 'warning');
        $contents = setEnv($contents, 'DEMO_DEVICE_KEY', DEFAULT_DEVICE_KEY);
    }

    // Live updates: Reverb on 8081 (the website has 80/8080), with real secrets.
    foreach (['REVERB_APP_KEY' => 10, 'REVERB_APP_SECRET' => 20] as $key => $bytes) {
        if (in_array($env[$key] ?? '', ['', 'change-me-key', 'change-me-secret'], true)) {
            $contents = setEnv($contents, $key, bin2hex(random_bytes($bytes)));
        }
    }

    $contents = setEnv($contents, 'REVERB_APP_ID', ($env['REVERB_APP_ID'] ?? '') ?: 'bms');
    $contents = setEnv($contents, 'REVERB_HOST', '127.0.0.1');
    $contents = setEnv($contents, 'REVERB_PORT', (string) REVERB_PORT);
    $contents = setEnv($contents, 'REVERB_SCHEME', 'http');
    $contents = setEnv($contents, 'REVERB_SERVER_HOST', '0.0.0.0');
    $contents = setEnv($contents, 'REVERB_SERVER_PORT', (string) REVERB_PORT);
    $contents = setEnv($contents, 'BROADCAST_CONNECTION', 'reverb');

    foreach (['VITE_REVERB_APP_KEY' => '"${REVERB_APP_KEY}"', 'VITE_REVERB_PORT' => '"${REVERB_PORT}"', 'VITE_REVERB_SCHEME' => '"${REVERB_SCHEME}"'] as $key => $reference) {
        $contents = setEnv($contents, $key, $reference, raw: true);
    }

    // Blank: the browser connects to whichever address it opened the site from.
    $contents = setEnv($contents, 'VITE_REVERB_HOST', '');

    if (! $isFresh) {
        $backup = envPath().'.backup-'.date('Ymd-His');
        copy(envPath(), $backup);
        note('Previous .env saved as '.basename($backup));
    }

    file_put_contents(envPath(), $contents);

    echo "\n";
    ok('.env written: '.webUrl($address, $webPort).', database '.$database['name'].' on '.$database['host'].', live updates on port '.REVERB_PORT);

    return 0;
}

/**
 * Asks for (or confirms) the MySQL login, proves it works and creates the database.
 *
 * @param  array<string, string>  $env
 * @return array{0: array{host: string, port: string, name: string, user: string, password: string}|null, 1: bool}
 */
function askDatabase(array $env, bool $isFresh): array
{
    $database = [
        'host' => $env['DB_HOST'] ?? '127.0.0.1',
        'port' => $env['DB_PORT'] ?? '3306',
        'name' => $isFresh ? 'bms' : ($env['DB_DATABASE'] ?? 'bms'),
        'user' => $env['DB_USERNAME'] ?? 'root',
        'password' => $env['DB_PASSWORD'] ?? '',
    ];
    $changed = $isFresh;

    if (! $isFresh && ! confirm("Keep the database settings in .env ({$database['user']}@{$database['host']}:{$database['port']}, database {$database['name']})?")) {
        $changed = true;
    }

    for ($attempt = 1; $attempt <= 3; $attempt++) {
        if ($changed) {
            echo "\n     MySQL - the database server the app stores everything in:\n";
            $database['host'] = ask('Host', $database['host']);
            $database['port'] = ask('Port', $database['port']);
            $database['name'] = ask('Database name (created if missing)', $database['name']);
            $database['user'] = ask('User', $database['user']);
            $database['password'] = askSecret("Password for {$database['user']}");
        }

        $error = createDatabase($database);

        if ($error === null) {
            ok("MySQL login works and database {$database['name']} is ready");

            return [$database, $changed];
        }

        bad("Could not use MySQL: {$error}");
        $changed = true;

        if (getenv('LOGXENSE_DB_PASSWORD') !== false) {
            break;
        }
    }

    bad('Giving up on the database. Check that MySQL is running and the login is right, then run the installer again.');

    return [null, $changed];
}

/**
 * @param  array{host: string, port: string, name: string, user: string, password: string}  $database
 */
function createDatabase(array $database): ?string
{
    if (! preg_match('/^[A-Za-z0-9_]+$/', $database['name'])) {
        return 'the database name may only use letters, digits and _';
    }

    try {
        $pdo = new PDO("mysql:host={$database['host']};port={$database['port']}", $database['user'], $database['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (PDOException $exception) {
        return $exception->getMessage();
    }

    return null;
}

/**
 * The IPv4 address of the network adapter that has the default gateway - the one the LAN sees.
 */
function detectLanAddress(): string
{
    $script = '$ProgressPreference = "SilentlyContinue"; '
        .'(Get-NetIPConfiguration | Where-Object { $_.IPv4DefaultGateway -and $_.NetAdapter.Status -eq "Up" } '
        .'| Select-Object -First 1).IPv4Address.IPAddress';
    $address = trim((string) shell_exec('powershell -NoProfile -NonInteractive -EncodedCommand '.base64_encode(mb_convert_encoding($script, 'UTF-16LE', 'UTF-8')).' 2>nul'));
    $address = strtok($address, "\r\n");

    if (is_string($address) && filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return $address;
    }

    $byName = gethostbyname(gethostname());

    return filter_var($byName, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $byName : 'localhost';
}

// ---- apache ---------------------------------------------------------------------

/**
 * Writes conf/extra/httpd-logxense.conf (the site, on the port from APP_URL) and edits XAMPP's
 * httpd.conf so Apache listens only there: its own "Listen 80" and the HTTPS-on-443 include are
 * commented out (a busy 443 is the usual reason XAMPP's Apache will not start), and the site
 * file is included. A backup of httpd.conf is kept the first time. Safe to re-run.
 *
 * @param  array<string, string|bool>  $options
 */
function writeApacheSite(array $options): int
{
    $httpd = (string) ($options['httpd'] ?? '');
    $conf = dirname($httpd, 2).'/conf';

    if (! is_file("{$conf}/httpd.conf")) {
        bad("httpd.conf not found in {$conf}");

        return 1;
    }

    $env = readEnv(file_get_contents(envPath()));
    $port = webPortFromUrl($env['APP_URL'] ?? '') ?? 80;
    $host = parse_url($env['APP_URL'] ?? '', PHP_URL_HOST) ?: 'localhost';
    $public = realpath(APP_ROOT.'/public');

    if ($public === false) {
        bad('No public folder in '.realpath(APP_ROOT));

        return 1;
    }

    $public = str_replace('\\', '/', $public);

    $site = <<<CONF
# LogXense Enterprise - written by deploy\\windows\\install.bat. Run the installer again to change it.
Listen {$port}

<VirtualHost *:{$port}>
    ServerName {$host}
    DocumentRoot "{$public}"

    <Directory "{$public}">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # The sensor API is left out of the access log: a device posting 10 times a second would
    # add almost a million lines a day, and Apache on Windows never rotates this file.
    SetEnvIf Request_URI "^/api/machine-logs" logxense_device
    CustomLog "logs/logxense-access.log" common env=!logxense_device
    ErrorLog "logs/logxense-error.log"
</VirtualHost>

CONF;

    file_put_contents("{$conf}/extra/httpd-logxense.conf", str_replace("\n", "\r\n", $site));

    $original = file_get_contents("{$conf}/httpd.conf");
    $contents = preg_replace('/^(Listen\s+\S+)[ \t]*(\r?)$/m', '#$1    # LogXense: the port is set in conf/extra/httpd-logxense.conf$2', $original);
    $contents = preg_replace('/^(Include\s+"?conf\/extra\/httpd-ssl\.conf"?)[ \t]*(\r?)$/m', '#$1    # LogXense: no HTTPS on 443$2', $contents);

    if (! preg_match('/^Include\s+"?conf\/extra\/httpd-logxense\.conf"?\s*$/m', $contents)) {
        $contents = rtrim($contents, "\r\n")."\r\n\r\n# LogXense Enterprise site (deploy\\windows\\install.bat)\r\nInclude conf/extra/httpd-logxense.conf\r\n";
    }

    if ($contents !== $original) {
        if (! is_file("{$conf}/httpd.conf.logxense-backup")) {
            copy("{$conf}/httpd.conf", "{$conf}/httpd.conf.logxense-backup");
        }

        file_put_contents("{$conf}/httpd.conf", $contents);
    }

    exec('"'.$httpd.'" -t -d "'.dirname($httpd, 2).'" -f "'.$conf.'/httpd.conf" 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        bad("Apache rejects its configuration:\n     ".implode("\n     ", $output));

        return 1;
    }

    ok("Apache site: port {$port}, serving {$public}");

    return 0;
}

// ---- test -----------------------------------------------------------------------

/**
 * @param  array<string, string|bool>  $options
 */
function smokeTest(array $options): int
{
    $results = ['ok' => 0, 'bad' => 0];
    $pass = function (string $message) use (&$results): void {
        ok($message);
        $results['ok']++;
    };
    $fail = function (string $message) use (&$results): void {
        bad($message);
        $results['bad']++;
    };

    echo "Files\n";

    if (! is_file(envPath())) {
        $fail('.env is missing - run install.bat');

        return 1;
    }

    $env = readEnv(file_get_contents(envPath()));
    $webPort = webPortFromUrl($env['APP_URL'] ?? '') ?? 80;
    $address = parse_url($env['APP_URL'] ?? '', PHP_URL_HOST) ?: 'localhost';
    $reverbPort = ((int) ($env['REVERB_SERVER_PORT'] ?? 0)) ?: 8080;

    ($env['APP_KEY'] ?? '') !== '' ? $pass('.env has an APP_KEY') : $fail('APP_KEY in .env is empty');
    is_file(APP_ROOT.'/vendor/autoload.php') ? $pass('PHP packages installed (vendor)') : $fail('vendor\ is missing - run composer install');
    is_file(APP_ROOT.'/public/build/manifest.json') ? $pass('Front-end built (public\build)') : $fail('public\build is missing - run npm run build');
    is_file(APP_ROOT.'/public/hot') ? $fail('public\hot exists (left by "npm run dev"): pages would load from a dev server - delete it') : $pass('No public\hot file');

    if (($env['APP_DEBUG'] ?? '') === 'true') {
        note('APP_DEBUG=true: visitors see full error details - fine for testing, not for production');
    }

    echo "\nWindows services\n";

    foreach (SERVICES as $service) {
        $state = serviceState($service);

        if ($state === null) {
            $fail("{$service}: not installed");

            continue;
        }

        $automatic = str_contains((string) shell_exec("sc qc \"{$service}\" 2>nul"), 'AUTO_START');
        $state === 'RUNNING' && $automatic
            ? $pass("{$service}: running, starts with Windows")
            : $fail("{$service}: {$state}".($automatic ? '' : ', and NOT set to start with Windows'));
    }

    echo "\nApplication\n";

    $laravel = bootLaravel();
    $booted = $laravel === true;

    if ($booted) {
        laravelChecks($pass, $fail);
    } else {
        $fail("The app does not boot: {$laravel}");
    }

    echo "\nWebsite (port {$webPort})\n";

    [$status] = httpRequest('GET', "http://127.0.0.1:{$webPort}/up");
    $status === 200 ? $pass("http://127.0.0.1:{$webPort}/up answers 200") : $fail("http://127.0.0.1:{$webPort}/up answers ".($status ?: 'nothing'));

    [$status, $body] = httpRequest('GET', "http://127.0.0.1:{$webPort}/login");

    if ($status !== 200) {
        $fail('The login page answers '.($status ?: 'nothing'));
    } elseif (! str_contains($body, '/build/assets/')) {
        $fail('The login page does not load the built CSS/JS (public\build) - run npm run build, delete public\hot');
    } else {
        $pass('The login page loads, with the built CSS/JS');
    }

    if (! in_array($address, ['localhost', '127.0.0.1'], true)) {
        [$status] = httpRequest('GET', webUrl($address, $webPort).'/up');
        $status === 200
            ? $pass(webUrl($address, $webPort).' answers too (the address in APP_URL)')
            : $fail(webUrl($address, $webPort).' answers '.($status ?: 'nothing').' - is that still this PC\'s IP?');
    }

    echo "\nLive updates (Reverb, port {$reverbPort})\n";

    liveUpdateChecks($env, $webPort, $reverbPort, $address, $booted && ! isset($options['no-post']), $pass, $fail);

    echo "\nFirewall (other PCs and the ESP32)\n";

    foreach ([WEB_FIREWALL_RULE => $webPort, REVERB_FIREWALL_RULE => $reverbPort] as $rule => $port) {
        $shown = (string) shell_exec('netsh advfirewall firewall show rule name="'.$rule.'" 2>nul');
        str_contains($shown, (string) $port)
            ? $pass("Inbound rule \"{$rule}\" allows TCP {$port}")
            : $fail("No inbound firewall rule \"{$rule}\" for TCP {$port} - other PCs cannot connect (run install.bat as administrator)");
    }

    echo "\n".str_repeat('-', 75)."\n";

    if ($results['bad'] === 0) {
        echo "All {$results['ok']} checks passed. Open ".webUrl($address, $webPort)." from another PC on the network.\n";

        return 0;
    }

    echo "{$results['bad']} check(s) failed, {$results['ok']} passed. Logs: storage\\logs\\ (laravel.log, *-service.log)\n";
    echo "and C:\\xampp\\apache\\logs\\logxense-error.log.\n";

    return 1;
}

function serviceState(string $service): ?string
{
    $output = (string) shell_exec("sc query \"{$service}\" 2>nul");

    return preg_match('/STATE\s+:\s+\d+\s+(\w+)/', $output, $match) ? $match[1] : null;
}

/**
 * @return true|string true, or why the app could not boot
 */
function bootLaravel(): bool|string
{
    try {
        require_once APP_ROOT.'/vendor/autoload.php';
        $app = require APP_ROOT.'/bootstrap/app.php';
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    } catch (Throwable $exception) {
        return $exception->getMessage();
    }

    return true;
}

function laravelChecks(Closure $pass, Closure $fail): void
{
    try {
        Illuminate\Support\Facades\DB::connection()->getPdo();
        $pass('Database connection ('.config('database.connections.mysql.database').')');
    } catch (Throwable $exception) {
        $fail('No database connection: '.$exception->getMessage());

        return;
    }

    $migrator = app('migrator');
    $files = array_keys($migrator->getMigrationFiles([APP_ROOT.'/database/migrations']));
    $pending = $migrator->repositoryExists() ? array_diff($files, $migrator->getRepository()->getRan()) : $files;
    $pending === [] ? $pass('Database tables are up to date ('.count($files).' migrations)') : $fail(count($pending).' migration(s) not run - php artisan migrate --force');

    App\Models\User::query()->exists() ? $pass('There are user accounts') : $fail('No user accounts - php artisan db:seed --force');

    is_file(APP_ROOT.'/bootstrap/cache/config.php')
        ? $pass('Configuration is cached (php artisan optimize)')
        : note('Configuration is not cached - run php artisan optimize for faster pages');

    // The queue worker: hand it a job and wait for it to run.
    $token = bin2hex(random_bytes(8));
    $key = 'installer-queue-probe';

    try {
        Illuminate\Support\Facades\Cache::forget($key);
        dispatch(function () use ($key, $token) {
            Illuminate\Support\Facades\Cache::put($key, $token, 600);
        });
    } catch (Throwable $exception) {
        $fail('Could not queue a test job: '.$exception->getMessage());

        return;
    }

    $ran = false;

    for ($wait = 0; $wait < 40 && ! $ran; $wait++) {
        usleep(500_000);
        $ran = Illuminate\Support\Facades\Cache::get($key) === $token;
    }

    $ran
        ? $pass('The queue worker ran a test job within '.ceil($wait / 2).' s (alert e-mails, AI replies)')
        : $fail('The queue worker did not run a test job within 20 s - see storage\logs\queue-service.log');

    $failedJobs = Illuminate\Support\Facades\DB::table('failed_jobs')->count();

    if ($failedJobs > 0) {
        note("{$failedJobs} failed job(s) in failed_jobs - php artisan queue:failed shows why");
    }

    // The scheduler: schedule:work writes a line to its log every minute.
    $log = APP_ROOT.'/storage/logs/scheduler-service.log';
    clearstatcache();

    if (! is_file($log)) {
        $fail('The scheduler has never written storage\logs\scheduler-service.log');
    } elseif (time() - filemtime($log) <= 90) {
        $pass('The scheduler is running its every-minute tasks (offline detection, summaries)');
    } else {
        $fail('The scheduler has been silent for '.(time() - filemtime($log)).' s - see storage\logs\scheduler-service.log');
    }
}

/**
 * Connects to Reverb the way a browser does, subscribes to the demo machine's private channel
 * (signed with REVERB_APP_SECRET, as the app would), posts a reading as the ESP32 would and
 * waits for it to arrive over the WebSocket.
 *
 * @param  array<string, string>  $env
 */
function liveUpdateChecks(array $env, int $webPort, int $reverbPort, string $address, bool $post, Closure $pass, Closure $fail): void
{
    $appKey = $env['REVERB_APP_KEY'] ?? '';
    $socket = WebSocket::open('127.0.0.1', $reverbPort, "/app/{$appKey}?protocol=7&client=logxense-test&version=1.0");

    if (is_string($socket)) {
        $fail("Reverb: {$socket} - see storage\\logs\\reverb-service.log");

        return;
    }

    $welcome = $socket->receiveEvent(5);

    if (($welcome['event'] ?? '') !== 'pusher:connection_established') {
        $fail('Reverb accepted the connection but refused the app key: '.json_encode($welcome).' - REVERB_APP_KEY changed? Run install.bat again');

        return;
    }

    $pass('A browser-style WebSocket connects to Reverb with the app key');

    if (! in_array($address, ['localhost', '127.0.0.1'], true)) {
        $lan = WebSocket::open($address, $reverbPort, "/app/{$appKey}?protocol=7&client=logxense-test&version=1.0");
        is_string($lan) ? $fail("Reverb is not reachable at {$address}:{$reverbPort}: {$lan}") : $pass("Reverb answers at {$address}:{$reverbPort} too");
    }

    if (! $post) {
        note('Test reading skipped (--no-post, or the app did not boot)');

        return;
    }

    $deviceKey = $env['DEMO_DEVICE_KEY'] ?? '';
    $machine = $deviceKey === '' ? null : rescue(fn () => App\Models\Machine::findByDeviceKey($deviceKey), null, false);

    if ($machine === null) {
        note('No machine has the DEMO_DEVICE_KEY from .env, so no test reading is sent');

        return;
    }

    $socketId = json_decode($welcome['data'] ?? '{}', true)['socket_id'] ?? '';
    $channel = "private-machines.{$machine->id}";
    $signature = hash_hmac('sha256', "{$socketId}:{$channel}", $env['REVERB_APP_SECRET'] ?? '');
    $socket->send(['event' => 'pusher:subscribe', 'data' => ['channel' => $channel, 'auth' => "{$appKey}:{$signature}"]]);
    $subscribed = $socket->receiveEvent(5, 'pusher_internal:subscription_succeeded');

    if ($subscribed === null) {
        $fail("Reverb did not accept a signed subscription to {$channel} - REVERB_APP_SECRET changed? Run install.bat again");

        return;
    }

    $pass("Subscribed to {$machine->name}'s live channel");

    $readings = [];

    foreach ($machine->parameters as $parameter) {
        $readings[$parameter->key] = safeValue($parameter);
    }

    $readings = array_filter($readings, fn ($value) => $value !== null);
    [$status, $body] = httpRequest('POST', "http://127.0.0.1:{$webPort}/api/machine-logs", [
        'log_type' => 'sensor_reading',
        'notes' => 'Installation test (deploy\windows\test-install.bat)',
        'readings' => (object) $readings,
    ], ['X-Device-Key: '.$deviceKey]);

    if ($status !== 201) {
        $fail("The sensor API answered {$status} instead of 201: ".substr($body, 0, 300));

        return;
    }

    $pass("The sensor API stored a test reading for {$machine->name} (201) - it shows in its logs as \"Installation test\"");

    $pushed = $socket->receiveEvent(5, 'reading.recorded');
    $pushed !== null
        ? $pass('The reading was pushed to the browser channel at once (live chart works)')
        : $fail('The reading was stored but never pushed over Reverb - check REVERB_HOST/REVERB_PORT in .env and storage\logs\laravel.log');

    $socket->close();
}

/**
 * A value inside the parameter's normal range, so the test raises no alert (and sends no e-mail).
 */
function safeValue(App\Models\MachineParameter $parameter): int|float|bool|string|null
{
    $type = $parameter->data_type?->value;

    if ($type === 'boolean') {
        return $parameter->required ? true : null;
    }

    if ($type === 'text') {
        return $parameter->required ? 'test' : null;
    }

    $low = array_filter([$parameter->min, $parameter->warning_min, $parameter->critical_min], fn ($bound) => $bound !== null);
    $high = array_filter([$parameter->max, $parameter->warning_max, $parameter->critical_max], fn ($bound) => $bound !== null);
    $low = $low === [] ? null : max(array_map('floatval', $low));
    $high = $high === [] ? null : min(array_map('floatval', $high));

    return match (true) {
        $low !== null && $high !== null => round(($low + $high) / 2, 2),
        $low !== null => $low + 1,
        $high !== null => $high - 1,
        default => 25,
    };
}

/**
 * @param  array<string, mixed>|null  $json
 * @param  list<string>  $headers
 * @return array{0: int, 1: string}
 */
function httpRequest(string $method, string $url, ?array $json = null, array $headers = []): array
{
    $headers[] = 'Accept: application/json, text/html';

    if ($json !== null) {
        $headers[] = 'Content-Type: application/json';
    }

    $context = stream_context_create(['http' => [
        'method' => $method,
        'header' => implode("\r\n", $headers),
        'content' => $json === null ? '' : json_encode($json),
        'timeout' => 10,
        'ignore_errors' => true,
        'follow_location' => 0,
    ]]);

    $body = @file_get_contents($url, false, $context);
    $status = isset($http_response_header[0]) && preg_match('/\s(\d{3})\s?/', $http_response_header[0], $match) ? (int) $match[1] : 0;

    return [$status, $body === false ? '' : $body];
}

/**
 * Just enough of a WebSocket client (RFC 6455) to talk the Pusher protocol to Reverb.
 */
final class WebSocket
{
    private string $buffer = '';

    /**
     * @param  resource  $stream
     */
    private function __construct(private $stream) {}

    public static function open(string $host, int $port, string $path): self|string
    {
        $stream = @stream_socket_client("tcp://{$host}:{$port}", $errorNumber, $errorText, 5);

        if ($stream === false) {
            return "nothing is listening on {$host}:{$port} ({$errorText})";
        }

        $key = base64_encode(random_bytes(16));
        fwrite($stream, "GET {$path} HTTP/1.1\r\nHost: {$host}:{$port}\r\nUpgrade: websocket\r\nConnection: Upgrade\r\n"
            ."Sec-WebSocket-Key: {$key}\r\nSec-WebSocket-Version: 13\r\n\r\n");

        $socket = new self($stream);
        $head = $socket->readUntil("\r\n\r\n", 5);

        if ($head === null || ! preg_match('#^HTTP/1\.1 101#', $head)) {
            fclose($stream);

            return 'no WebSocket upgrade ('.trim(strtok((string) $head, "\r\n") ?: 'no answer').')';
        }

        return $socket;
    }

    /**
     * @param  array<string, mixed>  $message
     */
    public function send(array $message): void
    {
        $this->writeFrame(0x1, json_encode($message));
    }

    /**
     * The next Pusher event (or the next one named $name), or null on timeout.
     *
     * @return array<string, mixed>|null
     */
    public function receiveEvent(float $seconds, ?string $name = null): ?array
    {
        $deadline = microtime(true) + $seconds;

        while (microtime(true) < $deadline) {
            $text = $this->readTextFrame($deadline);

            if ($text === null) {
                return null;
            }

            $event = json_decode($text, true);

            if ($name === null || ($event['event'] ?? null) === $name) {
                return is_array($event) ? $event : null;
            }
        }

        return null;
    }

    public function close(): void
    {
        $this->writeFrame(0x8, '');
        fclose($this->stream);
    }

    private function readTextFrame(float $deadline): ?string
    {
        while (true) {
            $header = $this->readBytes(2, $deadline);

            if ($header === null) {
                return null;
            }

            $opcode = ord($header[0]) & 0x0F;
            $length = ord($header[1]) & 0x7F;

            if ($length === 126) {
                $length = unpack('n', (string) $this->readBytes(2, $deadline))[1] ?? 0;
            } elseif ($length === 127) {
                $length = unpack('J', (string) $this->readBytes(8, $deadline))[1] ?? 0;
            }

            $mask = (ord($header[1]) & 0x80) ? $this->readBytes(4, $deadline) : null;
            $payload = $length > 0 ? (string) $this->readBytes($length, $deadline) : '';

            if ($mask !== null) {
                for ($i = 0; $i < strlen($payload); $i++) {
                    $payload[$i] = $payload[$i] ^ $mask[$i % 4];
                }
            }

            if ($opcode === 0x1) {
                return $payload;
            }

            if ($opcode === 0x8) {
                return null;
            }

            if ($opcode === 0x9) {
                $this->writeFrame(0xA, $payload);
            }
        }
    }

    private function writeFrame(int $opcode, string $payload): void
    {
        $length = strlen($payload);
        $frame = chr(0x80 | $opcode);

        if ($length < 126) {
            $frame .= chr(0x80 | $length);
        } elseif ($length < 65536) {
            $frame .= chr(0x80 | 126).pack('n', $length);
        } else {
            $frame .= chr(0x80 | 127).pack('J', $length);
        }

        $mask = random_bytes(4);
        $frame .= $mask;

        for ($i = 0; $i < $length; $i++) {
            $frame .= $payload[$i] ^ $mask[$i % 4];
        }

        @fwrite($this->stream, $frame);
    }

    private function readUntil(string $marker, float $seconds): ?string
    {
        $deadline = microtime(true) + $seconds;

        while (! str_contains($this->buffer, $marker)) {
            if (! $this->fill($deadline)) {
                return null;
            }
        }

        [$head, $this->buffer] = explode($marker, $this->buffer, 2);

        return $head;
    }

    private function readBytes(int $count, float $deadline): ?string
    {
        while (strlen($this->buffer) < $count) {
            if (! $this->fill($deadline)) {
                return null;
            }
        }

        $bytes = substr($this->buffer, 0, $count);
        $this->buffer = (string) substr($this->buffer, $count);

        return $bytes;
    }

    private function fill(float $deadline): bool
    {
        $remaining = $deadline - microtime(true);

        if ($remaining <= 0) {
            return false;
        }

        $read = [$this->stream];
        $write = $except = null;

        if (! @stream_select($read, $write, $except, (int) $remaining, (int) (($remaining - floor($remaining)) * 1_000_000))) {
            return false;
        }

        $chunk = fread($this->stream, 8192);

        if ($chunk === false || $chunk === '') {
            return false;
        }

        $this->buffer .= $chunk;

        return true;
    }
}
