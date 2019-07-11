<?php

namespace InertiaVue;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SplFileInfo;

class InertiaVueCommand extends Command
{
    protected $signature = 'inertia-vue
                            {--model=Model : Specify the Model to convert to Vue}
                            {--path= : Specify the js path of pages}
                            {--stub= : Specify the stub path}';

    protected $description = 'Converts a Model to an Inertia Vue File';

    public function handle()
    {
        $model =$this->option('model');
        $path =  rtrim($this->option('path') ?? resource_path('js/Pages'), '/');
        $stub =  rtrim($this->option('stub') ?? (__DIR__ . '/stubs'), '/');

        $file = collect(File::allFiles(database_path('migrations/')))->filter(function (SplFileInfo $item) use ($model) {
            $migration = 'create_' . Str::plural(strtolower($model)) . '_table.php';

            return Str::contains($item->getRelativePathname(), $migration);
        })->first();

        preg_match_all('/\$table->([A-Za-z]+)\(\'([A-Za-z_+]+)\'\);/', $file->getContents(), $matches);

        $fields = collect($matches[1])->zip($matches[2]);

        $primaryKey = $fields->shift();

        $buildFields = $this->buildFields($fields);

        [$indexVueFile, $editVueFile, $createVueFile] = $this->replaceWithData([
            '{{fields-head}}' => $buildFields->pluck('{{fields-head}}')->join('
            '),
            '{{fields-data}}' => $buildFields->pluck('{{fields-data}}')->join('
            '),
            '{{input-fields}}' => $buildFields->pluck('{{input-fields}}')->join('
          '),
            '{{data-form-input}}' => $buildFields->pluck('{{data-form-input}}')->join('
        '),
            '{{data-form-input-null}}' => $buildFields->pluck('{{data-form-input-null}}')->join('
        '),
        ], [
            File::get($stub . '/Index.vue.stub'),
            File::get($stub . '/Edit.vue.stub'),
            File::get($stub . '/Create.vue.stub'),
        ]);

        [$indexVueFile, $editVueFile, $createVueFile] = $this->replaceWithData([
            '{{Models}}' => ucfirst(Str::plural($model)),
            '{{Model}}' => ucfirst($model),
            '{{models}}' => strtolower(Str::plural($model)),
            '{{model}}' => strtolower($model),
            '{{primaryKey}}' => $primaryKey[1],
        ], [$indexVueFile, $editVueFile, $createVueFile]);

        if (!File::exists($path)) {
            File::makeDirectory($path);
        }

        $jsModelPath = $path . '/' . ucfirst(Str::plural($model));

        if (!File::exists($jsModelPath)) {
            File::makeDirectory($jsModelPath);
        }

        File::put($jsModelPath . '/Index.vue', $indexVueFile);
        File::put($jsModelPath . '/Create.vue', $createVueFile);
        File::put($jsModelPath . '/Edit.vue', $editVueFile);
    }

    private function replaceWithData(array $keysAndValues, array $files): array
    {
        return collect($files)->map(function ($item) use ($keysAndValues) {
            return strtr($item, $keysAndValues);
        })->toArray();
    }

    private function buildFields(Collection $fields): Collection
    {
        return $fields->map(function ($item) {
            return [
                '{{fields-head}}' => '<th class="px-6 pt-6 pb-4">' . ucfirst($item[1]) . '</th>',
                '{{fields-data}}' => '
            <td class="border-t">
              <inertia-link class="px-6 py-4 flex items-center" :href="route(\'{{models}}.edit\', {{model}}.id)" tabindex="-1">
                {{ {{model}}.' . $item[1] . ' }}
              </inertia-link>
            </td>',
                '{{input-fields}}' => '<text-input v-model="form.' . $item[1] . '" :errors="$page.errors.' . $item[1] . '" class="pr-6 pb-8 w-full lg:w-1/2" label="' . ucfirst($item[1]) . '" />',
                '{{data-form-input}}' => $item[1] . ': this.{{model}}.' . $item[1] . ',',
                '{{data-form-input-null}}' => $item[1] . ': null,',
            ];
        });
    }
}
