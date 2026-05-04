<?php
namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;

class SummarySheet implements FromArray
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            ['TABLEAU DE BORD FINANCIER'],
            [],
            ['Période', $this->data['period']],
            [],
            ['Chiffre d’affaires', $this->data['totalRevenue']],
            ['Dépenses', $this->data['totalExpenses']],
            ['Bénéfice', $this->data['profit']],
            [],
            ['Nombre de ventes', $this->data['salesCount']],
            // ['Ticket moyen', $this->data['avgTicket']],
        ];
    }
}