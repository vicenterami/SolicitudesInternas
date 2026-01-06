<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Control') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- SECCIÓN 1: TARJETAS DE RESUMEN --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="text-gray-500 text-sm font-bold uppercase">Total Tickets</div>
                    <div class="text-3xl font-bold text-gray-800" id="dash-total">{{ $total }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
                    <div class="text-gray-500 text-sm font-bold uppercase">Pendientes</div>
                    <div class="text-3xl font-bold text-red-600" id="dash-pendientes">{{ $pendientes }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                    <div class="text-gray-500 text-sm font-bold uppercase">Asignadas</div>
                    <div class="text-3xl font-bold text-yellow-600" id="dash-asignadas">{{ $asignadas }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="text-gray-500 text-sm font-bold uppercase">Resueltas</div>
                    <div class="text-3xl font-bold text-green-600" id="dash-resueltas">{{ $resueltas }}</div>
                </div>
            </div>

            {{-- SECCIÓN 2: GRÁFICO Y ACCIONES RÁPIDAS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Tickets por Prioridad</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="miGrafico"></canvas>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col justify-center items-center text-center">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Acciones Rápidas</h3>
                    <p class="text-gray-600 mb-6">Gestiona las solicitudes o crea un nuevo reporte de incidencia.</p>
                    
                    <a href="{{ route('solicitudes.create') }}" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg mb-3 transition">
                        + Nueva Solicitud
                    </a>
                    <a href="{{ route('solicitudes.index') }}" class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition">
                        Ver Listado Completo
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPT DEL GRÁFICO --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('miGrafico');
        
        // 1. Obtenemos los datos de PHP
        const etiquetas = {!! json_encode($labels) !!};
        const datos = {!! json_encode($data) !!};

        // 2. Definimos colores según la etiqueta (Lógica Semáforo)
        const coloresFondo = etiquetas.map(label => {
            const l = label.toLowerCase();
            if (l === 'alta') return 'rgba(239, 68, 68, 0.2)';   // Rojo (Red-500)
            if (l === 'media') return 'rgba(234, 179, 8, 0.2)';  // Amarillo (Yellow-500)
            return 'rgba(34, 197, 94, 0.2)';                     // Verde (Green-500) para Baja
        });

        const coloresBorde = etiquetas.map(label => {
            const l = label.toLowerCase();
            if (l === 'alta') return 'rgba(239, 68, 68, 1)';
            if (l === 'media') return 'rgba(234, 179, 8, 1)';
            return 'rgba(34, 197, 94, 1)';
        });

        // 3. Inicializamos el gráfico globalmente
        window.miChart = new Chart(ctx, {
            type: 'doughnut', 
            data: {
                labels: etiquetas, 
                datasets: [{
                    label: '# de Tickets',
                    data: datos,
                    borderWidth: 1,
                    backgroundColor: coloresFondo,
                    borderColor: coloresBorde,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</x-app-layout>