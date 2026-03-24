<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AGota') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    </head>
    
    {{-- Mudamos o fundo principal para Escuro (Slate 900) e usamos Flexbox para colocar a barra ao lado do conteúdo --}}
    <body class="font-sans antialiased bg-slate-900 text-slate-300 flex h-screen overflow-hidden">
        
        {{-- A BARRA LATERAL (SIDEBAR) --}}
        {{-- Ela tem 5rem de largura (w-20), mas quando passa o mouse (hover), cresce para 16rem (w-64) --}}
        <aside class="w-20 hover:w-64 group transition-all duration-300 ease-in-out bg-slate-800 border-r border-slate-700 flex flex-col py-6 relative z-50 shadow-2xl shrink-0">
            
            {{-- Logo --}}
            <div class="flex items-center px-6 mb-10 overflow-hidden cursor-default">
                <div class="text-orange-500 font-black text-3xl shrink-0">A</div>
                {{-- A palavra Gota fica invisível (opacity-0) até o mouse passar na barra lateral (group-hover:opacity-100) --}}
                <div class="text-white font-bold text-2xl ml-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap tracking-wider">
                    Gota
                </div>
            </div>

            {{-- Links de Navegação --}}
            <nav class="flex-1 flex flex-col gap-3 px-4">
                
                {{-- Link 1: Visão Geral --}}
                <a href="{{ route('dashboard') }}" class="flex items-center px-2 py-3 rounded-lg text-slate-400 hover:text-orange-400 hover:bg-slate-700/50 transition-colors">
                    <span class="text-2xl shrink-0">📊</span>
                    <span class="ml-4 font-semibold text-sm uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                        Visão Geral
                    </span>
                </a>

                {{-- Link 2: Lançamentos --}}
                <a href="#" class="flex items-center px-2 py-3 rounded-lg text-slate-400 hover:text-orange-400 hover:bg-slate-700/50 transition-colors">
                    <span class="text-2xl shrink-0">💸</span>
                    <span class="ml-4 font-semibold text-sm uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                        Lançamentos
                    </span>
                </a>

                {{-- Link 3: Cartões (Se a rota for a atual, ele fica Laranja!) --}}
                <a href="/cards" class="{{ request()->is('cards') ? 'bg-orange-500/10 text-orange-500 border-l-2 border-orange-500' : 'text-slate-400 hover:text-orange-400 hover:bg-slate-700/50' }} flex items-center px-2 py-3 rounded-lg transition-colors">
                    <span class="text-2xl shrink-0 {{ request()->is('cards') ? '' : 'grayscale opacity-70' }}">💳</span>
                    <span class="ml-4 font-semibold text-sm uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                        Meus Cartões
                    </span>
                </a>

                {{-- Link 4: Raio-X --}}
                <a href="/categories" class="flex items-center px-2 py-3 rounded-lg text-slate-400 hover:text-orange-400 hover:bg-slate-700/50 transition-colors">
                    <span class="text-2xl shrink-0">🔍</span>
                    <span class="ml-4 font-semibold text-sm uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                        Raio-X
                    </span>
                </a>

                {{-- Link 5: Investimentos --}}
                <a href="/investimentos" class="flex items-center px-2 py-3 rounded-lg text-slate-400 hover:text-orange-400 hover:bg-slate-700/50 transition-colors mt-auto">
                    <span class="text-2xl shrink-0">🚀</span>
                    <span class="ml-4 font-semibold text-sm uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                        Investimentos
                    </span>
                </a>
            </nav>

            {{-- Perfil e Sair --}}
            <div class="px-4 mt-6 border-t border-slate-700 pt-6">
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-2 py-3 rounded-lg text-slate-500 hover:text-red-400 hover:bg-slate-700/50 transition-colors">
                        <span class="text-2xl shrink-0">🚪</span>
                        <span class="ml-4 font-semibold text-sm uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                            Sair do Sistema
                        </span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- O CONTEÚDO DA PÁGINA (A tela que carrega no meio) --}}
        <main class="flex-1 overflow-y-auto bg-slate-900 relative">
            
            {{-- O Cabeçalho Antigo (Vamos deixá-lo escuro também) --}}
            @if (isset($header))
                <header class="bg-slate-800 shadow-lg border-b border-slate-700">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            {{-- Aqui entra a página de Cartões, Dashboard, etc. --}}
            {{ $slot }}
            
        </main>
    </body>
</html>