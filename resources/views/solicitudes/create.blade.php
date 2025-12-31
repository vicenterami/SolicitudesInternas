<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nueva Solicitud') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form method="POST" action="{{ route('solicitudes.store') }}" enctype="multipart/form-data"> 
                        @csrf {{-- Token de seguridad OBLIGATORIO en Laravel --}}

                        <div class="mb-4">
                            <label for="titulo" class="block text-gray-700 text-sm font-bold mb-2">Asunto del problema:</label>
                            <input type="text" name="titulo" id="titulo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>

                        <div class="mb-4">
                            <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción detallada:</label>
                            <textarea name="descripcion" id="descripcion" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="prioridad" class="block text-gray-700 text-sm font-bold mb-2">Prioridad:</label>
                            <select name="prioridad" id="prioridad" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="baja">Baja (Puede esperar)</option>
                                <option value="media">Media (Afecta mi trabajo)</option>
                                <option value="alta">Alta (Sistema crítico/Detenido)</option>
                            </select>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="archivo" :value="__('Adjuntar Archivo (Opcional)')" />
                            <input type="file" name="archivo" id="archivo" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                            <p class="mt-1 text-sm text-gray-500">PDF, JPG, PNG (Max. 2MB)</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('solicitudes.index') }}" class="text-gray-600 underline mr-4">Cancelar</a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Enviar Solicitud
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>