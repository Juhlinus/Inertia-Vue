# <p align="center">Inertie-Vue</p>

<p align="center">A laravel frontend command to turn your Models to Vue Components</p>

## <p align="center">Installation</p>

Require the package via composer

`composer require juhlinus/inertia-vue --dev`

## <p align="center">Usage</p>

```
inertia-vue
    --model= : Specify the Model to convert to Vue (Note: Leaving this empty will generate views from all Models)
    --path= : Specify the js path of pages (Default: resources/js/Pages)
    --stub= : Specify the stub path (Default: package stub directory)
    --data= : Whether or not the model data is accessesed through the `data` attribute (Default: false)
```