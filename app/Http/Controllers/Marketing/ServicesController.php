<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ServicesController extends Controller
{
    /**
     * Display the C-Corporation service page.
     */
    public function cCorp(): Response
    {
        return Inertia::render('Marketing/Services/CCorporation');
    }

    /**
     * Display the S-Corporation service page.
     */
    public function sCorp(): Response
    {
        return Inertia::render('Marketing/Services/SCorporation');
    }

    /**
     * Display the LLC formation service page.
     */
    public function llc(): Response
    {
        return Inertia::render('Marketing/Services/LLC');
    }

    /**
     * Display the Nonprofit service page.
     */
    public function nonprofit(): Response
    {
        return Inertia::render('Marketing/Services/Nonprofit');
    }

    /**
     * Display the Green Card Lottery service page.
     */
    public function greenCard(): Response
    {
        return Inertia::render('Marketing/Services/GreenCard');
    }

    /**
     * Display the Income Tax Filing & Planning service page.
     */
    public function incomeTax(): Response
    {
        return Inertia::render('Marketing/Services/IncomeTax');
    }
}
