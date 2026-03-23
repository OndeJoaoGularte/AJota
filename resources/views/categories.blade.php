<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <span class="text-orange-500">A</span>Gota <span class="text-gray-400 font-normal ml-2">| Raio-X por Categoria</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Aqui vamos injetar a inteligência da tela --}}
            <livewire:category-report />
        </div>
    </div>
</x-app-layout>