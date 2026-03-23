<?php

namespace App\Services\VDI;

use App\Models\Vm;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GuacamoleService
{
    private string $baseUrl;
    private string $user;
    private string $pass;
    private string $dataSource = 'postgresql';

    public function __construct()
    {
        $this->baseUrl    = rtrim(config('services.guacamole.url'), '/');
        $this->user       = config('services.guacamole.user');
        $this->pass       = config('services.guacamole.pass');
    }

    // -------------------------------------------------------------------------
    // Authenticate — returns a short-lived auth token
    // -------------------------------------------------------------------------

    public function authenticate(): string
    {
        $response = Http::asForm()
            ->timeout(10)
            ->post("{$this->baseUrl}/api/tokens", [
                'username' => $this->user,
                'password' => $this->pass,
            ]);

        if (! $response->successful()) {
            Log::error('Guacamole authentication failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Cannot authenticate with Guacamole: HTTP ' . $response->status());
        }

        return $response->json('authToken')
            ?? throw new RuntimeException('Guacamole returned no auth token.');
    }

    // -------------------------------------------------------------------------
    // Create an RDP connection in Guacamole, returns the new connection ID
    // -------------------------------------------------------------------------

    public function createRdpConnection(Vm $vm, string $token): string
    {
        $payload = [
            'name'             => $vm->vm_name,
            'protocol'         => 'rdp',
            'parentIdentifier' => 'ROOT',
            'parameters'       => [
                'hostname'             => $vm->rdp_host,
                'port'                 => (string) ($vm->rdp_port ?? 3389),
                'username'             => $vm->rdp_username,
                'password'             => $vm->rdp_password, // already decrypted by accessor
                'security'             => 'any',
                'ignore-cert'          => 'true',
                'enable-wallpaper'     => 'false',
                'enable-theming'       => 'false',
                'color-depth'          => '16',
                'width'                => '1280',
                'height'               => '720',
            ],
            'attributes' => [
                'max-connections'          => '1',
                'max-connections-per-user' => '1',
            ],
        ];

        $response = Http::withToken($token, '')
            ->withQueryParameters(['token' => $token])
            ->timeout(15)
            ->post("{$this->baseUrl}/api/session/data/{$this->dataSource}/connections", $payload);

        if (! $response->successful()) {
            Log::error('Guacamole createRdpConnection failed', [
                'vm_id'  => $vm->id,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException('Failed to create Guacamole connection: HTTP ' . $response->status());
        }

        return (string) $response->json('identifier')
            ?? throw new RuntimeException('Guacamole returned no connection identifier.');
    }

    // -------------------------------------------------------------------------
    // Delete a connection by ID
    // -------------------------------------------------------------------------

    public function deleteConnection(string $connectionId, string $token): void
    {
        $response = Http::withQueryParameters(['token' => $token])
            ->timeout(10)
            ->delete("{$this->baseUrl}/api/session/data/{$this->dataSource}/connections/{$connectionId}");

        if (! $response->successful() && $response->status() !== 404) {
            Log::warning('Guacamole deleteConnection failed', [
                'connection_id' => $connectionId,
                'status'        => $response->status(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Build the browser-side Guacamole client URL for iframe embedding
    // Format: {baseUrl}/#/client/{base64(id\0c\0dataSource)}?token={token}
    // -------------------------------------------------------------------------

    public function buildClientUrl(string $connectionId, string $token): string
    {
        $encoded = base64_encode("{$connectionId}\0c\0{$this->dataSource}");

        return "{$this->baseUrl}/#/client/{$encoded}?token={$token}";
    }
}
