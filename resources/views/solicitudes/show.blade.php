<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            {{-- Botón "Regresar" a la izquierda --}}
            <a href="{{ route('solicitudes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                ← Volver al Listado
            </a>

            {{-- Título a la derecha --}}
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Solicitud #{{ $solicitud->id }} - Detalles
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">{{ $solicitud->titulo }}</h3>
                            <p class="text-gray-700 text-lg mb-4">{{ $solicitud->descripcion }}</p>
                        </div>
                        <div class="text-right">
                             <span class="inline-block px-3 py-1 font-semibold text-green-900 bg-green-200 rounded-full">
                                {{ strtoupper($solicitud->estado) }}
                            </span>
                            <div class="text-sm text-gray-500 mt-2">Prioridad: {{ ucfirst($solicitud->prioridad) }}</div>
                        </div>
                    </div>
                    
                    <div class="border-t mt-4 pt-4 text-sm text-gray-500 flex justify-between">
                        <span>Solicitado por: <strong>{{ $solicitud->creador->name }}</strong></span>
                        <span>Técnico: <strong>{{ $solicitud->tecnico ? $solicitud->tecnico->name : 'Sin asignar' }}</strong></span>
                        <span>Fecha: {{ $solicitud->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-100 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-bold mb-4">Historial de Comentarios</h4>

                    <div class="space-y-4 mb-8">
                        @forelse($solicitud->comentarios as $comentario)
                            <div class="bg-white p-4 rounded-lg shadow {{ $comentario->user_id == Auth::id() ? 'border-l-4 border-blue-500' : '' }}">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-bold text-gray-800">
                                        {{ $comentario->user->name }}
                                        <span class="text-xs text-gray-500 font-normal">({{ $comentario->user->rol_id == 2 ? 'Técnico' : ($comentario->user->rol_id == 3 ? 'Admin' : 'Usuario') }})</span>
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $comentario->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-gray-700">
                                    {{ $comentario->comentario }}
                                </p>
                            </div>
                        @empty
                            <p class="text-gray-500 italic text-center">No hay comentarios aún. ¡Escribe el primero!</p>
                        @endforelse
                    </div>

                    <form action="{{ route('solicitudes.comentarios.store', $solicitud->id) }}" method="POST" class="bg-white p-4 rounded-lg shadow-lg">
                        @csrf
                        <div class="mb-4">
                            <label for="comentario" class="block text-sm font-medium text-gray-700">Nuevo Mensaje</label>
                            <textarea name="comentario" id="comentario" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Escribe aquí para actualizar el estado o hacer una pregunta..."></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Enviar Comentario
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>