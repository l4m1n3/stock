<?php
namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
class ExpensesSheet implements FromCollection, WithHeadings
{
    protected $expenses;

    public function __construct($data)
    {
        $this->expenses = $data['expenses'];
    }

    public function collection()
    {
        return $this->expenses->map(function ($e) {
            return [
                $e->title,
                $e->type,
                $e->amount,
                $e->expense_date,
            ];
        });
    }

    public function headings(): array
    {
        return ['Intitulé', 'Type', 'Montant', 'Date'];
    }
}