<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Http\Resources\FileResource;
use App\Models\Category;
use App\Models\File;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use function App\Helpers\api_response;
use function App\Helpers\edit_file;
use function App\Helpers\getAndCheckModelById;
use function App\Helpers\getIdByName;
use function App\Helpers\getMediaType;
use function App\Helpers\store_files;
use Barryvdh\DomPDF\Facade\Pdf;

class FileController extends Controller
{
    /**
     * Retrieve and paginate Manuals_And_Plans files.
     *
     * This function fetches files of type 'Manuals & Plans' from the database,
     * paginates the results, and transforms the file data using the specified
     * resource class before returning it.
     *
     */
    public function view_Manuals_And_Plans()
    {
        return $this->get_files(['main_category_id' => '003e8400-e29b-41d4-a716-4466554400MP']);
    }

    /**
     * Get the educational files
     */
    public function view_educational_files()
    {
        return $this->get_files(['main_category_id' => '003e8400-e29b-41d4-a716-44665544EDU']);
    }

    /**
     * Get the educational file versions
     */
    public function view_educational_files_versions(string $version_id)
    {
        try {

            $files = File::where(['version_id' => $version_id])->join('categories', 'categories.id', '=', 'files.sub_category_id')->when(Auth::check(), function ($query) {
                return $query->addSelect('files.id', 'categories.id as category_id', 'categories.name as ctegory', 'files.title', 'files.description', 'files.version', 'files.media_url', 'files.created_at');
            }, function ($query) {
                return $query->addSelect('files.id', 'categories.name as ctegory', 'files.title', 'files.description', 'files.version', 'files.created_at');
            })->orderBy('files.created_at', 'desc')->get();


            return api_response($files, 'educational-file-versions-getting-success');
        } catch (Exception $e) {
            return api_response(errors: [$e->getMessage()], message: 'educational-file-versions-getting-error', code: 500);
        }
    }

    /**
     * Get the Guide lines files
     */
    public function view_guidelines_and_updates()
    {
        return $this->get_files(['main_category_id' => '003e8400-e29b-41d4-a716-44665544GAU']);
    }

    /**
     * Get my Guide lines files
     */
    public function view_my_guidelines_and_updates()
    {
        return $this->get_files(['main_category_id' => '003e8400-e29b-41d4-a716-44665544GAU', 'user_id' => Auth::id()]);
    }

    /**
     * Get view infrastructure service reports files
     */
    public function view_infrastructure_service_reports()
    {
        return $this->get_files(['main_category_id' => '003e8400-e29b-41d4-a716-44665544ISR']);
    }

    /**
     * Get my water level files
     */
    public function view_water_level_reports()
    {
        return $this->get_files(['main_category_id' => '003e8400-e29b-41d4-a716-44665544WLR']);
    }

    /**
     * Get files
     */
    public function get_files(array $Conditions)
    {
        try {

            $files = File::where($Conditions)->join('categories', 'categories.id', '=', 'files.sub_category_id')->when(Auth::check(), function ($query) {
                return $query->addSelect('files.id', 'categories.id as category_id', 'categories.name as ctegory', 'files.title', 'files.description', 'files.media_url', 'files.created_at');
            }, function ($query) {
                return $query->addSelect('files.id', 'categories.name as ctegory', 'files.title', 'files.description', 'files.created_at');
            })->get();

            return api_response($files, 'files-getting-success');
        } catch (Exception $e) {

            return api_response(message: 'files-getting-error', errors: [$e->getMessage()], code: 500);
        }
    }


