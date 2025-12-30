<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            {{-- Botón "Regresar" a la izquierda --}}
            <a href="{{ route('solicitudes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                ← Volver al Listado
            </a>

            {{-- Título a la derecha --}}
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestionar Solicitud #{{ $solicitud->id }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-bold text-gray-700">{{ $solicitud->titulo }}</h3>
                        <p class="text-gray-600 mt-2">{{ $solicitud->descripcion }}</p>
                        <div class="mt-4 text-sm text-gray-500">
                            Creado por: <strong>{{ $solicitud->creador->name }}</strong> | 
                            Prioridad: <strong>{{ ucfirst($solicitud->prioridad) }}</strong>
                        </div>
                    </div>

                    <form action="{{ route('solicitudes.update', $solicitud->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="tecnico_id" class="block text-sm font-medium text-gray-700">Asignar Técnico</label>
                            <select name="tecnico_id" id="tecnico_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Sin Asignar --</option>
                                @foreach($tecnicos as $tecnico)
                                    <option value="{{ $tecnico->id }}" {{ $solicitud->tecnico_id == $tecnico->id ? 'selected' : '' }}>
                                        {{ $tecnico->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                            <select name="estado" id="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="pendiente" {{ $solicitud->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="asignada" {{ $solicitud->estado == 'asignada' ? 'selected' : '' }}>Asignada</option>
                                <option value="resuelta" {{ $solicitud->estado == 'resuelta' ? 'selected' : '' }}>Resuelta</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('solicitudes.index') }}" class="text-gray-600 underline mr-4">Cancelar</a>
                            <x-primary-button>
                                Guardar Cambios
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>