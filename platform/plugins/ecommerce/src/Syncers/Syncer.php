<?php

namespace Botble\Ecommerce\Syncers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Illuminate\Contracts\View\View;

abstract class Syncer
{
    public function getAsProducts()
    {
        return [];
    }

    public function counters(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        return trans(
            'plugins/ecommerce::sync.heading',
            ['label' => $this->getLabel()]
        );
    }

    public function getLayout(): string
    {
        return BaseHelper::getAdminMasterLayoutTemplate();
    }

    public function getCounters(): array
    {
        return apply_filters('data_synchronize_syncer_counters', $this->counters(), $this);
    }

    public function hasDataToSync(): bool
    {
        return true;
    }

    public static function make(): static
    {
        return new static();
    }

    public function render(): View
    {
        // Assets::addScriptsDirectly('vendor/core/packages/data-synchronize/js/data-synchronize.js');

        return view('packages/data-synchronize::sync', [
            'syncer' => $this,
        ]);
    }

    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): string
    {
        if (isset($this->url)) {
            return $this->url;
        }

        return url()->current();
    }
}
