<?php

namespace TCG\Voyager\Http\Controllers;

use App\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;
use Illuminate\Support\Facades\Auth;

class VoyagerBreadController extends Controller
{
    use BreadRelationshipParser;
    //***************************************
    //               ____
    //              |  _ \
    //              | |_) |
    //              |  _ <
    //              | |_) |
    //              |____/
    //
    //      Browse our Data Type (B)READ
    //
    //****************************************

    public function index(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('browse_'.$dataType->name);

        $getter = $dataType->server_side ? 'paginate' : 'get';
        $search = '';
        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);

            $relationships = $this->getRelationships($dataType);

            $dataModel = $model->with($relationships);
            if($_GET){
                foreach ($dataType->browseRows as $bRow){
                    if(isset($_GET[$bRow->field])){
                        if(isset($_GET['sortby'])){
                            $dataModel->where($bRow->field, 'like', '%'.$_GET[$bRow->field].'%')->orderBy($_GET['sortby'], 'DESC');
                        } else{
                            $dataModel->where($bRow->field, 'like', '%'.$_GET[$bRow->field].'%');
                        }
                    }
                }
            }
            if ($model->timestamps) {
                $dataTypeContent = call_user_func([$dataModel->latest(), $getter]);
            } else {
                $dataTypeContent = call_user_func([$dataModel->orderBy('id', 'DESC'), $getter]);
            }
            //dd($model->with($relationships)->latest());
            //Replace relationships' keys for labels and create READ links if a slug is provided.
            $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
        } else {
            // If Model doesn't exist, get data from table name
            $dataTypeContent = call_user_func([DB::table($dataType->name), $getter]);
            $model = false;
        }
        //dd($dataTypeContent);
        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($model);

        $view = 'voyager::bread.browse';

        if (view()->exists("voyager::$slug.browse")) {
            $view = "voyager::$slug.browse";
        }


        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'))->with(['search' => $search]);
    }

    //***************************************
    //                _____
    //               |  __ \
    //               | |__) |
    //               |  _  /
    //               | | \ \
    //               |_|  \_\
    //
    //  Read an item of our Data Type B(R)EAD
    //
    //****************************************

    public function show(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('read_'.$dataType->name);

        $relationships = $this->getRelationships($dataType);
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $dataTypeContent = call_user_func([$model->with($relationships), 'findOrFail'], $id);
        } else {
            // If Model doest exist, get data from table name
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }

        //Replace relationships' keys for labels and create READ links if a slug is provided.
        $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType, true);

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        $view = 'voyager::bread.read';

        if (view()->exists("voyager::$slug.read")) {
            $view = "voyager::$slug.read";
        }

        $dataSeo = DB::table('seo')->where('table', $dataType->name)->where('item_id', $id)->get();

        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'dataSeo'));
    }

    //***************************************
    //                ______
    //               |  ____|
    //               | |__
    //               |  __|
    //               | |____
    //               |______|
    //
    //  Edit an item of our Data Type BR(E)AD
    //
    //****************************************

    public function edit(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('edit_'.$dataType->name);

        $relationships = $this->getRelationships($dataType);

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->with($relationships)->findOrFail($id)
            : DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        $view = 'voyager::bread.edit-add';

        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }
        //dd($dataTypeContent);

        $dataSeoDescription = DB::table('seo')->where('table', $dataType->name)->where('item_id', $id)->value('description');
        $dataSeoKeywords = DB::table('seo')->where('table', $dataType->name)->where('item_id', $id)->value('keywords');
        $dataSeoTitle = DB::table('seo')->where('table', $dataType->name)->where('item_id', $id)->value('title');

        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'dataSeoDescription', 'dataSeoKeywords', 'dataSeoTitle'));
    }

    // POST BR(E)AD
    public function update(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('edit_'.$dataType->name);

        //Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->ajax()) {
            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);


            $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

            $seoRecord = DB::table('seo')->where('table', '=', $dataType->name)->where('item_id', '=', $id)->first();
            if (!$seoRecord){
                DB::table('seo')->insert(
                    ['table' => $dataType->name,
                        'item_id' => $id,
                        'title' => $request->metaTitle,
                        'description' => $request->metaDescription,
                        'keywords' => $request->metaKeywords]
                );
            } else {
                DB::table('seo')->where('item_id', '=', $id)->update(
                    ['table' => $dataType->name,
                        'item_id' => $id,
                        'title' => $request->metaTitle,
                        'description' => $request->metaDescription,
                        'keywords' => $request->metaKeywords]
                );
            }

            return redirect()
                ->route("voyager.{$dataType->slug}.edit", ['id' => $id])
                ->with([
                    'message'    => "Запись успешно обновлена {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }
    }

    //***************************************
    //
    //                   /\
    //                  /  \
    //                 / /\ \
    //                / ____ \
    //               /_/    \_\
    //
    //
    // Add a new item of our Data Type BRE(A)D
    //
    //****************************************

    public function create(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('add_'.$dataType->name);

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? new $dataType->model_name()
            : false;

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        $view = 'voyager::bread.edit-add';

        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }

        $dataSeoDescription = DB::table('seo')->where('table', $dataType->name)->where('item_id', $dataTypeContent->id)->value('description');
        $dataSeoKeywords = DB::table('seo')->where('table', $dataType->name)->where('item_id', $dataTypeContent->id)->value('keywords');
        $dataSeoTitle = DB::table('seo')->where('table', $dataType->name)->where('item_id', $dataTypeContent->id)->value('title');

        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'dataSeoDescription', 'dataSeoKeywords', 'dataSeoTitle'));
    }

    // POST BRE(A)D
    public function store(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('add_'.$dataType->name);

        //Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->ajax()) {

            $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

            DB::table('seo')->insert(
                ['table' => $dataType->name,
                    'item_id' => $data->id,
                    'title' => $request->metaTitle,
                    'description' => $request->metaDescription,
                    'keywords' => $request->metaKeywords]
            );

            return redirect()
                ->route("voyager.{$dataType->slug}.edit", ['id' => $data->id])
                ->with([
                    'message'    => "Запись успешно добавлена {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }
    }

    //***************************************
    //                _____
    //               |  __ \
    //               | |  | |
    //               | |  | |
    //               | |__| |
    //               |_____/
    //
    //         Delete an item BREA(D)
    //
    //****************************************

    public function destroy(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('delete_'.$dataType->name);

        $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);

        // Delete Translations, if present
        if (is_bread_translatable($data)) {
            $data->deleteAttributeTranslations($data->getTranslatableAttributes());
        }

        foreach ($dataType->deleteRows as $row) {
            if ($row->type == 'image') {
                $this->deleteFileIfExists('/uploads/'.$data->{$row->field});

                $options = json_decode($row->details);

                if (isset($options->thumbnails)) {
                    foreach ($options->thumbnails as $thumbnail) {
                        $ext = explode('.', $data->{$row->field});
                        $extension = '.'.$ext[count($ext) - 1];

                        $path = str_replace($extension, '', $data->{$row->field});

                        $thumb_name = $thumbnail->name;

                        $this->deleteFileIfExists('/uploads/'.$path.'-'.$thumb_name.$extension);
                    }
                }
            }
        }

        $data = $data->destroy($id)
            ? [
                'message'    => "Запись удалена {$dataType->display_name_singular}",
                'alert-type' => 'success',
            ]
            : [
                'message'    => "Проблема при удалении {$dataType->display_name_singular}",
                'alert-type' => 'error',
            ];

        DB::table('seo')->where('table', $dataType->name)->where('item_id', $id)->delete();

        DB::table('logs')->insert(
            ['user_id' => Auth::id(), 'user_name' => Auth::user()->name, 'action' => 'destroy',
                'table_name' => $dataType->name, 'row_id' => $id, 'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')]
        );

        return redirect()->route("voyager.{$dataType->slug}.index")->with($data);
    }

    public function deleteImage(Request $request, $id, $db, $field)
    {
        $data = DB::table($db)->where('id', $id)->update([$field => ''])
            ? [
                'message'    => "Успешно удалено",
                'alert-type' => 'success',
            ]
            : [
                'message'    => "Возникли проблемы",
                'alert-type' => 'error',
            ];

        return redirect()->back()->with($data);
    }
}
