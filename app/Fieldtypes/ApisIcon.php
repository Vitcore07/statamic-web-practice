<?php

namespace App\Fieldtypes;

use Statamic\Facades\Icon as Icons;
use Statamic\Fieldtypes\Icon as StatamicIcon;
use Statamic\Icons\IconSet;

class ApisIcon extends StatamicIcon
{
    protected $component = 'icon';

    protected $indexComponent = 'icon';

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
