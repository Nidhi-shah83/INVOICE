<?php

namespace App\Services;

use Illuminate\Support\Str;

abstract class ModuleService
{
    abstract public function moduleName(): string;

    public function actionPayload(string $action): array
    {
        return [
            'module' => $this->moduleName(),
            'action' => Str::headline($action),
            'serviceClass' => static::class,
        ];
    }

    public function primaryActionLabel(): string
    {
        return sprintf('Create %s', Str::singular($this->moduleName()));
    }
}
