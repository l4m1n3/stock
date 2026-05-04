<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExpensesExport implements FromCollection, WithHeadings
{
    protected $start;
    protected $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end   = $end;
    }

    public function collection()
    {
        return Expense::whereBetween('expense_date', [$this->start, $this->end])
            ->where('branch_id', Auth::user()->branch_id)
            ->select('title', 'type', 'amount', 'expense_date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Intitulé',
            'Type',
            'Montant (FCFA)',
            'Date',
        ];
    }
}
