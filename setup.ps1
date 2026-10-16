Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location -LiteralPath $projectRoot

function Require-Command([string] $name, [string] $hint) {
    if (-not (Get-Command $name -ErrorAction SilentlyContinue)) {
        throw "Command '$name' was not found. $hint"
    }
}

Require-Command 'php' 'Install PHP 8.2+ (or XAMPP) and add the PHP directory to PATH.'
Require-Command 'composer' 'Install Composer from https://getcomposer.org/download/'
Require-Command 'node' 'Install Node.js 20+ from https://nodejs.org/'
Require-Command 'npm' 'npm is installed together with Node.js.'

$requiredExtensions = @('fileinfo', 'mbstring', 'openssl', 'pdo_sqlite', 'sqlite3')
$loadedExtensions = @(php -m)
$missingExtensions = @($requiredExtensions | Where-Object { $_ -notin $loadedExtensions })
if ($missingExtensions.Count -gt 0) {
    throw "Enable these extensions in php.ini: $($missingExtensions -join ', '). Remove ';' before the matching extension=... lines."
}

Write-Host '1/6 Installing PHP dependencies...'
composer install --no-interaction --prefer-dist

if (-not (Test-Path -LiteralPath '.env')) {
    Copy-Item -LiteralPath '.env.example' -Destination '.env'
}

if (-not (Test-Path -LiteralPath 'database\database.sqlite')) {
    New-Item -Path 'database\database.sqlite' -ItemType File | Out-Null
}

Write-Host '2/6 Generating the application key...'
php artisan key:generate --force

Write-Host '3/6 Creating tables and demo data...'
php artisan migrate --seed --force

Write-Host '4/6 Clearing caches...'
php artisan optimize:clear

Write-Host '5/6 Installing frontend dependencies...'
npm ci

Write-Host '6/6 Building CSS and JavaScript...'
npm run build

Write-Host ''
Write-Host 'Ready. Start the app with: php artisan serve'
Write-Host 'Site: http://127.0.0.1:8000'
Write-Host 'Admin: admin@blog.test / password'
Write-Host 'User: user@blog.test / password'
