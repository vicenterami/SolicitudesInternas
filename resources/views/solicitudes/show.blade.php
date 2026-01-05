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
                            {{--  👇 AQUÍ CAMBIO 1: Agregamos id="estado-solicitud-..." al span --}}
                             <span id="estado-solicitud-{{ $solicitud->id }}" class="inline-block px-3 py-1 font-semibold text-green-900 bg-green-200 rounded-full">
                                {{ strtoupper($solicitud->estado) }}
                            </span>
                            <div class="text-sm text-gray-500 mt-2">Prioridad: {{ ucfirst($solicitud->prioridad) }}</div>
                        </div>
                    </div>


                    {{-- === AQUÍ AGREGAMOS LOS ADJUNTOS === --}}
                    @if($solicitud->adjuntos->count() > 0)
                        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
                            <h4 class="font-bold text-blue-800 mb-2 text-sm uppercase">Archivos Adjuntos:</h4>
                            <ul class="list-disc list-inside">
                                @foreach($solicitud->adjuntos as $adjunto)
                                    <li>
                                        <a href="{{ asset('storage/' . $adjunto->ruta_archivo) }}" target="_blank" class="text-blue-600 hover:underline hover:text-blue-800">
                                            📄 {{ $adjunto->nombre_archivo }} 
                                            <span class="text-gray-500 text-xs">(Clic para ver)</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {{-- =================================== --}}

                    
                    <div class="border-t mt-4 pt-4 text-sm text-gray-500 flex justify-between">
                        <span>Solicitado por: <strong>{{ $solicitud->creador->name }}</strong></span>
                        
                        {{-- 👇 AQUÍ CAMBIO 2: Agregamos id="tecnico-solicitud-..." al strong --}}
                        <span>Técnico: <strong id="tecnico-solicitud-{{ $solicitud->id }}">{{ $solicitud->tecnico ? $solicitud->tecnico->name : 'Sin asignar' }}</strong></span>
                        
                        <span>Fecha: {{ $solicitud->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-100 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-bold mb-4">Historial de Comentarios</h4>

                    {{-- 1. AQUI AGREGAMOS EL ID "lista-comentarios" AL CONTENEDOR PRINCIPAL --}}
                    <div id="lista-comentarios" class="space-y-4 mb-8 max-h-96 overflow-y-auto">
                        @forelse($solicitud->comentarios as $comentario)
                            {{-- AGREGAMOS EL ID ÚNICO AQUÍ: id="comentario-{{ $comentario->id }}" --}}
                            <div id="comentario-{{ $comentario->id }}" class="bg-white p-4 rounded-lg shadow {{ $comentario->user_id == Auth::id() ? 'border-l-4 border-blue-500' : '' }}">
                                <div class="flex justify-between items-center mb-2">
                                    {{-- ... resto del contenido igual ... --}}
                                </div>
                                <p class="text-gray-700">{{ $comentario->comentario }}</p>
                            </div>
                        @empty
                            <p id="mensaje-sin-comentarios" class="text-gray-500 italic text-center">No hay comentarios aún. ¡Escribe el primero!</p>
                        @endforelse
                    </div>

                    {{-- AGREGAMOS ID AL FORMULARIO PARA MANEJARLO CON JS --}}
                    <form id="form-comentario" action="{{ route('solicitudes.comentarios.store', $solicitud->id) }}" class="bg-white p-4 rounded-lg shadow-lg">
                        {{-- Quitamos el method="POST" del HTML porque lo haremos por Axios, pero dejamos el @csrf --}}
                        @csrf 
                        <div class="mb-4">
                            {{-- ... inputs iguales ... --}}
                            <textarea name="comentario" id="comentario" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                        </div>
                        <div class="flex justify-end">
                            {{-- Cambiamos type="submit" a un botón normal o controlamos el submit --}}
                            <button type="submit" id="btn-enviar" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
                                Enviar Comentario
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- DATOS OCULTOS PARA JS --}}
    <div id="solicitud-data" data-id="{{ $solicitud->id }}" class="hidden"></div>

</x-app-layout>