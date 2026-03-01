<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExecutionLog;
use App\Models\Scenario;
use App\Services\MakeSyncService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // <--- ¡Verifica que esto esté aquí!

class DashboardController extends Controller
{
    public function index()
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        // KPIs
        $totalCost = ExecutionLog::whereBetween('executed_at', [$start, $end])->sum('cost');
        $totalOps = ExecutionLog::whereBetween('executed_at', [$start, $end])->sum('operations');
        $errors = ExecutionLog::whereBetween('executed_at', [$start, $end])->where('status', 3)->count();

        // Escenario Top
        $topScenarioStats = ExecutionLog::select('scenario_id', DB::raw('sum(cost) as total_cost'))
            ->whereBetween('executed_at', [$start, $end])
            ->groupBy('scenario_id')
            ->orderByDesc('total_cost')
            ->first();

        $topScenarioName = $topScenarioStats ? Scenario::find($topScenarioStats->scenario_id)->name : 'N/A';
        $topScenarioCost = $topScenarioStats ? $topScenarioStats->total_cost : 0;

        // Tabla
        $scenariosTable = Scenario::with('folder')
            ->withSum(['logs as period_cost' => fn($q) => $q->whereBetween('executed_at', [$start, $end])], 'cost')
            ->withSum(['logs as period_ops' => fn($q) => $q->whereBetween('executed_at', [$start, $end])], 'operations')
            ->orderByDesc('period_cost')
            ->take(20)
            ->get();

        // Gráfica (ESTA ES LA PARTE QUE FALTA A VECES)
        $chartData = ExecutionLog::select(
            DB::raw('DATE(executed_at) as date'),
            DB::raw('sum(operations) as ops'),
            DB::raw('sum(cost) as cost')
        )
        ->where('executed_at', '>=', Carbon::now()->subDays(7))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // ¡Asegúrate de que 'chartData' esté dentro del compact!
        return view('dashboard', compact(
            'totalCost', 'totalOps', 'errors', 
            'topScenarioName', 'topScenarioCost', 
            'scenariosTable', 'chartData' 
        ));
    }

    public function sync(MakeSyncService $service)
    {
        try {
            $service->syncAll();
            return back()->with('success', 'Sincronización completada.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}