<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class ResourceList extends Component
{
    use WithPagination;

    /**
     * Storage location to persisted state
     */
    public ?array $livewire_persist = null;

    /**
     * Current resource_list we are filtering by
     */
    public ?string $resource_list_filter_by_current = null;

    /**
     * Whether or not to disable filtering by resource_lists
     */
    public bool $disable_filter_by_resource_lists = true;

    /**
     * Most recent resources fetched
     * @var \Illuminate\Support\Collection
     */
    private $got_resources;

    /**
     * Get the resource_lists to filter by
     */
    public function getResourceListsFilterBy() {
        return \App\Models\ProductContentResourceList::whereIn('id', $this->livewire_persist['resource_lists_filter_by_ids'])->get();
    }

    /**
     * Get the resource_list we are displaying
     */
    public function getResourceList() {
        return \App\Models\ProductContentResourceList::getResourceList($this->livewire_persist['resource_list_url_id']);
    }

    /**
     * Get the resources in the resource_list
     */
    public function getResources() {
        // Check have we already got the resources
        if($this->got_resources !== null)
            return $this->got_resources;

        // Compose the query to fetch the resources
        if ($this->resource_list_filter_by_current != null) {
            $this->resetPage();
            
            $query = \App\Models\ProductContentResource::where('resource_list', 'like' , '%'.$this->resource_list_filter_by_current.'%')
                        ->whereIn('id', $this->livewire_persist['resources_all_ids']);
        }
        else {
            $query = \App\Models\ProductContentResource::whereIn('id', $this->livewire_persist['resources_all_ids']);
        }

        // Execute the query, store the result, and return it
        return ($this->got_resources = $query->orderByDesc('date_created')->paginate(10)); 
    }

    public function mount($resource_list, $resources, $resource_lists_filter_by=null, $disable_filter_by_resource_lists=true) {
        // Init
        $this->livewire_persist = 
        [
            'resource_lists_filter_by_ids' => [], 
            'resource_list_url_id' => null, 
            'resources_all_ids' => []
        ];

        // Check have we got a resource_list filter definition
        if($disable_filter_by_resource_lists != true) {
            $disable_filter_by_resource_lists = (bool) $disable_filter_by_resource_lists;
            $this->livewire_persist['resource_lists_filter_by_ids'] = $resource_lists_filter_by->pluck('id');
        }
        
        // Persist the resource_list and resource details
        $this->livewire_persist['resource_list_url_id'] = $resource_list->url_id;
        $this->livewire_persist['resources_all_ids'] = $resources->pluck('id');
    }


    public function render()
    {
        return view('livewire.resource-list');
    }
}
