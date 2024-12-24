<?php

namespace App\Livewire;

use App\Helpers\AdminCRUDHelper;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AdminCRUDList extends Component
{
    use WithPagination;

    /**
     * Name of the class being listed
     */
    public ?string $model_class_name = null;

    /**
     * Model FQCN
     */
    public ?string $model_fqcn = null;

    /**
     * Model object variables
     */
    public ?array $model_object_vars = null;

    /**
     * Most recent search query
     */
    public ?string $search_query = null;

    /**
     * Number of interactions
     */
    public int $interact_count = 0;

    /**
     * Most recently fetched list
     */
    private $list;

    /**
     * Previous ORM query executed
     */
    private $orm_query_previous;
    public ?string $age_range = null;
    public ?string $gender = null;

    public ?string $country = null;

    public $user;
    // public $countries = [];
    public $countries;


    /**
     * Get the list of model instances to display
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Get the next ORM query to execute
     */
    private function nextORMQuery()
    {
        // Init
        $model_instance = new $this->model_fqcn;
        $return         = null;

        // Search query
        if ($this->search_query !== null) {
            $return = $model_instance::getSearchORMQuery($this->search_query);
        } else {
            // Default query
            $return = $model_instance::where('id', '>=', '1')
                ->orderBy('created_at', 'desc');
        }

        // Age range filter
        if ($this->age_range !== null) {
            [$minAge, $maxAge] = explode('-', $this->age_range);
            $return->whereBetween('age', [(int)$minAge, (int)$maxAge]);
        }

        // Gender filter
        if ($this->gender !== null) {
            $return->where('gender', $this->gender);
        }

        // Country filter
        if ($this->country !== null && $this->country !== '') {
            $return->where('country', $this->country);
        }

        return ($this->orm_query_previous = $return);
    }

    /**
     * Receive the search submission from the component
     */
    public function submit_search()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->model_fqcn        = "App\Models\\$this->model_class_name";
        $this->model_object_vars = AdminCRUDHelper::modelGetDisplayableAttributes($this->model_fqcn);

        $this->countries = User::getAllCountries();
    }

    public function boot()
    {
        $this->interact_count++;
    }

    public function render()
    {
        // Execute database query
        $this->list = $this->nextORMQuery()->paginate(50);

        return view('livewire.admin-c-r-u-d-list', [
            'countries' => $this->countries, // Pass countries to the view
        ]);
    }

    public function exportUserResult()
    {
        // Use the existing filtering logic to get the filtered users
        $usersQuery = $this->nextORMQuery()->with('profileQuestions'); // Add 'profileQuestions' relationship
        $users = $usersQuery->get()->toArray(); // Get all matching records

        // Prepare the data for CSV
        $data = [];
        foreach ($users as $user) {
            $questions = [];
            if (isset($user['profile_questions'])) {
                foreach ($user['profile_questions'] as $profile) {
                    $goals = is_array($profile['goals']) ? implode(', ', $profile['goals']) : $profile['goals'];
                    $mentalHealthIssues = is_array($profile['mental_health_issues']) ? implode(', ', $profile['mental_health_issues']) : $profile['mental_health_issues'];

                    $questions[] = 'Goals: ' . ($goals ?? 'N/A') . 
                                '; Mental Health Issues: ' . ($mentalHealthIssues ?? 'N/A') .
                                '; Hair Loss: ' . ($profile['hair_loss'] ?? 'N/A') .
                                '; Birth Control: ' . ($profile['birth_control'] ?? 'N/A') .
                                '; Reproductive Disorder: ' . ($profile['reproductive_disorder'] ?? 'N/A') .
                                '; Weight Change: ' . ($profile['weight_change'] ?? 'N/A') .
                                '; Coffee Consumption: ' . ($profile['coffee_consumption'] ?? 'N/A') .
                                '; Alcohol Consumption: ' . ($profile['alcohol_consumption'] ?? 'N/A') .
                                '; Other Goal: ' . ($profile['other_goal'] ?? 'N/A');
                }
            }

            $data[] = [
                'ID'             => $user['id'],
                'Name'           => $user['name'],
                'Email'          => $user['email'],
                'Gender'         => $user['gender'],
                'Age'            => $user['age'],
                'Height'         => $user['height'],
                'Weight'         => $user['weight'],
                'Address'        => $user['address_line1'],
                'City'           => $user['city'],
                'Region'         => $user['region'],
                'Country'        => $user['country'],
                'Postal Code'    => $user['postcode'],
                'Hobbies'        => $user['hobbies'],
                'Email Verified' => ($user['email_verified_at'] != null) ? 'Yes' : 'No',
                'Profile Questions' => implode('; ', $questions), // Concatenate all questions and answers
            ];
        }

        // Generate CSV file
        $fileName = 'users-' . time() . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Gender', 'Age', 'Height', 'Weight', 'Address', 'City', 'Region', 'Country', 'Postal Code', 'Hobbies', 'Email Verified', 'Profile Questions']);
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
