<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Folder;
use App\Models\Scenario;
use App\Models\ExecutionLog;
use Carbon\Carbon;
use Exception;

class MakeSyncService
{
    // CAMBIAR SI ES NECESARIO: '[https://eu1.make.com/api/v2](https://eu1.make.com/api/v2)' para Europa
    protected $baseUrl = '[https://us1.make.com/api/v2](https://us1.make.com/api/v2)'; 
    protected $token;
    protected $costPerOperation = 0.000725; // Costo estimado por operación

    public function __construct()
    {
        $this->token = env('MAKE_API_TOKEN');
    }

    public function syncAll()
    {
        if (!$this->token) throw new Exception("Token de API no configurado.");

        $this->syncFolders();
        $this->syncScenarios();
        // Sincroniza logs de los últimos 7 días
        $this->syncLogs(now()->subDays(7)); 
    }

    private function syncFolders()
    {
        $response = Http::withToken($this->token)->get("{$this->baseUrl}/folders");
        
        if ($response->successful()) {
            foreach ($response->json()['folders'] as $folder) {
                Folder::updateOrCreate(
                    ['id' => $folder['id']],
                    ['name' => $folder['name']]
                );
            }
        }
    }

    private function syncScenarios()
    {
        $response = Http::withToken($this->token)->get("{$this->baseUrl}/scenarios", [
            'organizationId' => env('MAKE_ORG_ID'),
            'limit' => 200
        ]);

        if ($response->successful()) {
            foreach ($response->json()['scenarios'] as $sc) {
                Scenario::updateOrCreate(
                    ['id' => $sc['id']],
                    [
                        'name' => $sc['name'],
                        'folder_id' => $sc['folderId'],
                        'is_active' => $sc['isactive']
                    ]
                );
            }
        }
    }

    private function syncLogs(Carbon $since)
    {
        $scenarios = Scenario::where('is_active', true)->get();

        foreach ($scenarios as $scenario) {
            $response = Http::withToken($this->token)->get("{$this->baseUrl}/scenarios/{$scenario->id}/logs", [
                'from' => $since->timestamp,
                'limit' => 20 
            ]);

            if ($response->successful() && isset($response->json()['logs'])) {
                foreach ($response->json()['logs'] as $log) {
                    $ops = $log['operations'] ?? 0;
                    $status = ($log['status'] === 3) ? 3 : 1; // 3 es error

                    ExecutionLog::updateOrCreate(
                        ['id' => $log['id']],
                        [
                            'scenario_id' => $scenario->id,
                            'operations' => $ops,
                            'cost' => $ops * $this->costPerOperation,
                            'duration_ms' => $log['duration'] ?? 0,
                            'status' => $status,
                            'executed_at' => Carbon::parse($log['timestamp'])
                        ]
                    );
                }
            }
            usleep(200000); // Pausa para evitar bloqueo de API
        }
    }
}
