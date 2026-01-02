<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                ← Volver al Listado
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Usuario') }}: {{ $user->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Ruta update y ID del usuario --}}
                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                        @csrf
                        @method('PUT') {{-- Importante para updates --}}

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nombre Completo')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="email" :value="__('Correo Electrónico')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="rol_id" :value="__('Rol de Usuario')" />
                            <select id="rol_id" name="rol_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ $user->rol_id == $role->id ? 'selected' : '' }}>
                                        {{ ucfirst($role->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('rol_id')" class="mt-2" />
                        </div>

                        <hr class="my-6 border-gray-200">
                        <p class="text-sm text-gray-500 mb-4">Dejar en blanco si no desea cambiar la contraseña.</p>

                        <div class="mb-4">
                            <x-input-label for="password" :value="__('Nueva Contraseña (Opcional)')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Actualizar Usuario') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>