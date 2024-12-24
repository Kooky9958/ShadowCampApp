<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\AdminCRUDReports;

class AdminCRUDReport extends Component
{
    use WithPagination;

    /**
     * Name of the report being run
     *
     * @var string $report_name
     */
    public $report_name;

    public function render()
    {
        return view('livewire.admin-c-r-u-d-report', ['report_out' => AdminCRUDReports::{$this->report_name}()]);
    }
}
