<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Database\Eloquent\Builder;

class BookingsQueryExport implements FromQuery, WithMapping, WithHeadings, WithChunkReading
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * Return the query used for export. Ensure required relationships are eager loaded by the caller.
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Map each row for the spreadsheet.
     */
    public function map($b): array
    {
        return [
            $b->id,
            $b->client_name,
            $b->reference_no,
            optional($b->marketingPerson)->name,
            $b->items->count(),
            $b->job_order_date ? \Carbon\Carbon::parse($b->job_order_date)->format('Y-m-d') : '',
            optional($b->department)->name,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Client Name',
            'Reference No',
            'Marketing Person',
            'Items Count',
            'Job Order Date',
            'Department',
        ];
    }

    /**
     * Chunk size for reading; keeps memory usage bounded.
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
