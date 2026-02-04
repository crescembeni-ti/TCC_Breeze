@extends('layouts.dashboard')
@section('title', 'Tarefas Concluídas')

@section('content')
    <div x-data="{ 
    open: false, 
    showPhoto: false, 

    showLightbox: false,
    photoUrl: '', 
    item: { id: '', contact: { status: {} }, motivos: [], servicos: [], equipamentos: [] },
    currentPhotos: [],
    openCarousel(photos) {
        this.currentPhotos = photos;
        this.showPhoto = true;
    }
}">
    {{-- CABEÇALHO DA PÁGINA --}}
    <header class="bg-white shadow mb-8 rounded-lg p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-semibold text-gray-700">Histórico de Conclusão</h2>
                <p class="text-gray-500 mt-1">Serviços finalizados pela equipe técnica.</p>
            </div>

            {{-- Filtro de Data --}}
            <form method="GET" action="{{ route('service.tasks.concluidas') }}" x-data="{ period: '{{ request('period') }}' }" class="flex flex-col md:flex-row items-end gap-3 w-full md:w-auto">
                <div class="relative w-full md:w-auto">
                    <select name="period" x-model="period" onchange="if(this.value != 'custom') this.form.submit()" class="appearance-none w-full md:w-48 bg-gray-50 border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:border-green-500 cursor-pointer shadow-sm">
                        <option value="" {{ request('period') == '' ? 'selected' : '' }}>Todo o Período</option>
                        <option value="7_days">📅 Últimos 7 dias</option>
                        <option value="30_days">📅 Últimos 30 dias</option>
                        <option value="custom">📆 Personalizado...</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg></div>
                </div>
                <div x-show="period === 'custom'" x-transition class="flex gap-2 w-full md:w-auto" style="display: none;">
                    <input type="date" name="date_start" value="{{ request('date_start') }}" class="rounded-lg border-gray-300 text-sm focus:border-green-500 w-full md:w-auto">
                    <span class="self-center text-gray-500">até</span>
                    <input type="date" name="date_end" value="{{ request('date_end') }}" class="rounded-lg border-gray-300 text-sm focus:border-green-500 w-full md:w-auto">
                    <button type="submit" class="bg-[#358054] text-white px-3 py-2 rounded-lg hover:bg-green-700 transition"><i data-lucide="search" class="w-4 h-4"></i></button>
                </div>
                @if(request()->filled('period'))
                    <a href="{{ route('service.tasks.concluidas') }}" class="text-gray-500 hover:text-gray-700 flex items-center gap-1 text-sm underline">Limpar</a>
                @endif
            </form>
        </div>
    </header>

    {{-- CONTEÚDO PRINCIPAL --}}
    @if($ordens->isEmpty())
        
        {{-- ESTADO VAZIO --}}
        <div class="bg-white overflow-hidden shadow-md sm:rounded-lg p-12 text-center flex flex-col items-center justify-center min-h-[300px]">
            <div class="bg-gray-100 p-4 rounded-full mb-4">
                <i data-lucide="archive" class="w-12 h-12 text-gray-400"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Histórico vazio</h3>
            <p class="text-gray-500">Nenhuma tarefa foi concluída até o momento.</p>
        </div>

    @else
        
        {{-- LISTAGEM (TABELA PADRONIZADA) --}}
        <div class="bg-white overflow-hidden shadow-md sm:rounded-lg p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protocolo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                            {{-- NOVA COLUNA: DATA CONCLUSÃO --}}
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Conclusão</th>
                            {{-- NOVA COLUNA: STATUS --}}
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fotos</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($ordens as $os)
                        <tr class="hover:bg-gray-50 transition-colors opacity-90">
                            {{-- PROTOCOLO --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-600">
                                #{{ $os->contact->id }}
                            </td>
                            
                            {{-- LOCAL --}}
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="font-bold text-gray-900">{{ $os->contact->bairro }}</div>
                                <div class="text-xs text-gray-500">{{ $os->contact->rua }}</div>
                            </td>

                            {{-- DATA CONCLUSÃO (UPDATED_AT) --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="calendar-check" class="w-4 h-4 text-green-600 mr-1"></i>
                                    <span>{{ $os->updated_at->format('d/m/Y') }}</span>
                                    <span class="text-gray-500 text-xs">às</span>
                                    <span>{{ $os->updated_at->format('H:i') }}</span>
                                </div>
                            </td>

                            {{-- STATUS (FIXO: CONCLUÍDO) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                    Concluído
                                </span>
                            </td>

                            {{-- FOTOS --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @php
                                    $fotos = $os->contact->fotos;
                                    if (is_string($fotos)) {
                                        $fotos = json_decode($fotos, true);
                                    }
                                @endphp
                                @if(is_array($fotos) && count($fotos) > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($fotos as $index => $foto)
                                            <button @click="photoUrl = '/storage/{{ $foto }}'; showPhoto = true; currentPhotos = {{ json_encode($fotos) }};" class="flex items-center gap-1 text-[#358054] hover:underline font-bold">
                                                <i data-lucide="image" class="w-4 h-4"></i> Foto {{ $index + 1 }}
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif($os->contact->foto_path)
                                    <button @click="photoUrl = '{{ Storage::url($os->contact->foto_path) }}'; showPhoto = true; currentPhotos = [];" class="flex items-center gap-1 text-[#358054] hover:underline font-bold">
                                        <i data-lucide="image" class="w-4 h-4"></i> Foto 1
                                    </button>
                                @else
                                    <span class="text-gray-400 italic">Sem foto</span>
                                @endif
                            </td>

                            {{-- AÇÕES (BOTÃO VER OS) --}}
                            <td class="px-6 py-4 text-right">
                                <button @click="open = true; item = {{ json_encode($os->load('contact.status')) }}" 
                                    class="text-green-700 hover:text-blue-900 font-bold border border-green-700 px-4 py-1.5 rounded hover:bg-blue-50 transition flex items-center gap-2 ml-auto">
                                    <i data-lucide="eye" class="w-4 h-4"></i> Ver OS
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif





    {{-- MODAL DE FOTO CENTRALIZADO (ESTILO ADMIN CORRIGIDO) --}}
    <div x-show="showPhoto" 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4" 
         style="display: none;" 
         x-cloak>
        <div class="fixed inset-0 bg-black/75 transition-opacity" @click="showPhoto = false"></div>
        
        <div class="relative bg-white rounded-xl shadow-2xl overflow-hidden transform transition-all z-10 mx-auto flex flex-col items-center w-auto max-w-[95vw] sm:max-w-4xl">
            {{-- Botão Fechar no Canto Superior Direito --}}
            <button @click="showPhoto = false" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 transition-colors z-20 bg-white/80 rounded-full p-1 shadow-sm">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
            
            {{-- Conteúdo da Foto --}}
            <div class="p-1 w-full flex justify-center items-center">
                <div class="relative flex justify-center items-center overflow-hidden rounded-lg bg-gray-100 shadow-inner" style="max-height: 85vh;">
                    <img :src="photoUrl" 
                         class="max-w-full h-auto object-contain block mx-auto"
                         style="max-height: 85vh; width: auto; display: block;"
                         @click.stop>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL (APENAS LEITURA) --}}
    @include('servico.partials.modal_os', ['action' => 'none'])
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => { 
        lucide.createIcons(); 
    });
</script>
@endsection
