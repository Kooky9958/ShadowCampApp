<?php

namespace App\Livewire;

use App\Helpers\AdminCRUDHelper;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminCRUDRetrieveModal extends Component
{
    /**
     * Name of Model for which we are retrieving data with necessary namespace path
     *
     * @var string $model_fqcn
     * @example App\Models\User
     */
    public $model_fqcn;

    /**
     * Generic instance of the Model for which we are retrieving data
     *
     * @var \Illuminate\Database\Eloquent\Model $model_generic_instance
     */
    public $model_generic_instance;

    /**
     * ID of the specific Object we are to retrieve
     *
     * @var mixed $id
     */
    public $id;

    /**
     * Data for the specific Object we are to retrieve
     *
     * @var mixed $object_data_array
     */
    public $object_data_array;

    /**
     * State of the modal
     *
     * @var string $state
     */
    public $state = "uninitialised";

    public function mount($model_fqcn)
    {
        $this->model_fqcn             = $model_fqcn;
        $this->model_generic_instance = new $this->model_fqcn;
        $this->state                  = 'mounted';
    }

    public function render()
    {
        return view('livewire.admin-c-r-u-d-retrieve-modal');
    }

    #[On('show-retrieve-modal')]
    public function updateModal($id)
    {
        // Init
        $model_object_vars = AdminCRUDHelper::modelGetDisplayableAttributes($this->model_fqcn);

        // Update id field
        $this->id = $id;

        //// Fetch data for object with specified id
        if (($object_fetch = $this->model_generic_instance::find($id)) !== null) {
            // Convert the fetched object to an array while also removing and hidden attributes
            $this->object_data_array = array_intersect_key($object_fetch->toArray(), array_fill_keys($model_object_vars, 1));

            $this->state = 'object_fetched';
        } else {
            $this->state = 'error_id_not_found';
        }

    }
}
