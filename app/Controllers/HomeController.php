<?php

namespace App\Controllers;

class HomeController
{
    /**
     * Show the home page.
     *
     * @return string HTML output for the home page.
     */
    public function index(): string
    {
        return '<h1>👋 Welcome to TrackPHP!</h1>';
    }
}
