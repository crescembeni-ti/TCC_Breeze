<?php

namespace App\Exports;

use App\Models\Tree;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TreesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    // 1. A consulta (igualzinha a que fizemos no Controller)
    public function collection()
    {
        $query = Tree::with(['bairro', 'admin'])->where('aprovado', true);
        $req = $this->request;

        // Filtros Básicos
        if ($req->filled('scientific_name')) $query->where('scientific_name', $req->scientific_name);
        if ($req->filled('vulgar_name')) $query->where('vulgar_name', $req->vulgar_name);
        if ($req->filled('bairro_id')) $query->where('bairro_id', $req->bairro_id);

        // Busca Texto
        if ($req->filled('search')) {
            $term = $req->search;
            $query->where(function($q) use ($term) {
                $q->where('scientific_name', 'like', "%{$term}%")
                  ->orWhere('vulgar_name', 'like', "%{$term}%")
                  ->orWhere('address', 'like', "%{$term}%");
            });
        }

        // Filtros Admin
        $adminFields = ['health_status', 'wiring_status', 'stem_balance']; // Adicione outros se quiser
        foreach ($adminFields as $field) {
            if ($req->filled($field)) $query->where($field, $req->$field);
        }

        return $query->get();
    }

    // 2. Cabeçalhos da Planilha
    public function headings(): array
    {
        return [
            'ID',
            'Nome Científico',
            'Nome Vulgar',
            'Bairro',
            'Endereço',
            'Estado de Saúde',
            'Fiação',
            'Data Plantio',
            'Cadastrado Por'
        ];
    }

    // 3. Mapear os dados (o que vai em cada coluna)
    public function map($tree): array
    {
        return [
            $tree->id,
            $tree->scientific_name,
            $tree->vulgar_name,
            $tree->bairro->nome ?? '-',
            $tree->address,
            $tree->health_status,
            $tree->wiring_status,
            $tree->planted_at ? $tree->planted_at->format('d/m/Y') : '-',
            $tree->admin ? $tree->admin->name : 'Sistema'
        ];
    }

    // 4. Estilos (Opcional - Deixa o cabeçalho em negrito)
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF358054']]],
        ];
    }
}