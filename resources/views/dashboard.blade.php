<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Analytics</title>
    <!-- Estilos y Librerías -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white hidden md:flex flex-col">
            <div class="p-6 text-center font-bold text-xl tracking-wider border-b border-gray-700">
                <i class="fa-solid fa-chart-line text-purple-500 mr-2"></i> ANALYTICS
            </div>
            <nav class="flex-1 px-4 py-6">
                <a href="#" class="flex items-center px-4 py-3 bg-purple-700 rounded-lg text-white">
                    <i class="fa-solid fa-gauge mr-3 w-6"></i> Dashboard
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-y-auto">
            
            <!-- Header -->
            <header class="bg-white shadow-sm sticky top-0 z-10 px-6 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Resumen General</h1>
                
                <!-- Formulario de Sync -->
                <form action="{{ route('sync') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded shadow flex items-center transition transform hover:scale-105">
                        <i class="fa-solid fa-rotate mr-2"></i> Sincronizar
                    </button>
                </form>
            </header>

            <div class="p-6 max-w-7xl mx-auto w-full">
                
                <!-- Mensajes de Alerta -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">¡Éxito!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Error:</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <!-- KPIs -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Costo -->
                    <div class="bg-white rounded-xl shadow p-5 border-l-4 border-purple-500 hover:shadow-lg transition">
                        <p class="text-xs font-semibold text-gray-400 uppercase">Costo (Mes)</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($totalCost, 2) }}</h3>
                    </div>
                    <!-- Operaciones -->
                    <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500 hover:shadow-lg transition">
                        <p class="text-xs font-semibold text-gray-400 uppercase">Operaciones</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalOps) }}</h3>
                    </div>
                    <!-- Errores -->
                    <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500 hover:shadow-lg transition">
                        <p class="text-xs font-semibold text-gray-400 uppercase">Errores</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $errors }}</h3>
                    </div>
                    <!-- Top Escenario -->
                    <div class="bg-white rounded-xl shadow p-5 border-l-4 border-yellow-500 hover:shadow-lg transition">
                        <p class="text-xs font-semibold text-gray-400 uppercase">Más Costoso</p>
                        <h3 class="text-lg font-bold text-gray-800 mt-1 truncate" title="{{ $topScenarioName }}">{{ \Illuminate\Support\Str::limit($topScenarioName, 18) }}</h3>
                        <p class="text-xs text-gray-500 mt-1">${{ number_format($topScenarioCost, 2) }}</p>
                    </div>
                </div>

                <!-- Gráfica -->
                <div class="bg-white rounded-xl shadow p-6 mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Tendencia de Costos (Últimos 7 días)</h3>
                    <div class="relative h-64">
                        <canvas id="mainChart"></canvas>
                    </div>
                </div>

                <!-- Tabla Detallada -->
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-bold text-gray-800">Top Escenarios por Gasto</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3">Escenario</th>
                                    <th class="px-6 py-3">Carpeta</th>
                                    <th class="px-6 py-3 text-center">Ops</th>
                                    <th class="px-6 py-3 text-center">Costo</th>
                                    <th class="px-6 py-3 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($scenariosTable as $sc)
                                <tr class="bg-white hover:bg-gray-50 border-b transition">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $sc->name }}</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded border border-gray-200">
                                            {{ $sc->folder->name ?? 'Sin Carpeta' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold">{{ number_format($sc->period_ops) }}</td>
                                    <td class="px-6 py-4 text-center text-red-600 font-bold">${{ number_format($sc->period_cost, 2) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if($sc->is_active)
                                            <span class="h-3 w-3 rounded-full bg-green-500 inline-block shadow-sm" title="Activo"></span>
                                        @else
                                            <span class="h-3 w-3 rounded-full bg-gray-400 inline-block shadow-sm" title="Inactivo"></span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-solid fa-inbox text-4xl mb-3 text-gray-300"></i>
                                            <p>No hay datos disponibles.</p>
                                            <p class="text-xs mt-1">Dale al botón "Sincronizar" para comenzar.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Script Corregido para inyectar datos -->
    <script>
        // CORRECCIÓN: Usamos {!! !!} y json_encode en lugar de @json.
        // Esto funciona en todas las versiones de Laravel y es menos propenso a errores de caché.
        const chartData = {!! json_encode($chartData) !!};
        
        const ctx = document.getElementById('mainChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [
                    {
                        label: 'Operaciones',
                        data: chartData.map(d => d.ops),
                        borderColor: '#663399',
                        backgroundColor: 'rgba(102, 51, 153, 0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Costo ($)',
                        data: chartData.map(d => d.cost),
                        borderColor: '#10B981',
                        borderDash: [5, 5],
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        title: {display: true, text: 'Operaciones'},
                        grid: { borderDash: [2, 4] }
                    },
                    y1: { 
                        type: 'linear', 
                        display: true, 
                        position: 'right', 
                        grid: {drawOnChartArea: false},
                        title: {display: true, text: 'Costo ($)'}
                    }
                }
            }
        });
    </script>
</body>
</html>