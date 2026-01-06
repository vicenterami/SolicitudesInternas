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
                            {{-- Usamos las clases dinámicas del Modelo --}}
                            <span id="estado-solicitud-{{ $solicitud->id }}" 
                                class="px-4 py-1 rounded-full text-sm font-bold border {{ $solicitud->color_clase }}">
                                {{ $solicitud->nombre_estado }}
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

                    <div id="lista-comentarios" class="space-y-4 mb-8 max-h-96 overflow-y-auto">
                        @forelse($solicitud->comentarios as $comentario)
                            <div id="comentario-{{ $comentario->id }}" 
                                x-data="{ editing: false }" 
                                class="bg-white p-4 rounded-lg shadow {{ $comentario->user_id == Auth::id() ? 'border-l-4 border-blue-500' : '' }}">
                                
                                <div class="flex justify-between items-center mb-2">
                                    <div class="font-bold text-sm">
                                        {{ $comentario->user->name }}
                                        
                                        {{-- MEJORA VISUAL: Usamos la relación del rol directamenente --}}
                                        <span class="text-xs text-gray-500 font-normal">
                                            ({{ $comentario->user->rol ? $comentario->user->rol->nombre : 'Usuario' }})
                                        </span>
                                    </div>
                                    
                                    <div class="text-xs text-gray-400 flex items-center gap-2">
                                        <span>{{ $comentario->created_at->diffForHumans() }}</span>

                                        {{-- 🟢 AQUÍ USAMOS LA POLICY: EDITAR --}}
                                        @can('update', $comentario)
                                            <button @click="editing = true" x-show="!editing" class="text-blue-500 hover:text-blue-700 font-bold ml-2 cursor-pointer">
                                                Editar
                                            </button>
                                        @endcan

                                        {{-- 🔴 AQUÍ USAMOS LA POLICY: ELIMINAR --}}
                                        {{-- Nota: Si eres Admin, verás este botón aunque no sea tu comentario, gracias a la Policy --}}
                                        @can('delete', $comentario)
                                            <form action="{{ route('comentarios.destroy', $comentario->id) }}" method="POST" x-show="!editing" onsubmit="return confirm('¿Borrar comentario?');" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-bold ml-1 cursor-pointer">
                                                    Borrar
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>

                                {{-- 1. MODO LECTURA --}}
                                <p x-show="!editing" class="text-gray-700 whitespace-pre-wrap">{{ $comentario->comentario }}</p>

                                {{-- 2. MODO EDICIÓN --}}
                                {{-- Protegemos visualmente el formulario también con @can --}}
                                @can('update', $comentario)
                                    <div x-show="editing" style="display: none;" class="mt-2">
                                        <form action="{{ route('comentarios.update', $comentario->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <textarea name="comentario" rows="2" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900" required>{{ $comentario->comentario }}</textarea>
                                            
                                            <div class="flex justify-end mt-2 gap-2">
                                                <button type="button" @click="editing = false" class="text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
                                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-1 px-3 rounded">Guardar</button>
                                            </div>
                                        </form>
                                    </div>
                                @endcan
                            </div>
                        @empty
                            <p class="text-gray-500 italic text-center">No hay comentarios aún.</p>
                        @endforelse
                    </div>

                    {{-- FORMULARIO NUEVO COMENTARIO (Igual que antes) --}}
                    <form action="{{ route('solicitudes.comentarios.store', $solicitud->id) }}" method="POST" class="bg-white p-4 rounded-lg shadow-lg">
                        @csrf
                        <div class="mb-4">
                            <label for="comentario" class="block text-gray-700 text-sm font-bold mb-2">Nuevo Comentario:</label>
                            <textarea name="comentario" id="comentario" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Escribe aquí..." required></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
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