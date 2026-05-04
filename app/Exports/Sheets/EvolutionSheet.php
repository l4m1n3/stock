<?php
namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;

class EvolutionSheet implements FromArray
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [['Période', 'CA', 'Dépenses']];

        foreach ($this->data['chartLabels'] as $i => $label) {
            $rows[] = [
                $label,
                $this->data['chartRevenue'][$i],
                $this->data['chartExpenses'][$i],
            ];
        }

        return $rows;
    }
}