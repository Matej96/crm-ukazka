<?php

namespace App\Casts;

use App\Plugins\Drive\app\Models\File;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriveFile implements CastsAttributes
{
    private string|null $name;
    private string $prefix;    //Prefixes: hidden / system / public
    private bool $is_public;

    private array $parents;     //Example: $slide => ['course', 'chapter']

    public function __construct(string $name = '', string $prefix = 'hidden', string $is_public = 'true', string $parents = null)
    {
        $this->name = ($name === '') ? null : $name;
        $this->prefix = ($prefix === '') ? 'hidden' : $prefix;
        $this->is_public = filter_var(($is_public === '') ? 'true' : $is_public, FILTER_VALIDATE_BOOLEAN);
        $this->parents = json_decode(base64_decode($parents), true) ?? [];
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $model::saved(function ($savedModel) use ($model, $key, $value, $attributes) {
            if($savedModel->getKey() == $model->getKey()) {
                $is_dirty = $savedModel->isDirty($key);

                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $parent_prefix = '';
                    $parent = null;
                    foreach (array_reverse($this->parents) as $relation) {
                        $parent = $parent ? $parent->$relation : $model->$relation;
                        $plugin = getClassPlugin($parent::class, $table);
                        $parent_prefix = '/_' . $plugin . '_' . Str::plural($table) . '/' . $parent->getKey() . $parent_prefix;
                    }

                    $file = new File();
                    $file->user_id = auth()->id();
                    $file->is_public = $this->is_public;
                    $file->editable = 0;
                    $file->subject_type = $savedModel::class;
                    $file->subject_id = $savedModel->getKey();

                    $plugin = getClassPlugin($savedModel::class, $table);
                    $file->media_path = '_'. $this->prefix . $parent_prefix . '/_' . $plugin . '_' . Str::plural($table) . '/' . $savedModel->getKey() . '/';

                    $file->name = str_replace('/', '_', $this->name ?? (($label = __('table.' . $key)) == ('table.' . $key) ? __('forms.' . $key) : $label));   //TODO prerobit cez getDatatable??

                    if($file->subject && method_exists($file->subject, 'getDriveFileOwner')) {
                        $file->owner()->associate($file->subject::getDriveFileOwner());
                    }

                    $fileUploaded = $value;
                    $originalName = $fileUploaded->getClientOriginalName();
                    $randomFileName = Str::random(40) . '.' . $fileUploaded->getClientOriginalExtension();

                    try {
                        $file->addMedia($fileUploaded)
                            ->usingFileName($randomFileName)
                            ->withCustomProperties([
                                'original_name' => $originalName,
                            ])->toMediaCollection('drive', 'local');
                    } catch (\Exception $e) {
                        $file->forceDelete();
                        return $value;
                    }

                    if ($file->save()) {
                        // Po uložení vygenerujeme URL
                        $savedModel->forceFill([
                            $key => route('drive.file.show', [$file->id])
                        ])->updateQuietly();
                    } else {
                        // Ak zlyhá uloženie File objektu
                        return null;
                    }
                }

                if (($is_dirty) && ($old_path = $attributes[$key] ?? null)) {
                    if ($file = File::find(basename(parse_url($old_path, PHP_URL_PATH)))) $file->delete();

//                    if(Storage::exists($old_path)) {
//                        Storage::delete($old_path);
//                    }
                }

            }
        });
        return $value;
    }
}
