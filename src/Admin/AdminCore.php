<?php

declare(strict_types=1);

namespace NiceSimpleVp\Admin;

final readonly class AdminCore
{
    public function __construct() {}


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueStyles(): void {}

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueScripts(): void {}
}