    /**
     * Download files
     */
    public function download_file(string $id)
    {

        try {

            $file = getAndCheckModelById(File::class, $id);

            return response()->download(public_path($file->media_url));
        } catch (Exception $e) {
            return api_response(errors: [$e->getMessage()], code: 500, message: 'download-error');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get files by auth user ID
        $files = File::where('user_id', auth()->user()->id)->get();

        return ($files->count() == 1)
            ? new FileResource($files->first())
            : FileResource::collection($files);
    }

    /**
     * Add Manuals_And_Plans
     */
    public function add_Manuals_And_Plans(FileRequest $request)
    {
        return $this->store($request, 'Manuals_And_Plans');
    }

    /**
     * Add educational files
     */
    public function add_educational_files(FileRequest $request)
    {
        return $this->store($request, 'Education');
    }

    /**
     * Add Guidelines and updates files
     */
    public function add_guidelines_and_updates_files(FileRequest $request)
    {
        return $this->store($request, 'Guideline_And_Updates');
    }

    /**
     * Add Infrastructure service report file
     */
    public function add_nfrastructure_services_report_file(FileRequest $request)
    {
        return $this->store($request, 'Infrastructure_Reports');
    }

    /**
     * Add water level report file
     */
    public function add_water_level_report_file(FileRequest $request)
    {
        return $this->store($request, 'Water_Level_Reports');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $file_type)
    {
        try {

            $request->validated();

            $file = $request->file;

            $category_id = getIdByName(Category::class, $file_type);

            $path = store_files($file, '/files/' . $file_type);

            if ($file_type == 'Education' && !$request->has('version_id')) {
                $version_id = Str::uuid();
                $version = '1';
            } elseif ($file_type == 'Education' && $request->has('version_id')) {
                $version_id = $request->input('version_id');
                $version = $request->input('version');
            } else {
                $version_id = null;
                $version = null;
            }

            File::create([
                'user_id' => Auth::id(),
                'main_category_id' => $category_id,
                'sub_category_id' => $request->input('category_id'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'version' => $version,
                'version_id' => $version_id,
                'media_url' => $path,
                'media_type' => getMediaType($file)
            ]);


            return api_response(message: 'file-adding-success');
        } catch (Exception $e) {

            return api_response(errors: [$e->getMessage()], message: 'file-adding-error', code: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Get file by ID
        $file = File::find($id);

        // Check if file exists
        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return new FileResource($file);
    }


    /**
     * edit Manuals_And_Plans
     */
    public function edit_Manuals_And_Plans(FileRequest $request, string $id)
    {
        return $this->update($request, 'Manuals_And_Plans', $id);
    }

    /**
     * edit educational files
     */
    public function edit_educational_files(FileRequest $request, string $id)
    {
        return $this->update($request, 'Educational', $id);
    }

    /**
     * edit Guideline_And_Updates files
     */
    public function edit_guidelines_and_updates_files(FileRequest $request, string $id)
    {
        return $this->update($request, 'Guideline_And_Updates', $id);
    }

    /**
     * edit Guideline_And_Updates files
     */
    public function update_guidelines_and_updates_files(Request $request, string $id)
    {
        try {

            // Get file by ID
            $file_ = getAndCheckModelById(File::class, $id);

            if ($request->hasFile('file')) {

                $newFile = $request->file('file');

                // Process and store the new file
                $path = '/files/Guideline_And_Updates';

                $file_path = edit_file($file_->media_url, $newFile, $path);
            } else {
                // Keep the existing file path if no new file is uploaded
                $file_path = $file_->media_url;
            }

            $file_->update([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'version' => $request->input('version'),
                'media_url' => $file_path,
            ]);

            return api_response(message: 'update-Guideline-And-Updates-success');
        } catch (Exception $e) {

            return api_response(errors: [$e->getMessage()], message: 'update-Guideline_And_Updates-error', code: 500);
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $file_type, string $id)
    {
        try {

            $request->validated();

            // Get file by ID
            $file_ = getAndCheckModelById(File::class, $id);

            $file_path = $file_->media_url;

            if ($request->hasFile('file')) {

                $newFile = $request->file('file');

                // Process and store the new file
                $path = '/files/' . $file_type;

                $file_path = edit_file($file_->media_url, $newFile, $path);

                $file_type = getMediaType($newFile);
            } else {
                // Keep the existing file path if no new file is uploaded
                $file_path = $file_->media_url;

                $file_type = $file_->file_type;
            }

            $file_->update([
                'sub_category_id' => $request->input('category_id'),
                'file_type' => $file_type,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'version' => $request->input('version'),
                'media_url' => $file_path,
                'media_type' => $file_type
            ]);


            return api_response(message: 'file-editing-success');
        } catch (Exception $e) {

            return api_response(errors: [$e->getMessage()], message: 'file-editing-error', code: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Get file by ID
        $file = getAndCheckModelById(File::class, $id);

        // Check if file exists
        if (!$file) {
            return api_response(message: 'File not found', code: 404);
        }

        // Check if user is authorized
        if ($file->user_id != Auth::id()) {
            return api_response(message: 'Unauthorized to delete this file it\'s dont belong to you', code: 401);
        }

        // todo add this in evere object has any type of media to remove it from the app
        unlink(public_path($file->media_url));

        // Delete file
        $file->delete();

        return api_response(message: 'file-delete-success');
    }

    /**
     * Generet reports pdf files
     */
    public function generatePDF(){
        try{
            $data = [

            ];
            $pdf = Pdf::loadView('pdf.report', $data);
            return $pdf->download('invoice.pdf');
        }
        catch(Exception $e){
            return api_response(errors:[$e->getMessage()],message:'report-generatting-error',code:500);
        }
    }

    /**
     * Get report information details
     */
    public function get_info(){
        try{

            $info = "" ;

            return $info;
        }
        catch(Exception $e){
            return api_response(errors:[$e->getMessage()],message:'getting-report-info-erroe',code:500);
        }
    }

}

