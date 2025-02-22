<?php

namespace Botble\Ecommerce\Syncers\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Syncers\Syncer;

abstract class SyncController extends BaseController
{
    abstract protected function getSyncer(): Syncer;

    protected function allowsSelectColumns(): bool
    {
        return true;
    }

    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('core/base::layouts.tools'))
            ->add(trans('packages/data-synchronize::data-synchronize.tools.export_import_data'), route('tools.data-synchronize'));
    }
}
