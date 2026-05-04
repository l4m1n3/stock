<?php
namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;

class PaymentsSheet implements FromArray
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [['Mode de paiement', 'Montant']];

        foreach ($this->data['paymentData']['labels'] as $i => $label) {
            $rows[] = [
                $label,
                $this->data['paymentData']['values'][$i],
            ];
        }

        return $rows;
    }
}