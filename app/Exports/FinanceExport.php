<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinanceExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        return [
            new Sheets\SummarySheet($this->data),
            new Sheets\EvolutionSheet($this->data),
            new Sheets\ExpensesSheet($this->data),
            new Sheets\PaymentsSheet($this->data),
        ];
    }
}