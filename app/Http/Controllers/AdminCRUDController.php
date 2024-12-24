<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCRUDController extends Controller {
    /**
     * List specified model
     * 
     * @param Illuminate\Http\Request $request
     */
    public static function list(Request $request, $model_class_name) {
        // Init
        $model_fqcn = "App\Models\\$model_class_name";
        $model_instance = new $model_fqcn;

        return view('admin/crud/list', ['model_class_name' => $model_class_name, 'model_instance' => $model_instance, 'model_fqcn' => $model_fqcn]);
    }

    /**
     * Generate specified report
     * 
     * @param Illuminate\Http\Request $request
     * @param string $report_name Report name
     */
    public static function report(Request $request, $report_name) {
        return view('admin/crud/report', ['report_name' => $report_name]);
    }

      /**
     * Delete a specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $model_class_name The name of the model class
     * @param int $id The ID of the model to delete
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $model_class_name, $id)
    {
        // Dynamically resolve the model class
        $model_fqcn = "App\\Models\\$model_class_name";
        DB::beginTransaction();

        try {
            // Find the model instance and delete it
            $instance = $model_fqcn::findOrFail($id);

            // If the model is User, also delete related accounts
            if (stripos($model_class_name, 'User') !== false) {
                // Assuming the 'accounts' table has a foreign key 'user_id' to 'users' table
                DB::table('accounts')->where('user_id', $id)->delete();
            }

            $instance->delete();

            // Commit the transaction
            DB::commit();

            // Redirect or return a response
            return redirect()->back()->with('status', 'Item deleted successfully!');
        } catch (\Exception $e) {
            // Rollback the transaction if something went wrong
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete item.');
        }
    }
}
