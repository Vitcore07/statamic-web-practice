<?php

namespace App\Fieldtypes;

use Statamic\Facades\Icon as Icons;
use Statamic\Fieldtypes\Icon as StatamicIcon;
use Statamic\Icons\IconSet;

class ApisIcon extends StatamicIcon
{
    protected $component = 'icon';

    protected $indexComponent = 'icon';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Selection'),
                'fields' => [
                    'set' => [
                        'display' => __('Icon Set'),
                        'instructions' => __('statamic::fieldtypes.icon.config.set'),
                        'type' => 'hidden',
                        'default' => 'apis',
                    ],
                    'default' => [
                        'display' => __('Default Icon'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                        'width' => 50,
                    ],
                ],
            ],
        ];
    }

    public function icons()
    {
        $set = $this->iconSet();

        return $set->name() === 'default'
            ? $set->names()->mapWithKeys(fn ($name) => [$name => null])->all()
            : $set->contents();
    }

    public function augment($value)
    {
        if (! $value) {
            return null;
        }

        return $this->iconSet()->get($value);
    }

    protected function iconSet(): IconSet
    {
        return Icons::get($this->config('set', 'apis'));
    }
}
