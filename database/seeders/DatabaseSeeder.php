<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Plugins\System\database\seeders\ClientSeeder;
use App\Plugins\System\database\seeders\UserSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Laravel\Telescope\Telescope;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Telescope::stopRecording();
        $this->call([
            ClientSeeder::class,
            UserSeeder::class,
            RolesAndPermissionsSeeder::class,

            \App\Plugins\Example\database\seeders\ItemSeeder::class,
            \App\Plugins\Blog\database\seeders\CategorySeeder::class,

            \App\Plugins\Elearning\database\seeders\CourseCategorySeeder::class,
        ]);

        $this->call([
            \App\Plugins\Drive\database\seeders\FileSeeder::class,
            \App\Plugins\Codebook\database\seeders\RegionSeeder::class,

            \App\Plugins\Product\database\seeders\PartnerCategorySeeder::class,
            \App\Plugins\Product\database\seeders\SectorSeeder::class,
            \App\Plugins\Product\database\seeders\ProductCategorySeeder::class,
            \App\Plugins\Product\database\seeders\ProductSectorCategorySeeder::class,
            \App\Plugins\Product\database\seeders\ProductTypeSeeder::class,

            \App\Plugins\User\database\seeders\InitBrokerSeeder::class,
        ]);

        $this->call([
            init\PartnerSeeder::class,
            init\ProductSeeder::class,
            init\ProductVersionSeeder::class,
        ]);
        //TODO doriesit aby sa volalo len ked chcem
        $this->call([
            DemoSeeder::class,
        ]);

        foreach (Media::all() as $media) {
            $sourceFile = database_path('seeders/storage') . '/' . $media->getPathRelativeToRoot();
            if (!file_exists($sourceFile)) {
                echo "Missing file: $sourceFile\n";
                continue;
            }
            Storage::disk($media->disk)->put($media->getPathRelativeToRoot(), file_get_contents($sourceFile));
        }
        Telescope::startRecording();
    }
}
